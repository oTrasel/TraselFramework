<?php

namespace Helpers\Facilities;

use Helpers\Core\Database;

class DB extends Database
{

    /**
     * Begins a new database transaction if one is not already active.
     *
     * @return void
     */
    public static function beginTransaction()
    {
        $pdo = self::getPdo();
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }
    }

    /**
     * Commits the current database transaction if one is active.
     *
     * @return void
     */
    public static function commit()
    {
        $pdo = self::getPdo();
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }

    /**
     * Rollback the current database transaction if one is active.
     *
     * @return void
     */
    public static function rollback()
    {
        $pdo = self::getPdo();
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
    }

    /**
     * Executes a SELECT query using a prepared statement and returns the result as an associative array.
     *
     * This method uses a shared PDO instance (retrieved via self::getPdo()) to execute
     * a read-only query. Parameters are bound securely to prevent SQL injection.
     *
     * @param string $sql     The SQL query to execute (should contain placeholders for parameters).
     * @param array  $params  An associative array of parameters to bind to the query (optional).
     *
     * @return array          The query results as an associative array. Returns an empty array if no rows are found.
     */
    public static function select(string $sql, array $params = []): array
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($pdo::FETCH_ASSOC);
    }
}
