<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use Closure;
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
#[UsesClass(MigrationRunCompleted::class)]
#[CoversClass(MigrationRunFailure::class)]
#[CoversClass(StreamLogger::class)]
final class RunCommandTest extends TestCase
{
    private function assertExecutedWithParamsCompleted(MigrationInterface $migration): Closure
    {
        return function (array $params) use ($migration): true {
            [
                "version" => $version,
                "name" => $name,
                "status" => $status,
            ] = $params;
            $this->assertSame(0, $version);
            $this->assertSame($migration::class, $name);
            $this->assertSame(MigrationRunStatus::COMPLETED->name, $status);
            return true;
        };
    }

    private function assertExecuteFailsWithParamsCompleted(MigrationInterface $migration): Closure
    {
        return function (array $params) use ($migration): false {
            [
                "version" => $version,
                "name" => $name,
                "status" => $status,
            ] = $params;
            $this->assertSame(0, $version);
            $this->assertSame($migration::class, $name);
            $this->assertSame(MigrationRunStatus::COMPLETED->name, $status);
            return false;
        };
    }

    private function assertExecutedWithParamsFailed(MigrationInterface $migration): Closure
    {
        return function (array $params) use ($migration): false {
            [
                "version" => $version,
                "name" => $name,
                "status" => $status,
                "error_text" => $errorText,
            ] = $params;
            $this->assertSame(0, $version);
            $this->assertSame($migration::class, $name);
            $this->assertSame("boom", $errorText);
            $this->assertSame(MigrationRunStatus::FAILED->name, $status);
            return false;
        };
    }


    #[Test]
    #[TestDox("Shall run migration and record migration completion")]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`, `version`)
        VALUES (:name, :status, :version);
        SQL,
        "name",
        "status",
        "version",
    ])]
    public function runsMigration(
        string $insertStatement,
        string $nameParam,
        string $statusParam,
        string $versionParam,
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
                    $nameParam => get_class($migrationMock),
                    $versionParam => 0,
                    "duration_ms" => 0,
                    $statusParam => MigrationRunStatus::COMPLETED->name
                ]
            )->willReturn(true);

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            insertMigrationResultWithErrorStmt: "",
        );

        $result = $sut->execute([$migrationMock]);

        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox("Shall run all migrations and record migration completion")]
    #[TestWith([
        <<<SQL
        INSERT INTO `migration` (`name`, `status`, `version`)
        VALUES (:name, :status, :version);
        SQL,
    ])]
    public function runsAllMigration(
        string $insertStatement,
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
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock0));
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock1));
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock2));
        $stmtMock3->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock3));

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            insertMigrationResultWithErrorStmt: "",
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
        INSERT INTO `migration` (`name`, `status`, `version`)
        VALUES (:name, :status, :version);
        SQL,
    ])]
    public function runsAllMigrationOneFails(
        string $insertStatement,
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

        $connectionMock->expects($this->atLeast(3))
            ->method("prepare")
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
            ->willThrowException(new \Exception("boom"));
        $stmtMock0->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock0));
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock1));
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock1));
        $stmtMock3->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsFailed($migrationMock3));

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            insertMigrationResultWithErrorStmt: "",
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
        INSERT INTO `migration` (`name`, `status`, `version`)
        VALUES (:name, :status, :version);
        SQL,
    ])]
    public function runsAllMigrationOnePrepareFails(
        string $insertStatement,
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
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock0));
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock1));
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock2));
        $stmtMock3->expects($this->never())
            ->method("execute");

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            insertMigrationResultWithErrorStmt: "",
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
        INSERT INTO `migration` (`name`, `status`, `version`)
        VALUES (:name, :status, :version);
        SQL,
        "name",
        "status",
        "version",
    ])]
    public function runsAllMigrationOneExecuteFails(
        string $insertStatement,
        string $nameParam,
        string $statusParam,
        string $versionParam,
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
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock0));
        $stmtMock1->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock1));
        $stmtMock2->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecutedWithParamsCompleted($migrationMock2));
        $stmtMock3->expects($this->once())
            ->method("execute")
            ->willReturnCallback($this->assertExecuteFailsWithParamsCompleted($migrationMock3));

        $sut = new RunCommand(
            connection: $connectionMock,
            insertMigrationResultStmt: $insertStatement,
            insertMigrationResultWithErrorStmt: "",
            nameParam: $nameParam,
            statusParam: $statusParam,
            versionParam: $versionParam,
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
