<?php

namespace App\models;

use App\core\BaseModel;
use PDO;

/**
 * Transaction model manages transaction records linked to clients and users.
 * Supports soft deletion and provides methods for common transaction queries.
 */
class Transaction extends BaseModel
{
    protected static $table = 'transactions';
    protected static $fields = ['id', 'client_id', 'user_id', 'amount', 'type', 'description', 'date', 'created_at', 'updated_at'];
    protected static $required = ['client_id', 'user_id', 'amount', 'type', 'date'];
    protected static $softDelete = true;

    /**
     * Get the client associated with this transaction.
     *
     * @return Client|null
     */
    public function client()
    {
        return Client::find($this->client_id);
    }

    /**
     * Delete transactions by client ID.
     * Supports soft delete if enabled.
     *
     * @param int $clientId
     * @return bool
     */
    public static function deleteByClientId($clientId)
    {
        $table = static::table();
        if (static::$softDelete) {
            $sql = "UPDATE `$table` SET deleted_at = NOW() WHERE client_id = ?";
        } else {
            $sql = "DELETE FROM `$table` WHERE client_id = ?";
        }
        $stmt = self::db()->prepare($sql);
        return $stmt->execute([$clientId]);
    }

    /**
     * Get transactions filtered by criteria with pagination.
     *
     * @param array  $filters   Column => value filters
     * @param string $dateFrom  Start date (inclusive)
     * @param string $dateTo    End date (inclusive)
     * @param int    $offset    Offset for pagination
     * @param int    $limit     Number of records to fetch
     * @return array            List of transactions
     */
    public static function getMovements($filters, $dateFrom, $dateTo, $offset, $limit)
    {
        $params = [];
        $where = self::buildWhereClause($filters, $dateFrom, $dateTo, $params);

        $sql = "SELECT * FROM " . self::table() .
            " $where ORDER BY date DESC, id DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int) $limit;
        $params[':offset'] = (int) $offset;

        $stmt = self::db()->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total income and expenses for given filters and date range.
     *
     * @param array  $filters
     * @param string $dateFrom
     * @param string $dateTo
     * @return array|null  Associative array with 'total_income' and 'total_expenses'
     */
    public static function getTotals($filters, $dateFrom, $dateTo)
    {
        $params = [];
        $where = self::buildWhereClause($filters, $dateFrom, $dateTo, $params);

        $sql = "SELECT 
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expenses
            FROM " . self::table() . " $where";
        $stmt = self::db()->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get count of transactions matching filters and date range.
     *
     * @param array  $filters
     * @param string $dateFrom
     * @param string $dateTo
     * @return int
     */
    public static function getMovementsCount($filters, $dateFrom, $dateTo)
    {
        $params = [];
        $where = self::buildWhereClause($filters, $dateFrom, $dateTo, $params);

        $sql = "SELECT COUNT(*) FROM " . self::table() . " $where";
        $stmt = self::db()->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Build WHERE clause SQL and bind parameters for filters and date range.
     *
     * @param array  $filters
     * @param string $dateFrom
     * @param string $dateTo
     * @param array  &$params Output bind params
     * @return string WHERE clause or empty string
     */
    private static function buildWhereClause($filters, $dateFrom, $dateTo, &$params)
    {
        $where = [];
        foreach ($filters as $col => $val) {
            $where[] = "`$col` = :$col";
            $params[":$col"] = $val;
        }
        $where[] = 'deleted_at IS NULL';
        if ($dateFrom && $dateTo) {
            $where[] = "date BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $dateFrom;
            $params[':date_to'] = $dateTo;
        }
        return $where ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    /**
     * Bind parameters to PDO statement, handling limit and offset as integers.
     *
     * @param \PDOStatement $stmt
     * @param array        $params
     * @return void
     */
    private static function bindParams($stmt, $params)
    {
        foreach ($params as $k => $v) {
            if ($k === ':limit' || $k === ':offset') {
                $stmt->bindValue($k, (int) $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
            }
        }
    }
}
