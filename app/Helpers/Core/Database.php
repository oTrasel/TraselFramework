<?php

namespace Helpers\Core;

use Exception;
use PDO;
use PDOException;

/**
 * Class Database
 *
 * Singleton class for managing the PDO connection to the database.
 * Reads configuration from environment variables.
 *
 * @package Helpers
 */
class Database
{
    /**
     * The PDO instance.
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Database name.
     * @var string
     */
    private static string $DB_NAME;

    /**
     * Database user.
     * @var string
     */
    private static string $DB_USER;

    /**
     * Database password.
     * @var string
     */
    private static string $DB_PASSWORD;

    /**
     * Database host.
     * @var string
     */
    private static string $DB_HOST;

    /**
     * Database port.
     * @var int
     */
    private static int $DB_PORT = 5432;

    /**
     * Get the PDO connection instance.
     *
     * @return PDO
     * @throws Exception
     */
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
                throw new Exception("Database connection error: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
