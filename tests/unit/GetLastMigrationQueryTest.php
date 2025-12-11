<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PDOStatement;
use PhpContrib\Migration\MigrationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(GetLastMigrationQuery::class)]
final class GetLastMigrationQueryTest extends TestCase
{
    #[Test]
    #[TestDox("Shall retrieve the last successfully run migration")]
    #[TestWith([
        <<<SQL
        SELECT `name` FROM `migration`
        WHERE `id`=:id;
        SQL,
        "Migration1765073576565CreateSomeRandomTable",
    ])]
    public function retrievesMigration(
        string $query,
        string $migrationName,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($query)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())->method("execute");
        $stmtMock->expects($this->once())
            ->method("bindColumn")
            ->willReturnCallback(
                function (
                    string $name,
                    mixed &$var,
                    int $type,
                ) use ($migrationName) {
                    $var = $migrationName;
                    return $name === "name"
                        && $type === PDO::PARAM_STR;
                }
            );
        $stmtMock->expects($this->once())
            ->method("fetch")
            ->with(PDO::FETCH_BOUND);

        $sut = new GetLastMigrationQuery(
            connection: $connectionMock,
            lastMigrationQuery: $query,
        );

        $result = $sut->query();

        $this->assertInstanceOf(MigrationInterface::class, $result);
    }

    #[Test]
    #[TestDox("Shall notify if preparing the statement failed")]
    #[TestWith([
        <<<SQL
        SELECT `name` FROM `migration`
        WHERE `id`=:id;
        SQL,
    ])]
    public function notifiesPrepareFailed(
        string $query,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($query)
            ->willReturn(false);

        $stmtMock->expects($this->never())->method("execute");
        $stmtMock->expects($this->never())
            ->method("bindColumn");
        $stmtMock->expects($this->never())
            ->method("fetch")
            ->with(PDO::FETCH_BOUND);

        $sut = new GetLastMigrationQuery(
            connection: $connectionMock,
            lastMigrationQuery: $query,
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify if name not set to string")]
    #[TestWith([
        <<<SQL
        SELECT `name` FROM `migration`
        WHERE `id`=:id;
        SQL,
    ])]
    public function notifiesNameNotString(
        string $query,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($query)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())->method("execute");
        $stmtMock->expects($this->once())
            ->method("bindColumn")
            ->willReturnCallback(
                function (
                    string $name,
                    mixed &$var,
                    int $type,
                ) {
                    $var = true;
                    return $name === "name"
                        && $type === PDO::PARAM_STR;
                }
            );
        $stmtMock->expects($this->once())
            ->method("fetch")
            ->with(PDO::FETCH_BOUND);

        $sut = new GetLastMigrationQuery(
            connection: $connectionMock,
            lastMigrationQuery: $query,
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify if name is empty string")]
    #[TestWith([
        <<<SQL
        SELECT `name` FROM `migration`
        WHERE `id`=:id;
        SQL,
    ])]
    public function notifiesNameEmpty(
        string $query,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($query)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())->method("execute");
        $stmtMock->expects($this->once())
            ->method("bindColumn")
            ->willReturnCallback(
                function (
                    string $name,
                    mixed &$var,
                    int $type,
                ) {
                    $var = "";
                    return $name === "name"
                        && $type === PDO::PARAM_STR;
                }
            );
        $stmtMock->expects($this->once())
            ->method("fetch")
            ->with(PDO::FETCH_BOUND);

        $sut = new GetLastMigrationQuery(
            connection: $connectionMock,
            lastMigrationQuery: $query,
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify migration name is not an existing class")]
    #[TestWith([
        <<<SQL
        SELECT `name` FROM `migration`
        WHERE `id`=:id;
        SQL,
        "MigrationNonExistingClass",
    ])]
    public function notifiesMigrationNonExistingClass(
        string $query,
        string $migrationName,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($query)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())->method("execute");
        $stmtMock->expects($this->once())
            ->method("bindColumn")
            ->willReturnCallback(
                function (
                    string $name,
                    mixed &$var,
                    int $type,
                ) use ($migrationName) {
                    $var = $migrationName;
                    return $name === "name"
                        && $type === PDO::PARAM_STR;
                }
            );
        $stmtMock->expects($this->once())
            ->method("fetch")
            ->with(PDO::FETCH_BOUND);

        $sut = new GetLastMigrationQuery(
            connection: $connectionMock,
            lastMigrationQuery: $query,
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall notify migration does not implement MigrationInterface")]
    #[TestWith([
        <<<SQL
        SELECT `name` FROM `migration`
        WHERE `id`=:id;
        SQL,
        stdClass::class,
    ])]
    public function notifiesMigrationNotImplementingMigration(
        string $query,
        string $migrationName,
    ) {
        $connectionMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $connectionMock->expects($this->once())
            ->method("prepare")
            ->with($query)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())->method("execute");
        $stmtMock->expects($this->once())
            ->method("bindColumn")
            ->willReturnCallback(
                function (
                    string $name,
                    mixed &$var,
                    int $type,
                ) use ($migrationName) {
                    $var = $migrationName;
                    return $name === "name"
                        && $type === PDO::PARAM_STR;
                }
            );
        $stmtMock->expects($this->once())
            ->method("fetch")
            ->with(PDO::FETCH_BOUND);

        $sut = new GetLastMigrationQuery(
            connection: $connectionMock,
            lastMigrationQuery: $query,
        );

        $result = $sut->query();

        $this->assertFalse($result);
    }
}
