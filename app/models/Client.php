<?php

namespace App\models;

use App\core\BaseModel;
use PDO;

/**
 * Client model represents clients belonging to users.
 * Supports soft deletion and related transactions.
 */
class Client extends BaseModel
{
    protected static $table = 'clients';  // Explicit table name (optional if matches class name)
    protected static $fields = ['id', 'name', 'email', 'phone', 'user_id']; // Columns allowed for DB operations
    protected static $required = ['name', 'email', 'phone', 'user_id']; // Required fields on create/update
    protected static $softDelete = true;

    /**
     * Get transactions related to this client.
     *
     * @return array List of transactions
     */
    public function transactions()
    {
        return Transaction::paginate(1, 10000, '', [], false, ['client_id' => $this->id]);
    }

    /**
     * Get the user who owns this client.
     *
     * @return User|null
     */
    public function user()
    {
        return User::find($this->user_id);
    }

    /**
     * Delete client and optionally delete related transactions.
     *
     * @param bool $deleteTransactions Whether to delete related transactions
     * @return bool
     */
    public function deleteClient($deleteTransactions = true)
    {
        if ($deleteTransactions) {
            Transaction::deleteByClientId($this->id);
        }
        return parent::delete($this->id);
    }

    /**
     * Paginate clients with calculated balance (income - expense).
     *
     * @param int    $page
     * @param int    $perPage
     * @param string $search
     * @param array  $searchColumns
     * @param bool   $withTrashed
     * @param array  $filters
     * @return array
     */
    public static function paginateWithBalances(
        $page = 1,
        $perPage = 10,
        $search = '',
        $searchColumns = [],
        $withTrashed = false,
        $filters = []
    ) {
        $perPage = min((int) $perPage, 300);
        $table = static::table();
        $offset = ($page - 1) * $perPage;
        $validColumns = static::$fields ?? [];
        $searchColumns = array_intersect($searchColumns, $validColumns);

        $where = [];
        $params = [];

        // Apply filters securely
        foreach ($filters as $key => $value) {
            if (in_array($key, $validColumns, true)) {
                $where[] = "`$table`.`$key` = :filter_$key";
                $params[":filter_$key"] = $value;
            }
        }

        // Search condition
        if ($search && !empty($searchColumns)) {
            $searchWheres = [];
            foreach ($searchColumns as $col) {
                $searchWheres[] = "`$table`.`$col` LIKE :search";
            }
            $where[] = '(' . implode(' OR ', $searchWheres) . ')';
            $params[':search'] = "%$search%";
        }

        // Soft delete condition
        if ((property_exists(static::class, 'softDelete') && static::$softDelete) && !$withTrashed) {
            $where[] = "`$table`.`deleted_at` IS NULL";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $limit = (int) $perPage;
        $offset = (int) $offset;

        $sql = "SELECT 
                    `$table`.*, 
                    COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END), 0) - 
                    COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END), 0) AS balance
                FROM `$table`
                LEFT JOIN transactions t ON t.client_id = `$table`.id AND t.deleted_at IS NULL
                $whereClause
                GROUP BY `$table`.id
                ORDER BY `$table`.id DESC
                LIMIT $limit OFFSET $offset";

        $stmt = static::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
