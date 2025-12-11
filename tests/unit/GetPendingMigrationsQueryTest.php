<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PDOStatement;
use PhpContrib\Migration\MigrationRunStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetPendingMigrationsQuery::class)]
final class GetPendingMigrationsQueryTest extends TestCase
{
    #[Test]
    #[TestDox("Shall retrieve pending migrations")]
    #[TestWith([
        <<<SQL
        SELECT `name`
        FROM `migration`
        WHERE `status`='COMPLETED';
        SQL,
        "status",
        ["Migration1765073576565CreateSomeRandomTable"],
        ["Migration1765073576565CreateSomeRandomTable"],
        0,
    ])]
    #[TestWith([
        <<<SQL
        SELECT `name`
        FROM `migration`
        WHERE `status`='COMPLETED';
        SQL,
        "status",
        [
            "Migration1765073576565CreateSomeRandomTable",
            "Migration1765073576566CreateSomeRandomTable",
        ],
        ["Migration1765073576565CreateSomeRandomTable"],
        1,
    ])]
    #[TestWith([
        <<<SQL
        SELECT `name`
        FROM `migration`
        WHERE `status`<>'COMPLETED';
        SQL,
        "status",
        [
            "Migration1765073576565CreateSomeRandomTable",
            "Migration1765073576566CreateSomeRandomTable",
            "Migration1765073576567CreateSomeRandomTable",
        ],
        ["Migration1765073576565CreateSomeRandomTable"],
        2,
    ])]
    public function getsPendingMigrations(
        string $completedMigrationsQuery,
        string $statusColumn,
        array $candidates,
        array $completed,
        int $expectedCount,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($completedMigrationsQuery)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method("execute")
            ->willReturn(true);
        $stmtMock->expects($this->once())
            ->method("fetchAll")
            ->with(PDO::FETCH_COLUMN, 0)
            ->willReturn($completed);


        $sut = new GetPendingMigrationsQuery(
            candidates: $candidates,
            connection: $connectionMock,
            completedMigrationsQuery: $completedMigrationsQuery,
            statusColumn: $statusColumn,
        );

        $result = $sut->query();

        $this->assertCount($expectedCount, $result);
    }

    #[Test]
    #[TestDox("Shall notify prepare failed")]
    #[TestWith([
        <<<SQL
        SELECT `name`
        FROM `migration`
        WHERE `status`<>'COMPLETED';
        SQL,
    ])]
    public function notifiesFailedPrepare(
        string $completedMigrationsQuery,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($completedMigrationsQuery)
            ->willReturn(false);

        $stmtMock->expects($this->never())
            ->method("execute");
        $stmtMock->expects($this->never())
            ->method("fetchAll");


        $sut = new GetPendingMigrationsQuery(
            candidates: [],
            connection: $connectionMock,
            completedMigrationsQuery: $completedMigrationsQuery,
            statusColumn: "",
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify prepare failed")]
    #[TestWith([
        <<<SQL
        SELECT `name`
        FROM `migration`
        WHERE `status`<>'COMPLETED';
        SQL,
        "status"
    ])]
    public function notifiesExecuteFailed(
        string $completedMigrationsQuery,
        string $statusColumn,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($completedMigrationsQuery)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method("execute")
            ->with([$statusColumn => MigrationRunStatus::COMPLETED->name])
            ->willReturn(false);
        $stmtMock->expects($this->never())
            ->method("fetchAll");


        $sut = new GetPendingMigrationsQuery(
            candidates: [],
            connection: $connectionMock,
            completedMigrationsQuery: $completedMigrationsQuery,
            statusColumn: $statusColumn,
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }
}
