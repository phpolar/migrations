<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * Provides migration instances that have not been executed.
 */
readonly class GetPendingMigrationsQuery
{
    /**
     * @param string[] $candidates A list of potentially pending migrations
     */
    public function __construct(
        private PDO $connection,
        private string $completedMigrationsQuery,
        private array $candidates,
        private string $statusColumn,
    ) {
    }

    /**
     * Retrieve pending migrations.
     *
     * @return MigrationInterface[]|false
     */
    public function query(): array|false
    {
        $stmt = $this->connection->prepare($this->completedMigrationsQuery);

        if ($stmt === false) {
            return false;
        }

        $result = $stmt->execute([
            $this->statusColumn => MigrationRunStatus::COMPLETED->name
        ]);

        if ($result === false) {
            return false;
        }

        return array_map(
            $this->getInstance(...),
            array_diff(
                $this->candidates,
                $stmt->fetchAll(PDO::FETCH_COLUMN, 0),
            ),
        );
    }

    private function getInstance(string $name): MigrationInterface
    {
        /**
         * @var MigrationInterface
         */
        return new $name();
    }
}
