<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

// phpcs:ignore
final class Migration1765073576565CreateSomeRandomTable implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        return MigrationRunStatus::COMPLETED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return MigrationRunStatus::COMPLETED;
    }
}
