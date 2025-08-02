<?php

namespace Helpers\Facilities;

use Helpers\Core\Database;

class DB extends Database{

    /**
     * Begins a new database transaction if one is not already active.
     *
     * @return void
     */
    public static function beginTransaction(){
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
    public static function commit(){
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
    public static function rollback(){
        $pdo = self::getPdo();
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
    }

    public static function select(string $sql, array $paramns){
        
    }

}