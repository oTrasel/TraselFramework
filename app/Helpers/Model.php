<?php

namespace Helpers;

use PDO;

abstract class Model {

    private ?PDO $pdo;

    protected static string $table;
    protected static string $primaryKey;
    protected static bool $incremental = true;

    private function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function find(int $id){
        $stmt = $this->pdo->prepare("SELECT * FROM :table where :primaryKey = :id");
        $stmt->execute([
            'table' => static::$table,
            'primaryKey' => static::$primaryKey,
            'id' => $id
        ]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? static::mapToObject($data) : null;
    }

    private static function mapToObject(array $data){
        $object = new static();
        foreach ($data as $key => $value){
            $object->$key = $value;
        }

        return $object;
    }
}