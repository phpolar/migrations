<?php

namespace Phpolar\Migrations;

use PhpContrib\Migration\MigrationInterface;
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
        "expectedLogOutputPattern" => "/^Revert of .+ completed successfully\.$/"
    ])]
    public function revertsMigrations(
        string $expectedLogOutputPattern,
    ) {
        $revertCommandMock = $this->createMock(RevertCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $migrationStub = $this->createStub(MigrationInterface::class);
        $lastMigrationQueryMock = $this->createMock(GetLastMigrationQuery::class);

        $lastMigrationQueryMock->expects($this->once())
            ->method("query")
            ->willReturn($migrationStub);

        $loggerMock->expects($this->once())
            ->method("info")
            ->with($this->callback(
                static function (string $logInfo) use ($expectedLogOutputPattern) {
                    return preg_match($expectedLogOutputPattern, $logInfo) === 1;
                }
            ));

        $revertCommandMock->expects($this->once())
            ->method("execute")
            ->willReturn(true);

        $sut = new RevertCommandHandler(
            revertCommand: $revertCommandMock,
            lastMigrationQuery: $lastMigrationQueryMock,
            logger: $loggerMock,
        );

        $sut->revert();
    }

    #[Test]
    #[TestDox("Shall notify when reverting the last migration fails")]
    #[TestWith([
        "expectedLogOutputPattern" => "/^Migration revert of .+ failed or is pending\.$/"
    ])]
    public function notifiesRevertFailed(
        string $expectedLogOutputPattern,
    ) {
        $queryMock = $this->createMock(GetLastMigrationQuery::class);
        $revertCommandMock = $this->createMock(RevertCommand::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $migrationStub = $this->createStub(MigrationInterface::class);

        $queryMock->expects($this->once())
            ->method("query")
            ->willReturn($migrationStub);

        $loggerMock->expects($this->once())
            ->method("error")
            ->with($this->callback(
                static function (string $logInfo) use ($expectedLogOutputPattern) {
                    return preg_match($expectedLogOutputPattern, $logInfo) === 1;
                }
            ));

        $revertCommandMock->expects($this->once())
            ->method("execute")
            ->willReturn(false);

        $sut = new RevertCommandHandler(
            revertCommand: $revertCommandMock,
            lastMigrationQuery: $queryMock,
            logger: $loggerMock,
        );

        $sut->revert();
    }
}
