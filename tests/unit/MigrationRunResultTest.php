<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PhpContrib\Migration\MigrationRunStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(MigrationRunResult::class)]
final class MigrationRunResultTest extends TestCase
{
    #[Test]
    #[TestWith(
        ["", MigrationRunStatus::COMPLETED],
    )]
    public function determinesIfStatusCompleted(string $migrationName, MigrationRunStatus $status)
    {
        $sut = new MigrationRunResult(
            migrationName: $migrationName,
            status: $status,
        );

        $result = $sut->completed();

        $this->assertTrue($result);
    }

    #[Test]
    #[TestWith(
        [
            "",
            MigrationRunStatus::PENDING,
        ]
    )]
    #[TestWith(
        [
            "",
            MigrationRunStatus::FAILED,
        ]
    )]
    public function determinesIfStatusNotCompleted(string $migrationName, MigrationRunStatus $status)
    {
        $sut = new MigrationRunResult(
            migrationName: $migrationName,
            status: $status,
        );

        $result = $sut->completed();

        $this->assertFalse($result);
    }
}
