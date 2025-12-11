<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use DateTimeImmutable;

/**
 * Creates a migration stub.
 *
 * The migration must be implemented after it is created.
 */
readonly class CreateCommand
{
    public function __construct(
        private FileWriterInterface $fileWriter,
        private DateTimeImmutable $dateTime,
    ) {
    }

    /**
     * @param string $migrationName Name of migration to create
     * @param string $migrationsDir Migrations target directory
     */
    public function execute(
        string $migrationName,
        string $migrationsDir,
    ): bool {
        $timestamp = $this->dateTime->format("Uv");
        $classname = "Migration" . $timestamp . $migrationName;
        $filename = sprintf(
            "%s" . DIRECTORY_SEPARATOR . "%s.php",
            rtrim($migrationsDir, DIRECTORY_SEPARATOR),
            $classname
        );
        $contents = <<<FILE_CONTENTS
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

        return $this->fileWriter->write(
            $filename,
            $contents,
        ) !== false;
    }
}
