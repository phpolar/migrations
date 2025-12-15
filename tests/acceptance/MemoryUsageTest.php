<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use DateTimeImmutable;
use Migration1765073576565CreateSomeRandomTable;
use Migration1765073576566CreateSomeRandomTable;
use Migration1765073576567CreateSomeRandomTable;
use PDO;
use Pdo\Mysql;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

const CREATE_COMMAND_MEMORY_USAGE_THRESHOLD = 99_999;
const REVERT_COMMAND_MEMORY_USAGE_THRESHOLD = 109_999;
const RUN_COMMAND_MEMORY_USAGE_THRESHOLD = 39_999;
const MIGRATIONS_DIR = __DIR__ . "/../__files__";
const MIGRATION_NAME = "CreateTableForAcceptanceTests";

#[CoversNothing]
final class MemoryUsageTest extends TestCase
{
    private $stream;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stream = fopen("php://memory", "+w");
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        fclose($this->stream);

        array_walk(
            glob(MIGRATIONS_DIR . DIRECTORY_SEPARATOR . "Migration*" . MIGRATION_NAME . ".php"),
            static fn(string $migrationFile) =>
            file_exists($migrationFile) && unlink($migrationFile),
        );
    }

    #[Test]
    #[TestDox(
        "Memory usage for the create command request shall be below "
            . CREATE_COMMAND_MEMORY_USAGE_THRESHOLD
            . " bytes"
    )]
    public function shallBeBelowThreshold1()
    {
        $this->expectsOutput();

        $totalUsed = -memory_get_usage();

        new CreateCommandHandler(
            createCommand: new CreateCommand(
                fileWriter: new SimpleFileWriter(),
                dateTime: new DateTimeImmutable("now"),
            ),
            logger: new StreamLogger($this->stream),
        )->create(
            MIGRATIONS_DIR,
            MIGRATION_NAME,
        );

        $totalUsed += memory_get_usage();
        $this->assertGreaterThan(0, $totalUsed);
        $this->assertLessThanOrEqual(CREATE_COMMAND_MEMORY_USAGE_THRESHOLD, $totalUsed);
    }

    #[Test]
    #[TestDox(
        "Memory usage for the revert command request shall be below "
            . REVERT_COMMAND_MEMORY_USAGE_THRESHOLD
            . " bytes"
    )]
    public function shallBeBelowThreshold2()
    {
        $this->expectsOutput();

        $migrationName = "CreateSomeRandomTable";
        $migrationVersion = "1765073576565";
        $connectionStub = $this->createStub(Mysql::class);
        $stmtStub0 = $this->createStub(PDOStatement::class);
        $stmtStub1 = $this->createStub(PDOStatement::class);

        $stmtStub1->method("execute")->willReturn(true);
        $stmtStub0
            ->method("bindColumn")
            ->willReturnCallback(
                function (
                    string $val,
                    mixed &$var,
                    int $type,
                ) use (
                    $migrationName,
                    $migrationVersion,
                ) {
                    $var = $val === "name" ? $migrationName : $migrationVersion;
                    return in_array($val, ["name", "version"])
                        && $type === PDO::PARAM_STR;
                }
            );

        $connectionStub->method("prepare")
            ->willReturnOnConsecutiveCalls(
                $stmtStub0,
                $stmtStub1
            );


        $totalUsed = -memory_get_usage();

        new RevertCommandHandler(
            revertCommand: new RevertCommand(
                migration: new Migration1765073576565CreateSomeRandomTable(),
                connection: $connectionStub,
                migrationRecordDeleteStatement: <<<SQL
                DELETE FROM `migration`
                WHERE :name=`name`;
                SQL,
            ),
            logger: new StreamLogger($this->stream),
        )->revert();

        $totalUsed += memory_get_usage();
        $this->assertGreaterThan(0, $totalUsed);
        $this->assertLessThanOrEqual(REVERT_COMMAND_MEMORY_USAGE_THRESHOLD, $totalUsed);
    }

    #[Test]
    #[TestDox(
        "Memory usage for the run command request shall be below "
            . RUN_COMMAND_MEMORY_USAGE_THRESHOLD
            . " bytes"
    )]
    public function shallBeBelowThreshold3()
    {
        $connectionStub = $this->createStub(Mysql::class);
        $stmtStub0 = $this->createStub(PDOStatement::class);

        $connectionStub->method("prepare")->willReturn($stmtStub0);
        $stmtStub0->method("execute")->willReturn(true);

        $pendingMigrations = [
            new Migration1765073576565CreateSomeRandomTable(),
            new Migration1765073576566CreateSomeRandomTable(),
            new Migration1765073576567CreateSomeRandomTable(),
        ];

        $totalUsed = -memory_get_usage();

        $runCommand = new RunCommand(
            connection: $connectionStub,
            insertMigrationResultStmt: <<<SQL
            INSERT INTO `migration` (`name`, `status`, `version`, `duration_ms`)
            VALUES (:name, :status, :version, :duration_ms);
            SQL,
            insertMigrationResultWithErrorStmt: <<<SQL
            INSERT INTO `migration` (`name`, `status`, `version`, `duration_ms`, `error_text`)
            VALUES (:name, :status, :version, :duration_ms, :error_text);
            SQL,
        );

        $runCommand->execute($pendingMigrations);

        $totalUsed += memory_get_usage();
        $this->assertGreaterThan(0, $totalUsed);
        $this->assertLessThanOrEqual(RUN_COMMAND_MEMORY_USAGE_THRESHOLD, $totalUsed);
    }
}
