<?php
require_once (__DIR__ . '/../../vendor/autoload.php');

use Helpers\Core\Database;

/**
 * Class Migrate
 *
 * Handles database migrations: applying and rolling back migration files.
 */
class Migrate
{
    /**
     * @var PDO The PDO database connection.
     */
    private $pdo;

    /**
     * @var string Path to the migrations directory.
     */
    private $migrationsPath;

    /**
     * @var string Path to the environment (.env) directory.
     */
    private $envPath;

    /**
     * Migrate constructor.
     *
     * @param int $argc
     * @param array $argv
     */
    public function __construct($argc, $argv)
    {
        $this->migrationsPath = __DIR__ . '/../Database/migrations';
        $this->envPath = __DIR__ . '../../../';
        
        $this->validateArguments($argc, $argv);
        $this->initializeDatabase();
        $this->run($argv[1]);
    }

    /**
     * Validate CLI arguments.
     *
     * @param int $argc
     * @param array $argv
     */
    private function validateArguments($argc, $argv)
    {
        $options = ["up", "rollback"];

        if ($argc < 2 || !in_array($argv[1], $options)) {
            $this->displayError("Invalid Option. Use: composer migrate or composer migrate:rollback");
        }
    }

    /**
     * Initialize database connection and environment.
     *
     * @throws Exception
     */
    private function initializeDatabase()
    {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($this->envPath);
            $dotenv->load();

            $this->pdo = Database::getPdo();
            $this->createTableMigrations();

        } catch (Exception $e) {
            $this->displayError("Database initialization error: " . $e->getMessage());
        }
    }

    /**
     * Run the migration command.
     *
     * @param string $command
     */
    private function run($command)
    {
        try {
            $files = $this->getMigrationFiles();
            
            if (empty($files)) {
                $this->displaySuccess("Nothing to migrate");
                return;
            }

            $result = $this->{$command}($files);
            $this->displaySuccess($result);

        } catch (Exception $e) {
            $this->displayError("Migration error: " . $e->getMessage());
        }
    }

    /**
     * Get all migration files.
     *
     * @return array
     * @throws Exception
     */
    private function getMigrationFiles()
    {
        $files = glob($this->migrationsPath . '/*.php');
        
        if ($files === false) {
            throw new Exception("Failed to read migrations directory: {$this->migrationsPath}");
        }

        sort($files);
        return $files;
    }

    /**
     * Apply all new migrations.
     *
     * @param array $files
     * @return string
     * @throws Exception
     */
    private function up($files)
    {
        $appliedMigrations = [];
        $batch = $this->getNextBatch();

        foreach ($files as $file) {
            $migrationName = basename($file);

            if ($this->isMigrationApplied($migrationName)) {
                continue;
            }

            $this->applyMigration($file, $migrationName, $batch);
            $appliedMigrations[] = $migrationName;
        }

        return empty($appliedMigrations) 
            ? "No new migrations to apply." 
            : "Applied migrations:\n- " . implode("\n- ", $appliedMigrations);
    }

    /**
     * Rollback the last batch of migrations.
     *
     * @param array $files
     * @return string
     * @throws Exception
     */
    private function rollback($files)
    {
        $lastBatch = $this->getLastBatch();

        if ($lastBatch === 0) {
            return "Nothing to rollback";
        }

        $rollbackMigrations = [];
        $migrations = $this->getMigrationsByBatch($lastBatch);

        foreach (array_reverse($migrations) as $migrationName) {
            $file = $this->migrationsPath . "/{$migrationName}";
            
            $this->rollbackMigration($file, $migrationName, $lastBatch);
            $rollbackMigrations[] = $migrationName;
        }

        return "Rolled back migrations:\n- " . implode("\n- ", $rollbackMigrations);
    }

    /**
     * Check if a migration has already been applied.
     *
     * @param string $migrationName
     * @return bool
     */
    private function isMigrationApplied($migrationName)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = :migration");
        $stmt->execute([':migration' => $migrationName]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Apply a single migration.
     *
     * @param string $file
     * @param string $migrationName
     * @param int $batch
     * @throws Exception
     */
    private function applyMigration($file, $migrationName, $batch)
    {
        if (!file_exists($file)) {
            throw new Exception("Migration file not found: {$migrationName}");
        }

        $migration = require $file;

        if (!method_exists($migration, 'up')) {
            throw new Exception("Migration {$migrationName} does not have an 'up' method");
        }

        try {
            $this->pdo->beginTransaction();

            $migration->up($this->pdo);

            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
            $stmt->execute([
                ':migration' => $migrationName,
                ':batch' => $batch,
            ]);

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to apply migration {$migrationName}: " . $e->getMessage());
        }
    }

    /**
     * Rollback a single migration.
     *
     * @param string $file
     * @param string $migrationName
     * @param int $batch
     * @throws Exception
     */
    private function rollbackMigration($file, $migrationName, $batch)
    {
        if (!file_exists($file)) {
            throw new Exception("Migration file not found: {$migrationName}");
        }

        $migration = require $file;

        if (!method_exists($migration, 'down')) {
            throw new Exception("Migration {$migrationName} does not have a 'down' method");
        }

        try {
            $this->pdo->beginTransaction();

            $migration->down($this->pdo);

            $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = :migration AND batch = :batch");
            $stmt->execute([
                ':migration' => $migrationName,
                ':batch' => $batch,
            ]);

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to rollback migration {$migrationName}: " . $e->getMessage());
        }
    }

    /**
     * Create the migrations table if it does not exist.
     *
     * @throws Exception
     */
    private function createTableMigrations()
    {
        $stmt = $this->pdo->query("SELECT to_regclass('migrations') as exists");
        $exists = $stmt->fetchColumn();
        
        if ($exists !== null) {
            return;
        }

        try {
            $this->pdo->beginTransaction();

            $this->pdo->exec("
                CREATE TABLE migrations(
                    id SERIAL PRIMARY KEY,
                    migration VARCHAR(500) NOT NULL,
                    batch INTEGER NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT NOW()
                );
            ");

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to create migrations table: " . $e->getMessage());
        }
    }

    /**
     * Get the next batch number for migrations.
     *
     * @return int
     * @throws Exception
     */
    private function getNextBatch()
    {
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['next_batch'];
        } catch (PDOException $e) {
            throw new Exception("Failed to get next batch number: " . $e->getMessage());
        }
    }

    /**
     * Get the last batch number for migrations.
     *
     * @return int
     * @throws Exception
     */
    private function getLastBatch()
    {
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(MAX(batch), 0) as max_batch FROM migrations");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['max_batch'];
        } catch (PDOException $e) {
            throw new Exception("Failed to get last batch number: " . $e->getMessage());
        }
    }

    /**
     * Get all migrations for a given batch.
     *
     * @param int $batch
     * @return array
     * @throws Exception
     */
    private function getMigrationsByBatch($batch)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
            $stmt->execute([':batch' => $batch]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Failed to get migrations for batch {$batch}: " . $e->getMessage());
        }
    }

    /**
     * Display an error message and exit.
     *
     * @param string $message
     */
    private function displayError($message)
    {
        echo "\n\033[31m[ERROR]\033[0m {$message}\n\n";
        exit;
    }

    /**
     * Display a success message and exit.
     *
     * @param string $message
     */
    private function displaySuccess($message)
    {
        echo "\n\033[32m[SUCCESS]\033[0m {$message}\n\n";
        exit;
    }
}

$migrate = new Migrate($argc, $argv);