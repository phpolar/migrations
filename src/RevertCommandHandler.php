<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use Psr\Log\LoggerInterface;

/**
 * Handles revert migration requests.
 */
final readonly class RevertCommandHandler
{
    public function __construct(
        private RevertCommand $revertCommand,
        private GetLastMigrationQuery $lastMigrationQuery,
        private LoggerInterface $logger,
    ) {
    }

    public function revert(): void
    {
        $migration = $this->lastMigrationQuery->query();
        $status = $this->revertCommand->execute();

        if ($migration === false) {
            $this->logger->error("Last migration query failed.");
            return;
        }

        match ($status) {
            true => $this->logger->info(
                sprintf(
                    "Revert of %s completed successfully.",
                    get_class($migration),
                )
            ),
            false => $this->logger->error(
                sprintf(
                    "Migration revert of %s failed or is pending.",
                    get_class($migration),
                )
            ),
        };
    }
}
