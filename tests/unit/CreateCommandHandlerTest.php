<?php

namespace Phpolar\Migrations;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(CreateCommandHandler::class)]
final class CreateCommandHandlerTest extends TestCase
{
    const MIGRATION_DEST = __DIR__ . "/../__files__";

    #[Test]
    #[TestDox("Shall create migrations and log success")]
    #[TestWith([
        "migrationsDir" => self::MIGRATION_DEST,
        "migrationName" => "CreateSomeRandomTable",
        "expectedLogOutput" => "Migration created at " . self::MIGRATION_DEST . "/CreateSomeRandomTable.php"
    ])]
    #[TestWith([
        "migrationsDir" => self::MIGRATION_DEST . DIRECTORY_SEPARATOR,
        "migrationName" => "CreateSomeRandomTable",
        "expectedLogOutput" => "Migration created at " . self::MIGRATION_DEST . "/CreateSomeRandomTable.php"
    ])]
    public function createsMigrations(
        string $migrationsDir,
        string $migrationName,
        string $expectedLogOutput,
    ) {
        $createCommandMock = $this->createMock(CreateCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $loggerMock->expects($this->once())
            ->method("info")
            ->with($expectedLogOutput);

        $createCommandMock->expects($this->once())
            ->method("execute")
            ->with(
                $migrationName,
                $migrationsDir,
            )->willReturn(true);

        $sut = new CreateCommandHandler(
            createCommand: $createCommandMock,
            logger: $loggerMock,
        );

        $sut->create($migrationsDir, $migrationName);
    }

    #[Test]
    #[TestDox("Shall notify failure to create migration")]
    #[TestWith([
        "migrationsDir" => self::MIGRATION_DEST,
        "migrationName" => "CreateSomeRandomTable",
        "expectedLogOutput" => "Failed to create migration CreateSomeRandomTable"
    ])]
    #[TestWith([
        "migrationsDir" => self::MIGRATION_DEST . DIRECTORY_SEPARATOR,
        "migrationName" => "CreateSomeRandomTable",
        "expectedLogOutput" => "Failed to create migration CreateSomeRandomTable"
    ])]
    public function logsFailure(
        string $migrationsDir,
        string $migrationName,
        string $expectedLogOutput,
    ) {
        $createCommandMock = $this->createMock(CreateCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $loggerMock->expects($this->once())
            ->method("error")
            ->with($expectedLogOutput);

        $createCommandMock->expects($this->once())
            ->method("execute")
            ->with(
                $migrationName,
                $migrationsDir,
            )->willReturn(false);

        $sut = new CreateCommandHandler(
            createCommand: $createCommandMock,
            logger: $loggerMock,
        );

        $sut->create($migrationsDir, $migrationName);
    }
}
