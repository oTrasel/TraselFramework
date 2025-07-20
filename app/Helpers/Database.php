<?php

namespace Helpers;

use Exception;
use PDO;
use PDOException;

class Database
{

    private $DB_NAME;
    private $DB_USER;
    private $DB_PASSWORD;
    private $DB_HOST;
    private $DB_PORT = 5432;
    private $pdo;
    private $DB_CREATE;

    public function __construct()
    {
        $this->DB_NAME = $_ENV["DB_NAME"];
        $this->DB_USER = $_ENV["DB_USER"];
        $this->DB_PASSWORD = $_ENV["DB_PASSWORD"];
        $this->DB_HOST = $_ENV["DB_HOST"];
        $this->DB_PORT = $_ENV["DB_PORT"];

        $this->connect();
    }


    private function connect()
    {
        try {
            $pdo = new PDO(
                "pgsql:host={$this->DB_HOST};port={$this->DB_PORT};dbname={$this->DB_NAME}",
                $this->DB_USER,
                $this->DB_PASSWORD
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
            return;
        } catch (PDOException $e) {
                throw new Exception("Error: " . $e->getMessage());
        }
    }

    public function getPdo(){
        return $this->pdo;
    }
}
