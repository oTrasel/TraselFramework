<?php

namespace Helpers\Core;

use Exception;
use PDO;

/**
 * Class Model
 *
 * Base ORM model class for TraselFramework.
 * Extend this class to create your own models.
 *
 * @package Helpers
 */
abstract class Model
{
    /**
     * Table name in the database.
     * @var string
     */
    protected static string $table;

    /**
     * Primary key column name.
     * @var string
     */
    protected static string $primaryKey;

    /**
     * Whether the primary key is auto-increment.
     * @var bool
     */
    protected static bool $incremental = true;

    /**
     * Model attributes (column values).
     * @var array
     */
    protected array $attributes = [];

    /**
     * List of table columns.
     * @var array
     */
    protected static array $columns = [];

    /**
     * Whether this model exists in the database.
     * @var bool
     */
    protected bool $exists = false;

    /**
     * Model constructor.
     *
     * @param array $attributes
     * @param bool $exists
     */
    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->attributes = $attributes;
        $this->exists = $exists;
    }

    /**
     * Find a record by its primary key.
     *
     * @param int $id
     * @return static|null
     */
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

    /**
     * Get all records from the table.
     *
     * @return array
     */
    public static function getAll()
    {
        $pdo = Database::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($item) => (object)$item, $data);
    }

    /**
     * Save the model to the database (insert or update).
     *
     * @throws Exception
     */
    public function save()
    {
        $pdo = Database::getPdo();
        $table = static::$table;
        $pkName = static::$primaryKey;

        try {

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
        } catch (Exception $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete the record from the database.
     *
     * @throws Exception
     */
    public function delete(){
        $pdo = Database::getPdo();

        try{
            $table = static::$table;
            $pkValue = $this->attributes[static::$primaryKey];
            $pkName = static::$primaryKey;

            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$pkName} = :{$pkName};");
            $stmt->execute([
                $pkName => $pkValue
            ]);

            $this->exists = false;
            $this->attributes = [];
        }catch (Exception $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    /**
     * Magic getter for model attributes.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic setter for model attributes.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (in_array($key, static::$columns)) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get the model attributes as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
