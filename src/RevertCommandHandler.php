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
        private LoggerInterface $logger,
    ) {
    }

    public function revert(): void
    {
        $status = $this->revertCommand->execute();
        $lastMigrationName = $this->revertCommand->getLastMigrationName();

        match ($status) {
            true => $this->logger->info(
                sprintf(
                    "Revert of %s completed successfully.",
                    $lastMigrationName,
                )
            ),
            false => $this->logger->error(
                sprintf(
                    "Migration revert of %s failed or is pending.",
                    $lastMigrationName,
                )
            ),
        };
    }
}
