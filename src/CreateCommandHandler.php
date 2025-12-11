<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use Psr\Log\LoggerInterface;

/**
 * Handles create migration requests.
 */
final readonly class CreateCommandHandler
{
    public function __construct(
        private CreateCommand $createCommand,
        private LoggerInterface $logger,
    ) {
    }

    public function create(
        string $path,
        string $name
    ): void {
        $successful = $this->createCommand->execute(
            migrationName: $name,
            migrationsDir: $path,
        );

        match ($successful) {
            true => $this->logger->info(
                sprintf(
                    "Migration created at %s/%s.php",
                    rtrim($path, DIRECTORY_SEPARATOR),
                    $name,
                )
            ),
            false => $this->logger->error(
                sprintf("Failed to create migration %s", $name)
            )
        };
    }
}
