<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PDOStatement;
use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * Reverts the migration.
 */
readonly class RevertCommand
{
    public function __construct(
        private MigrationInterface $migration,
        private PDO $connection,
        private string $migrationRecordDeleteStatement,
        private string $nameColumn
    ) {
    }

    public function getLastMigrationName()
    {
        return $this->migration::class;
    }

    /**
     * Execute the command.
     */
    public function execute(): bool
    {
        $status = $this->migration->down($this->connection);

        return match ($status) {
            MigrationRunStatus::COMPLETED => $this->deleteMigrationRecord(),
            MigrationRunStatus::FAILED => false,
            MigrationRunStatus::PENDING => false,
        };
    }

    private function deleteMigrationRecord()
    {
        $stmt = $this->connection->prepare(
            $this->migrationRecordDeleteStatement
        );

        return $stmt instanceof PDOStatement
            && $stmt->execute(
                [$this->nameColumn => \get_class($this->migration)]
            );
    }
}
