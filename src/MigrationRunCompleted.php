<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PhpContrib\Migration\MigrationRunStatus;

/**
 * Provides information about the result of running a migration.
 */
final readonly class MigrationRunCompleted extends MigrationRunResult
{
    public MigrationRunStatus $status;

    public function __construct(
        string $migrationName,
        int $durationMs,
    ) {
        parent::__construct(
            migrationName: $migrationName,
            durationMs: $durationMs,
        );

        $this->status = MigrationRunStatus::COMPLETED;
    }
}
