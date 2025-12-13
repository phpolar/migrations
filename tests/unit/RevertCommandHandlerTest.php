<?php

namespace Phpolar\Migrations;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(RevertCommandHandler::class)]
final class RevertCommandHandlerTest extends TestCase
{
    const MIGRATION_DEST = __DIR__ . "/../__files__";

    #[Test]
    #[TestDox("Shall revert the last migration and log success")]
    #[TestWith([
        "Migration1700000000000CreateSomeRandomTable",
        "Revert of Migration1700000000000CreateSomeRandomTable completed successfully."
    ])]
    public function revertsMigrations(
        string $migrationName,
        string $expectedLogOutput,
    ) {
        $revertCommandMock = $this->createMock(RevertCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $loggerMock->expects($this->once())
            ->method("info")
            ->with($expectedLogOutput);

        $revertCommandMock->expects($this->once())
            ->method("execute")
            ->willReturn(true);

        $revertCommandMock->expects($this->once())
            ->method("getLastMigrationName")
            ->willReturn($migrationName);

        $sut = new RevertCommandHandler(
            revertCommand: $revertCommandMock,
            logger: $loggerMock,
        );

        $sut->revert();
    }

    #[Test]
    #[TestDox("Shall notify when reverting the last migration fails")]
    #[TestWith([
        "Migration revert of Migration1700000000000CreateSomeRandomTable failed or is pending.",
        "Migration1700000000000CreateSomeRandomTable",
    ])]
    public function notifiesRevertFailed(
        string $expectedLogOutput,
        string $migrationName,
    ) {
        $revertCommandMock = $this->createMock(RevertCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $loggerMock->expects($this->once())
            ->method("error")
            ->with($expectedLogOutput);

        $revertCommandMock->expects($this->once())
            ->method("execute")
            ->willReturn(false);

        $revertCommandMock->expects($this->once())
            ->method("getLastMigrationName")
            ->willReturn($migrationName);

        $sut = new RevertCommandHandler(
            revertCommand: $revertCommandMock,
            logger: $loggerMock,
        );

        $sut->revert();
    }
}
