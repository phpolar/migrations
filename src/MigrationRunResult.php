<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PhpContrib\Migration\MigrationRunStatus;

/**
 * Provides information about the result of running a migration.
 */
abstract readonly class MigrationRunResult
{
    public string $name;
    public int $version;
    public MigrationRunStatus $status;

    private const CLASS_PARSE_PATTERN =
    "/^(?:.*\\\\)?Migration(?<version>[[:digit:]]{13})(?<name>[\p{L}_][\p{L}\p{N}_]*)$/u";

    public function __construct(
        private string $migrationName,
        public int $durationMs,
    ) {
        $matches = [];

        if (
            preg_match(
                self::CLASS_PARSE_PATTERN,
                $migrationName,
                $matches
            ) === 1
        ) {
            ["name" => $name, "version" => $version] = $matches;
            $this->name = $name;
            $this->version = (int) $version;
        }
        $this->name ??= $migrationName;
        $this->version ??= 0;
    }
}
