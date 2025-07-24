<?php

namespace Helpers;

use Exception;
use PDO;

abstract class Model
{

    protected static string $table;
    protected static string $primaryKey;
    protected static bool $incremental = true;
    protected array $attributes = [];
    protected static array $columns = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->attributes = $attributes;
        $this->exists = $exists;
    }

    public static function find(int $id): ?self
    {
        $pdo = Database::getPdo();
        $table = static::$table;
        $primaryKey = static::$primaryKey;

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new static($data, true);
    }

    public static function getAll()
    {
        $pdo = Database::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($item) => (object)$item, $data);
    }

    public function save()
    {
        $pdo = Database::getPdo();
        $table = static::$table;
        $pkName = static::$primaryKey;

        try {
            $pdo->beginTransaction();
            $fields = array_keys($this->attributes);
            if ($this->exists) {

                $fields = array_filter($fields, fn($f) => $f !== $pkName);
                $set = implode(', ', array_map(fn($f) => "$f = :$f", $fields));
                $stmt = $pdo->prepare("UPDATE {$table} SET {$set} WHERE {$pkName} = :{$pkName}");
                $stmt->execute($this->attributes);
            } else {

                $columns = implode(', ', $fields);
                $placeholders = implode(', ', array_map(fn($f) => ":$f", $fields));
                $stmt = $pdo->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
                $stmt->execute($this->attributes);

                if (static::$incremental) {
                    $this->attributes[$pkName] = $pdo->lastInsertId();
                }

                $this->exists = true;
            }
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function delete(){
        $pdo = Database::getPdo();

        try{
            $pdo->beginTransaction();

            $table = static::$table;
            $pkValue = $this->attributes[static::$primaryKey];
            $pkName = static::$primaryKey;

            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$pkName} = :{$pkName};");
            $stmt->execute([
                $pkName => $pkValue
            ]);

            $pdo->commit();

            $this->exists = false;
            $this->attributes = [];
        }catch (Exception $e) {
            if ($pdo->inTransaction()){
                $pdo->rollBack();
            }
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        if (in_array($key, static::$columns)) {
            $this->attributes[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
