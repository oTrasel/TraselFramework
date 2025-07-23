<?php

namespace Helpers;

use PDO;

abstract class Model {

    private ?PDO $pdo;

    protected static string $table;
    protected static string $primaryKey;
    protected static bool $incremental = true;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function find(int $id){
        $table = static::$table;
        $primaryKey = static::$primaryKey;
        $stmt = $this->pdo->prepare("SELECT * FROM {$table} where {$primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? (object)$data : null;
    }

    public function getAll(){
        $stmt = $this->pdo->prepare("SELECT * FROM " . static::$table);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($item) => (object)$item, $data);
    }
}