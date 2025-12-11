<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PhpContrib\Migration\MigrationInterface;

/**
 * Retrieves the last successfully run migration.
 */
readonly class GetLastMigrationQuery
{
    public function __construct(
        private PDO $connection,
        private string $lastMigrationQuery,
    ) {
    }

    /**
     * Retrieve the migration.
     */
    public function query(): MigrationInterface|false
    {
        $name = "";
        $stmt = $this->connection->prepare($this->lastMigrationQuery);

        if ($stmt === false) {
            return false;
        }

        $stmt->execute();

        $stmt->bindColumn("name", $name, PDO::PARAM_STR);
        $stmt->fetch(PDO::FETCH_BOUND);

        if (\is_string($name) === false) {
            return false;
        }

        if (empty($name) === true) {
            return false;
        }

        if (\class_exists($name) === false) {
            return false;
        }

        $migration = new $name();

        if ($migration instanceof MigrationInterface === false) {
            return false;
        }

        return $migration;
    }
}
