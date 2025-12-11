<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PhpContrib\Migration\MigrationRunStatus;

/**
 * Provides information about the result of running a migration.
 */
final readonly class MigrationRunResult
{
    public function __construct(
        public string $migrationName,
        public MigrationRunStatus $status,
    ) {
    }

    public function completed(): bool
    {
        return $this->status === MigrationRunStatus::COMPLETED;
    }
}
