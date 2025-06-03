<?php

namespace App\core;

use App\core\Database;
use PDO;
use ReflectionClass;

/**
 * BaseModel
 * 
 * Abstract base class providing common ORM-like database interactions.
 * Child models should override static properties like $table, $fields, $required, $protected, $softDelete
 * to customize behavior per database table.
 */
abstract class BaseModel
{
    // Override these in your child model

    /** @var string|null Table name; defaults to plural lowercase of model class */
    protected static $table = null;

    /** @var array Allowed columns for insert/update operations */
    protected static $fields = [];

    /** @var array Required columns for insert/update operations */
    protected static $required = [];

    /** @var array Columns to exclude from default SELECT queries */
    protected static $protected = [];

    /** @var bool Enable soft delete (sets deleted_at timestamp instead of hard delete) */
    protected static $softDelete = false;

    /**
     * Constructor
     * Assigns data properties dynamically.
     *
     * @param array $data Associative array of properties to set
     */
    public function __construct($data = [])
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Get table name explicitly (for external use)
     *
     * @return string
     */
    public static function getTableName()
    {
        return static::table();
    }

    /**
     * Get PDO instance from Database singleton
     *
     * @return PDO
     */
    public static function db()
    {
        return Database::getInstance()->pdo();
    }

    /**
     * Determine table name based on static::$table or fallback naming convention
     *
     * @return string
     */
    protected static function table()
    {
        if (static::$table)
            return static::$table;

        $class = (new ReflectionClass(static::class))->getShortName();
        return strtolower($class) . 's';
    }

    /**
     * Fetch all records with optional pagination and soft delete filtering.
     *
     * @param int  $page       Page number (1-based)
     * @param int  $perPage    Items per page (max 300)
     * @param bool $withTrashed Include soft deleted records if true
     * @return array Result set as associative arrays
     */
    public static function all($page = 1, $perPage = 20, $withTrashed = false)
    {
        $table = static::table();
        $perPage = max(1, min((int) $perPage, 300)); // Cap perPage between 1 and 300
        $offset = ($page - 1) * $perPage;

        // Only include non-deleted if softDelete enabled and not withTrashed
        $where = (static::$softDelete && !$withTrashed) ? "WHERE deleted_at IS NULL" : "";

        $select = BaseModel::getDefaultFieldsForSelect();

        $sql = "SELECT $select FROM `$table` $where LIMIT :limit OFFSET :offset";
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a single record by id or array of conditions.
     *
     * @param mixed $params ID or array of column => value pairs
     * @param bool  $withTrashed Include soft deleted records if true
     * @return static|null Instance of model or null if not found
     */
    public static function find($params, $withTrashed = false)
    {
        $table = static::table();
        $select = static::getDefaultFieldsForSelect();

        if (!is_array($params))
            $params = ['id' => $params];

        $where = [];
        foreach ($params as $col => $val) {
            $where[] = "`$col` = :$col";
        }
        if (static::$softDelete && !$withTrashed)
            $where[] = "deleted_at IS NULL";

        $sql = "SELECT $select FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = self::db()->prepare($sql);

        foreach ($params as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new static($data) : null;
    }

    /**
     * Insert a new record with whitelisted and required fields.
     *
     * @param array $data Data to insert
     * @return string Last inserted ID
     * @throws \Exception if required fields missing or no valid fields to insert
     */
    public static function create($data)
    {
        $table = static::table();
        $fields = static::$fields;

        // Validate required fields are present
        foreach (static::$required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: $field");
            }
        }

        // Filter to allowed fields only
        $insert = array_intersect_key($data, array_flip($fields));

        if (empty($insert)) {
            throw new \Exception("No valid fields for insert.");
        }

        $columns = implode('`, `', array_keys($insert));
        $placeholders = implode(', ', array_fill(0, count($insert), '?'));
        $sql = "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)";
        $stmt = self::db()->prepare($sql);
        $stmt->execute(array_values($insert));
        return self::db()->lastInsertId();
    }

    /**
     * Update a record by ID with whitelisted fields.
     *
     * @param int   $id Record ID to update
     * @param array $data Data to update
     * @return bool Success
     * @throws \Exception if no valid fields provided
     */
    public static function update($id, $data)
    {
        $table = static::table();
        $fields = static::$fields;
        $update = array_intersect_key($data, array_flip($fields));
        if (empty($update)) {
            throw new \Exception("No valid fields for update.");
        }

        $set = implode(', ', array_map(function ($f) {
            return "`$f` = ?";
        }, array_keys($update)));
        $sql = "UPDATE `$table` SET $set WHERE id = ?";
        $params = array_values($update);
        $params[] = $id;
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a record by ID.
     * If soft delete enabled, set deleted_at timestamp, else hard delete.
     *
     * @param int $id Record ID
     * @return bool Success
     */
    public static function delete($id)
    {
        $table = static::table();
        if (static::$softDelete) {
            $sql = "UPDATE `$table` SET deleted_at = NOW() WHERE id = ?";
            $stmt = self::db()->prepare($sql);
            return $stmt->execute([$id]);
        } else {
            $sql = "DELETE FROM `$table` WHERE id = ?";
            $stmt = self::db()->prepare($sql);
            return $stmt->execute([$id]);
        }
    }

    /**
     * Count all records optionally excluding soft deleted.
     *
     * @param bool $withTrashed Include soft deleted if true
     * @return int
     */
    public static function count($withTrashed = false)
    {
        $table = static::table();
        $where = (static::$softDelete && !$withTrashed) ? "WHERE deleted_at IS NULL" : "";
        $sql = "SELECT COUNT(*) FROM `$table` $where";
        return self::db()->query($sql)->fetchColumn();
    }

    /**
     * Paginate with optional search and filters.
     *
     * @param int    $page        Page number (1-based)
     * @param int    $perPage     Records per page
     * @param string $search      Search string for LIKE queries
     * @param array  $searchColumns Columns to search in
     * @param bool   $withTrashed Include soft deleted if true
     * @param array  $filters     Exact match filters (column => value)
     * @return array Result set
     */
    public static function paginate($page = 1, $perPage = 10, $search = '', $searchColumns = [], $withTrashed = false, $filters = [])
    {
        $table = static::table();
        $perPage = min((int) $perPage, 300);
        $offset = ($page - 1) * $perPage;
        $validColumns = static::$fields ?? [];
        $searchColumns = array_intersect($searchColumns, $validColumns);

        $where = [];
        $params = [];

        // Apply filters safely ignoring invalid columns
        foreach ($filters as $key => $value) {
            if (in_array($key, $validColumns, true)) {
                $where[] = "`$key` = :filter_$key";
                $params[":filter_$key"] = $value;
            }
        }

        // Build search WHERE clause
        if ($search && !empty($searchColumns)) {
            $searchWheres = [];
            foreach ($searchColumns as $col) {
                $searchWheres[] = "`$col` LIKE :search";
            }
            $where[] = '(' . implode(' OR ', $searchWheres) . ')';
            $params[':search'] = "%$search%";
        }

        // Exclude soft deleted if needed
        if ((property_exists(static::class, 'softDelete') && static::$softDelete) && !$withTrashed) {
            $where[] = "deleted_at IS NULL";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $select = BaseModel::getDefaultFieldsForSelect();

        $sql = "SELECT $select FROM `$table` $whereClause ORDER BY id DESC LIMIT $perPage OFFSET $offset";
        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count records matching filters and search.
     *
     * @param string $search
     * @param array  $searchColumns
     * @param bool   $withTrashed
     * @param array  $filters
     * @return int
     */
    public static function countPaginated($search = '', $searchColumns = [], $withTrashed = false, $filters = [])
    {
        $table = static::table();
        $where = [];
        $params = [];
        $validColumns = static::$fields ?? [];
        $searchColumns = array_intersect($searchColumns, $validColumns);

        // Securely handle filters
        foreach ($filters as $key => $value) {
            if (in_array($key, $validColumns, true)) {
                $where[] = "`$key` = :filter_$key";
                $params[":filter_$key"] = $value;
            }
        }

        // Search conditions
        if ($search && !empty($searchColumns)) {
            $searchWheres = [];
            foreach ($searchColumns as $col) {
                $searchWheres[] = "`$col` LIKE :search";
            }
            $where[] = '(' . implode(' OR ', $searchWheres) . ')';
            $params[':search'] = "%$search%";
        }

        // Soft delete condition
        if ((property_exists(static::class, 'softDelete') && static::$softDelete) && !$withTrashed) {
            $where[] = "deleted_at IS NULL";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) FROM `$table` $whereClause";

        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Eager loads related models for a belongsTo relationship.
     *
     * @param array  $items        Array of associative arrays or model objects (e.g., transactions)
     * @param string $foreignKey   The foreign key in the item (e.g., 'client_id')
     * @param string $relatedModel The fully-qualified related Model class (e.g., \App\Models\Client::class)
     * @param string $as           The key to attach the related model to each item (e.g., 'client')
     * @param string $localKey     The related model's primary key (usually 'id')
     * @return array               The $items array with related model attached at $as
     */
    public static function eagerLoad(array $items, $foreignKey, $relatedModel, $as, $localKey = 'id')
    {
        // Collect unique foreign keys ignoring null/empty
        $ids = [];
        foreach ($items as $item) {
            $fk = is_array($item) ? $item[$foreignKey] : $item->$foreignKey;
            if ($fk !== null && $fk !== '' && is_scalar($fk)) {
                $ids[] = $fk;
            }
        }
        $ids = array_unique($ids);
        $ids = array_values($ids); // Reindex keys - critical!

        if (empty($ids)) {
            return $items; // No related keys, return early
        }

        // Prepare SQL to get related models in one query
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $table = $relatedModel::table();
        $sql = "SELECT * FROM `$table` WHERE `$localKey` IN ($placeholders)";
        $stmt = $relatedModel::db()->prepare($sql);
        $stmt->execute($ids);
        $relatedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map related models by local key
        $relatedMap = [];
        foreach ($relatedRows as $row) {
            $relatedMap[$row[$localKey]] = $row;
        }

        // Attach related model data to items
        foreach ($items as &$item) {
            $fk = is_array($item) ? $item[$foreignKey] : $item->$foreignKey;
            $related = $relatedMap[$fk] ?? null;
            if (is_array($item)) {
                $item[$as] = $related;
            } else {
                $item->$as = $related;
            }
        }
        unset($item);

        return $items;
    }

    /**
     * Get default fields for select queries, excluding protected columns.
     *
     * @return string
     */
    public static function getDefaultFieldsForSelect()
    {
        $allFields = static::$fields ?? [];
        $protected = static::$protected ?? [];
        $selectFields = array_diff($allFields, $protected);
        return empty($selectFields) ? '*' : '`' . implode('`, `', $selectFields) . '`';
    }
}
