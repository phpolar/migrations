<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateCommand::class)]
final class CreateCommandTest extends TestCase
{
    const MIGRATION_DEST = __DIR__ . "/../__files__";

    #[Test]
    #[TestDox("Shall write the content to the file")]
    #[TestWith([
        "migrationsDir" => self::MIGRATION_DEST,
        "migrationName" => "CreateSomeRandomTable",
        "timestamp" => "1765073576565",
        "expectedFilename" =>
        self::MIGRATION_DEST . DIRECTORY_SEPARATOR . "Migration1765073576565CreateSomeRandomTable.php",
        "classname" => "Migration1765073576565CreateSomeRandomTable",
    ])]
    #[TestWith([
        "migrationsDir" => self::MIGRATION_DEST . DIRECTORY_SEPARATOR,
        "migrationName" => "CreateSomeRandomTable",
        "timestamp" => "1765073576565",
        "expectedFilename" =>
        self::MIGRATION_DEST . DIRECTORY_SEPARATOR . "Migration1765073576565CreateSomeRandomTable.php",
        "classname" => "Migration1765073576565CreateSomeRandomTable",
    ])]
    public function createsMigrations(
        string $migrationsDir,
        string $migrationName,
        string $timestamp,
        string $expectedFilename,
        string $classname,
    ): void {
        $dateTimeStub = $this->createStub(DateTimeImmutable::class);
        $fileWriterMock = $this->createMock(FileWriterInterface::class);

        $dateTimeStub->method("format")->willReturn($timestamp);
        $fileWriterMock->expects($this->once())
            ->method("write")
            ->with(
                $expectedFilename,
                $this->getExpectedMigrationFileContents($classname),
            );

        $sut = new CreateCommand(
            fileWriter: $fileWriterMock,
            dateTime: $dateTimeStub
        );

        $sut->execute(
            migrationName: $migrationName,
            migrationsDir: $migrationsDir,
        );
    }

    private function getExpectedMigrationFileContents(
        string $classname,
    ): string {
        return <<<FILE_CONTENTS
<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

final readonly class $classname implements MigrationInterface
{
    public function up(PDO \$connection): MigrationRunStatus
    {
        throw new Exception("Not implemented");
    }

    public function down(PDO \$connection): MigrationRunStatus
    {
        throw new Exception("Not implemented");
    }
}

FILE_CONTENTS;
    }
}
