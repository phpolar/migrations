<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(MigrationRunCompleted::class)]
#[CoversClass(MigrationRunResult::class)]
final class MigrationRunResultTest extends TestCase
{
    #[Test]
    #[TestDox("Shall parse migration name as expected")]
    #[TestWith([
        "Migration1765596282708CreateSomeRandomTable",
        "CreateSomeRandomTable",
        1765596282708,
    ])]
    #[TestWith([
        "Phpolar\\Migrations\\Migration1765596282708CreateSomeRandomTable",
        "CreateSomeRandomTable",
        1765596282708,
    ])]

    #[TestWith([
        "Migration1765596282708CréateÉxámple",
        "CréateÉxámple",
        1765596282708,
    ])]
    public function parsesAsExpected(
        string $migrationName,
        string $expectedName,
        int $expectedVersion,
    ) {
        $sut = new MigrationRunCompleted(
            durationMs: 0,
            migrationName: $migrationName,
        );

        $this->assertSame($expectedName, $sut->name);
        $this->assertSame($expectedVersion, $sut->version);
    }

    #[Test]
    #[TestDox("Shall use defaults when parsing migration name fails")]
    #[TestWith([
        "migrationName" => "MigrationCreateSomeRandomTable",
        "expectedName" => "MigrationCreateSomeRandomTable",
        "expectedVersion" => 0,
    ])]
    public function usesDefaultsWhenParsingFails(
        string $migrationName,
        string $expectedName,
        int $expectedVersion,
    ) {
        $sut = new MigrationRunCompleted(
            durationMs: 0,
            migrationName: $migrationName,
        );

        $this->assertSame($expectedName, $sut->name);
        $this->assertSame($expectedVersion, $sut->version);
    }
}
