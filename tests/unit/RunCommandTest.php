<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PDOStatement;
use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RunCommand::class)]
#[UsesClass(MigrationRunResult::class)]
#[CoversClass(StreamLogger::class)]
final class RunCommandTest extends TestCase
{
    #[Test]
    #[TestDox("Shall run migration and record migration completion")]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`)
        VALUES (:name, :status);
        SQL,
        "name",
        "status"
    ])]
    public function runsMigration(
        string $insertStatement,
        string $nameColumn,
        string $statusColumn,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $migrationMock = $this->createMock(MigrationInterface::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($insertStatement)
            ->willReturn($stmtMock);
        $migrationMock->expects($this->once())
            ->method("up")
            ->with()
            ->willReturn(MigrationRunStatus::COMPLETED);
        $stmtMock->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            nameColumn: $nameColumn,
            statusColumn: $statusColumn,
        );

        $result = $sut->execute([$migrationMock]);

        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox("Shall run all migrations and record migration completion")]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`)
        VALUES (:name, :status);
        SQL,
        "name",
        "status",
    ])]
    public function runsAllMigration(
        string $insertStatement,
        string $nameColumn,
        string $statusColumn,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $migrationMock0 = $this->createMock(MigrationInterface::class);
        $migrationMock1 = $this->createMock(MigrationInterface::class);
        $migrationMock2 = $this->createMock(MigrationInterface::class);
        $migrationMock3 = $this->createMock(MigrationInterface::class);
        $stmtMock0 = $this->createMock(PDOStatement::class);
        $stmtMock1 = $this->createMock(PDOStatement::class);
        $stmtMock2 = $this->createMock(PDOStatement::class);
        $stmtMock3 = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->exactly(4))
            ->method("prepare")
            ->with($insertStatement)
            ->willReturnOnConsecutiveCalls(
                $stmtMock0,
                $stmtMock1,
                $stmtMock2,
                $stmtMock3,
            );
        $migrationMock0->expects($this->once())
            ->method("up")
            ->with()
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock1->expects($this->once())
            ->method("up")
            ->with()
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock2->expects($this->once())
            ->method("up")
            ->with()
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock3->expects($this->once())
            ->method("up")
            ->with()
            ->willReturn(MigrationRunStatus::COMPLETED);
        $stmtMock0->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock0),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock1),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock2),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock3->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock3),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            nameColumn: $nameColumn,
            statusColumn: $statusColumn,
        );

        $result = $sut->execute(
            [
                $migrationMock0,
                $migrationMock1,
                $migrationMock2,
                $migrationMock3,
            ]
        );

        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox("Shall run all migrations and notify if one or more fails")]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`)
        VALUES (:name, :status);
        SQL,
        "name",
        "status",
    ])]
    public function runsAllMigrationOneFails(
        string $insertStatement,
        string $nameColumn,
        string $statusColumn,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $migrationMock0 = $this->createMock(MigrationInterface::class);
        $migrationMock1 = $this->createMock(MigrationInterface::class);
        $migrationMock2 = $this->createMock(MigrationInterface::class);
        $migrationMock3 = $this->createMock(MigrationInterface::class);
        $stmtMock0 = $this->createMock(PDOStatement::class);
        $stmtMock1 = $this->createMock(PDOStatement::class);
        $stmtMock2 = $this->createMock(PDOStatement::class);
        $stmtMock3 = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->exactly(3))
            ->method("prepare")
            ->with($insertStatement)
            ->willReturnOnConsecutiveCalls(
                $stmtMock0,
                $stmtMock1,
                $stmtMock2,
                $stmtMock3,
            );
        $migrationMock0->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock1->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock2->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock3->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::FAILED);
        $stmtMock0->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock0),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock1),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock2),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock3->expects($this->never())
            ->method("execute");

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            nameColumn: $nameColumn,
            statusColumn: $statusColumn,
        );

        $result = $sut->execute(
            [
                $migrationMock0,
                $migrationMock1,
                $migrationMock2,
                $migrationMock3,
            ]
        );

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox(
        "Shall run all migrations and notify if one or more prepare of result record fails"
    )]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`)
        VALUES (:name, :status);
        SQL,
        "name",
        "status",
    ])]
    public function runsAllMigrationOnePrepareFails(
        string $insertStatement,
        string $nameColumn,
        string $statusColumn,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $migrationMock0 = $this->createMock(MigrationInterface::class);
        $migrationMock1 = $this->createMock(MigrationInterface::class);
        $migrationMock2 = $this->createMock(MigrationInterface::class);
        $migrationMock3 = $this->createMock(MigrationInterface::class);
        $stmtMock0 = $this->createMock(PDOStatement::class);
        $stmtMock1 = $this->createMock(PDOStatement::class);
        $stmtMock2 = $this->createMock(PDOStatement::class);
        $stmtMock3 = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->exactly(4))
            ->method("prepare")
            ->with($insertStatement)
            ->willReturnOnConsecutiveCalls(
                $stmtMock0,
                $stmtMock1,
                $stmtMock2,
                false,
            );
        $migrationMock0->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock1->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock2->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock3->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $stmtMock0->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock0),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock1),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock2),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock3->expects($this->never())
            ->method("execute");

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            nameColumn: $nameColumn,
            statusColumn: $statusColumn,
        );

        $result = $sut->execute(
            [
                $migrationMock0,
                $migrationMock1,
                $migrationMock2,
                $migrationMock3,
            ]
        );

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox(
        "Shall run all migrations and notify if one or more execute of result record fails"
    )]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`)
        VALUES (:name, :status);
        SQL,
        "name",
        "status",
    ])]
    public function runsAllMigrationOneExecuteFails(
        string $insertStatement,
        string $nameColumn,
        string $statusColumn,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $migrationMock0 = $this->createMock(MigrationInterface::class);
        $migrationMock1 = $this->createMock(MigrationInterface::class);
        $migrationMock2 = $this->createMock(MigrationInterface::class);
        $migrationMock3 = $this->createMock(MigrationInterface::class);
        $stmtMock0 = $this->createMock(PDOStatement::class);
        $stmtMock1 = $this->createMock(PDOStatement::class);
        $stmtMock2 = $this->createMock(PDOStatement::class);
        $stmtMock3 = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->exactly(4))
            ->method("prepare")
            ->with($insertStatement)
            ->willReturnOnConsecutiveCalls(
                $stmtMock0,
                $stmtMock1,
                $stmtMock2,
                $stmtMock3,
            );
        $migrationMock0->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock1->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock2->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $migrationMock3->expects($this->once())
            ->method("up")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);
        $stmtMock0->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock0),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock1),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock2),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);
        $stmtMock3->expects($this->once())
            ->method("execute")
            ->with(
                [
                    $nameColumn => get_class($migrationMock2),
                    $statusColumn => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(false);

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            nameColumn: $nameColumn,
            statusColumn: $statusColumn,
        );

        $result = $sut->execute(
            [
                $migrationMock0,
                $migrationMock1,
                $migrationMock2,
                $migrationMock3,
            ]
        );

        $this->assertFalse($result);
    }
}
