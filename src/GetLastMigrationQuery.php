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
        private string $nameParam = "name",
        private string $versionParam = "version",
    ) {
    }

    /**
     * Retrieve the migration.
     */
    public function query(): MigrationInterface|false
    {
        $name = "";
        $version = "";
        $stmt = $this->connection->prepare($this->lastMigrationQuery);

        if ($stmt === false) {
            return false;
        }

        $stmt->execute();

        $stmt->bindColumn($this->nameParam, $name, PDO::PARAM_STR);
        $stmt->bindColumn($this->versionParam, $version, PDO::PARAM_STR);
        $stmt->fetch(PDO::FETCH_BOUND);

        if (\is_string($name) === false) {
            return false;
        }

        if (empty($name) === true) {
            return false;
        }

        if (\is_string($version) === false) {
            return false;
        }

        if (empty($version) === true) {
            return false;
        }

        $className = sprintf("Migration%s%s", $version, $name);

        if (\class_exists($className) === false) {
            return false;
        }

        $migration = new $className();

        if ($migration instanceof MigrationInterface === false) {
            return false;
        }

        return $migration;
    }
}
