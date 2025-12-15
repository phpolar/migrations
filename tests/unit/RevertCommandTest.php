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
use PHPUnit\Framework\TestCase;

#[CoversClass(RevertCommand::class)]
final class RevertCommandTest extends TestCase
{
    #[Test]
    #[TestDox("Shall revert the last successfully run migration")]
    #[TestWith(["DELETE FROM `migration` WHERE `name`=:name", "name"])]
    public function revertMigration(string $deleteStatement, string $nameParam)
    {
        $migrationMock = $this->createMock(MigrationInterface::class);
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $migrationMock->expects($this->once())
            ->method("down")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($deleteStatement)
            ->willReturn($stmtMock);


        $stmtMock->expects($this->once())
            ->method("execute")
            ->willReturn(true);


        $sut = new RevertCommand(
            migration: $migrationMock,
            connection: $connectionMock,
            migrationRecordDeleteStatement: $deleteStatement,
        );

        $result = $sut->execute();

        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox("Shall notify when delete execution fails")]
    #[TestWith(["DELETE FROM `migration` WHERE `name`=:name", "name"])]
    public function notifiesWhenExecuteFails(string $deleteStatement, string $nameParam)
    {
        $migrationMock = $this->createMock(MigrationInterface::class);
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $migrationMock->expects($this->once())
            ->method("down")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($deleteStatement)
            ->willReturn($stmtMock);


        $stmtMock->expects($this->once())
            ->method("execute")
            ->willReturn(false);


        $sut = new RevertCommand(
            migration: $migrationMock,
            connection: $connectionMock,
            migrationRecordDeleteStatement: $deleteStatement,
        );

        $result = $sut->execute();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify when prepare statement fails")]
    #[TestWith(["DELETE FROM `migration` WHERE `name`=:name", "name"])]
    public function notifiesWhenPrepareFails(string $deleteStatement, string $nameParam)
    {
        $migrationMock = $this->createMock(MigrationInterface::class);
        $connectionMock = $this->createMock(PDO::class);

        $migrationMock->expects($this->once())
            ->method("down")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::COMPLETED);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($deleteStatement)
            ->willReturn(false);

        $sut = new RevertCommand(
            migration: $migrationMock,
            connection: $connectionMock,
            migrationRecordDeleteStatement: $deleteStatement,
        );

        $result = $sut->execute();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify when migration revert fails")]
    #[TestWith(["DELETE FROM `migration` WHERE `name`=:name", "name"])]
    public function notifiesWhenRevertFails(string $deleteStatement, string $nameParam)
    {
        $migrationMock = $this->createMock(MigrationInterface::class);
        $connectionMock = $this->createMock(PDO::class);

        $migrationMock->expects($this->once())
            ->method("down")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::FAILED);

        $connectionMock->expects($this->never())
            ->method("prepare");

        $sut = new RevertCommand(
            migration: $migrationMock,
            connection: $connectionMock,
            migrationRecordDeleteStatement: $deleteStatement,
        );

        $result = $sut->execute();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify when migration revert is still pending")]
    #[TestWith(["DELETE FROM `migration` WHERE `name`=:name", "name"])]
    public function notifiesWhenRevertPending(string $deleteStatement, string $nameParam)
    {
        $migrationMock = $this->createMock(MigrationInterface::class);
        $connectionMock = $this->createMock(PDO::class);

        $migrationMock->expects($this->once())
            ->method("down")
            ->with($connectionMock)
            ->willReturn(MigrationRunStatus::FAILED);

        $connectionMock->expects($this->never())
            ->method("prepare");

        $sut = new RevertCommand(
            migration: $migrationMock,
            connection: $connectionMock,
            migrationRecordDeleteStatement: $deleteStatement,
        );

        $result = $sut->execute();

        $this->assertFalse($result);
    }
}
