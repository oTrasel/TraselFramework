<?php

require_once(__DIR__ . '/vendor/autoload.php');

use Helpers\Database;

class Migrate
{
    private $pdo;

    public function __construct($argc, $argv)
    {
        $options = ["up", "rollback"];

        if ($argc < 2 || !in_array($argv[1], $options)) {
            echo "------------------- Invalid Option -------------------";
            exit(1);
        }

        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/app");
            $dotenv->load();

            $dataBase = new Database();
            $this->pdo = $dataBase->getPdo();

            $files = glob(__DIR__ . '/app/database/migrations/*.php');

            if (count($files) === 0) {
                echo "Nothing to migrate";
                exit(0);
            }

            sort($files);
            $this->createTableMigrations();

            echo $this->{$argv[1]}($files);


        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }

    private function up($files)
    {
        $appliedMigrations = '';
        $batch = $this->getNextBatch();

        foreach ($files as $file) {
            $migrationName = basename($file);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = :migration");
            $stmt->execute([':migration' => $migrationName]);
            $alreadyRun = $stmt->fetchColumn() > 0;

            if ($alreadyRun) {
                continue;
            }

            $migration = require $file;

            if (method_exists($migration, 'up')) {
                try {
                    $this->pdo->beginTransaction();

                    $migration->up($this->pdo);

                    $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
                    $stmt->execute([
                        ':migration' => $migrationName,
                        ':batch' => $batch,
                    ]);

                    $this->pdo->commit();
                    $appliedMigrations .= PHP_EOL . $migrationName . " - Migrated";

                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    throw new Exception("Erro ao migrar {$migrationName}: " . $e->getMessage());
                }
            }
        }

        return $appliedMigrations ?: "Nenhuma nova migração aplicada.";
    }

    private function rollback()
    {
        $lastBatch = $this->getLastBatch();

        if ($lastBatch === 0) {
            return "Nothing to Rollback";
        }

        $rollbackMigrations = '';
        $migrations = $this->getMigrationsByBatch($lastBatch);

        foreach (array_reverse($migrations) as $migrationName) {
            $file = __DIR__ . "/app/database/migrations/{$migrationName}";

            if (!file_exists($file)) {
                throw new Exception("Arquivo de migração não encontrado: {$migrationName}");
            }

            $migration = require $file;

            if (method_exists($migration, 'down')) {
                try {
                    $this->pdo->beginTransaction();

                    $migration->down($this->pdo);

                    $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = :migration AND batch = :batch");
                    $stmt->execute([
                        ':migration' => $migrationName,
                        ':batch' => $lastBatch,
                    ]);

                    $this->pdo->commit();

                    $rollbackMigrations .= PHP_EOL . $migrationName . " - Rollback";
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    throw new Exception("Erro no rollback de {$migrationName}: " . $e->getMessage());
                }
            }
        }

        return $rollbackMigrations;
    }

    private function createTableMigrations()
    {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations(
                    id serial primary key,
                    migration varchar(500) not null,
                    batch integer not null,
                    created_at TIMESTAMP NOT NULL DEFAULT NOW()
                );
            ");

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao criar tabela de migrations: " . $e->getMessage());
        }
    }

    private function getNextBatch()
    {
        try {
            $stmt = $this->pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($row['max_batch'] ?? 0) + 1;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter próximo batch: " . $e->getMessage());
        }
    }

    private function getLastBatch()
    {
        try {
            $stmt = $this->pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['max_batch'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter último batch: " . $e->getMessage());
        }
    }

    private function getMigrationsByBatch($batch)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
            $stmt->execute([':batch' => $batch]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar migrations do batch: " . $e->getMessage());
        }
    }
}

$migrate = new Migrate($argc, $argv);
