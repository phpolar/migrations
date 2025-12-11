<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * Runs migrations.
 */
readonly class RunCommand
{
    public function __construct(
        private PDO $connection,
        private string $insertMigrationResultStmt,
        private string $nameColumn,
        private string $statusColumn,
    ) {
    }

    /**
     * Run the given migrations.
     *
     * @param MigrationInterface[] $pendingMigrations
     */
    public function execute(array $pendingMigrations): bool
    {
        return array_all(
            array_map(
                $this->runMigration(...),
                $pendingMigrations,
            ),
            $this->runWasSuccessful(...),
        );
    }

    private function runMigration(MigrationInterface $pendingMigration): MigrationRunResult
    {
        return new MigrationRunResult(
            migrationName: get_class($pendingMigration),
            status: $pendingMigration->up($this->connection),
        );
    }

    private function recordSuccessfulMigrationRun(string $migrationName): bool
    {
        $stmt = $this->connection->prepare($this->insertMigrationResultStmt);

        return $stmt !== false && $stmt->execute(
            [
                $this->nameColumn => $migrationName,
                $this->statusColumn => MigrationRunStatus::COMPLETED->name,
            ]
        );
    }

    private function runWasSuccessful(MigrationRunResult $result): bool
    {
        return $result->completed()
            && $this->recordSuccessfulMigrationRun($result->migrationName);
    }
}
