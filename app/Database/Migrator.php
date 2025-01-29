<?php

namespace Notification\Database;

use DirectoryIterator;
use PDO;

class Migrator
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct(PDO $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = rtrim($migrationsPath, '/');
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function migrate(): void
    {
        $applied = $this->getAppliedMigrations();
        $toApply = [];
        $batch = $this->getLastBatch() + 1;

        foreach (new DirectoryIterator($this->migrationsPath) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            $filename = $fileInfo->getFilename();
            if (!str_ends_with($filename, '.php')) {
                continue;
            }

            if (!in_array($filename, $applied)) {
                $toApply[] = $filename;
            }
        }

        sort($toApply);

        foreach ($toApply as $migrationFile) {
            require_once $this->migrationsPath . '/' . $migrationFile;
            $className = $this->classNameFromFilename($migrationFile);
            $migrationClass = "Notification\\Database\\Migrations\\{$className}";

            if (!class_exists($migrationClass)) {
                echo "[Migrate] Class $migrationClass not found, skipping.\n";
                continue;
            }

            echo "[Migrate] Applying {$migrationFile}\n";
            $migrationInstance = new $migrationClass();
            $migrationInstance->up($this->db);

            $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (:mig, :batch)");
            $stmt->execute([':mig' => $migrationFile, ':batch' => $batch]);
        }
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getLastBatch(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        return (int)$stmt->fetchColumn();
    }

    private function classNameFromFilename(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $base);
        $parts = array_map('ucfirst', $parts);
        return implode('', $parts);
    }

}