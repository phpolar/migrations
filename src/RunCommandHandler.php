<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use Psr\Log\LoggerInterface;

/**
 * Handles database migration management.
 */
final readonly class RunCommandHandler
{
    public function __construct(
        private RunCommand $runCommand,
        private GetPendingMigrationsQuery $pendingMigrationQuery,
        private LoggerInterface $logger,
    ) {
    }

    public function run(): void
    {
        $pendingMigrations = $this->pendingMigrationQuery->query();

        if ($pendingMigrations === false) {
            $this->logger->error("The pending migration query failed.");
            return;
        }

        match ($this->runCommand->execute($pendingMigrations)) {
            true => $this->logger->info("All migrations executed successfully."),
            false => $this->logger->error("One or more of the migrations failed."),
        };
    }
}
