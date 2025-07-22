<?php

namespace Helpers;

use Exception;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    private static $DB_NAME;
    private static $DB_USER;
    private static $DB_PASSWORD;
    private static $DB_HOST;
    private static $DB_PORT = 5432;

    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            self::$DB_NAME = $_ENV["DB_NAME"];
            self::$DB_USER = $_ENV["DB_USER"];
            self::$DB_PASSWORD = $_ENV["DB_PASSWORD"];
            self::$DB_HOST = $_ENV["DB_HOST"];
            self::$DB_PORT = $_ENV["DB_PORT"] ?? 5432;

            try {
                self::$pdo = new PDO(
                    "pgsql:host=" . self::$DB_HOST . ";port=" . self::$DB_PORT . ";dbname=" . self::$DB_NAME,
                    self::$DB_USER,
                    self::$DB_PASSWORD
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new Exception("Erro ao conectar ao banco: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
