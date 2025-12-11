<?php

namespace Phpolar\Migrations;

use Migration1765073576565CreateSomeRandomTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(RunCommandHandler::class)]
final class RunCommandHandlerTest extends TestCase
{
    #[Test]
    #[TestDox("Shall run migrations and log success")]
    #[TestWith([
        "expectedLogOutput" => "All migrations executed successfully."
    ])]
    public function runsMigrations(
        string $expectedLogOutput,
    ) {
        $runCommandMock = $this->createMock(RunCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $pendingMigrationQueryMock = $this->createMock(GetPendingMigrationsQuery::class);
        $pendingMigrations = [new Migration1765073576565CreateSomeRandomTable()];

        $loggerMock->expects($this->once())
            ->method("info")
            ->with($expectedLogOutput);

        $pendingMigrationQueryMock->expects($this->once())
            ->method("query")
            ->willReturn($pendingMigrations);

        $runCommandMock->expects($this->once())
            ->method("execute")
            ->with($pendingMigrations)
            ->willReturn(true);

        $sut = new RunCommandHandler(
            runCommand: $runCommandMock,
            pendingMigrationQuery: $pendingMigrationQueryMock,
            logger: $loggerMock,
        );

        $sut->run();
    }

    #[Test]
    #[TestDox("Shall notify failure to run a migration")]
    #[TestWith([
        "expectedLogOutput" => "One or more of the migrations failed."
    ])]
    public function logsFailure(
        string $expectedLogOutput,
    ) {
        $runCommandMock = $this->createMock(RunCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $pendingMigrationQueryMock = $this->createMock(GetPendingMigrationsQuery::class);
        $pendingMigrations = [new Migration1765073576565CreateSomeRandomTable()];

        $loggerMock->expects($this->once())
            ->method("error")
            ->with($expectedLogOutput);

        $pendingMigrationQueryMock->expects($this->once())
            ->method("query")
            ->willReturn($pendingMigrations);

        $runCommandMock->expects($this->once())
            ->method("execute")
            ->with($pendingMigrations)
            ->willReturn(false);

        $sut = new RunCommandHandler(
            runCommand: $runCommandMock,
            pendingMigrationQuery: $pendingMigrationQueryMock,
            logger: $loggerMock,
        );

        $sut->run();
    }

    #[Test]
    #[TestDox("Shall notify failure to query pending migrations")]
    #[TestWith([
        "expectedLogOutput" => "The pending migration query failed."
    ])]
    public function logsQueryFailure(
        string $expectedLogOutput,
    ) {
        $runCommandMock = $this->createMock(RunCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $pendingMigrationQueryMock = $this->createMock(GetPendingMigrationsQuery::class);

        $loggerMock->expects($this->once())
            ->method("error")
            ->with($expectedLogOutput);

        $pendingMigrationQueryMock->expects($this->once())
            ->method("query")
            ->willReturn(false);

        $runCommandMock->expects($this->never())
            ->method("execute");

        $sut = new RunCommandHandler(
            runCommand: $runCommandMock,
            pendingMigrationQuery: $pendingMigrationQueryMock,
            logger: $loggerMock,
        );

        $sut->run();
    }
}
