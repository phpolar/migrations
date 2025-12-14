<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PDO;
use PhpContrib\Migration\MigrationInterface;
use Throwable;

/**
 * Runs migrations.
 */
readonly class RunCommand
{
    public function __construct(
        private PDO $connection,
        private string $insertMigrationResultStmt,
        private string $insertMigrationResultWithErrorStmt,
        private string $nameParam = "name",
        private string $statusParam = "status",
        private string $versionParam = "version",
        private string $durationParam = "duration_ms",
        private string $errorMessageParam = "error_text",
    ) {
    }

    /**
     * Run the given migrations.
     *
     * @param MigrationInterface[] $pendingMigrations
     */
    public function execute(array $pendingMigrations): bool
    {
        return array_all(
            array_map(
                $this->addMigrationRunToLedger(...),
                array_map(
                    $this->runMigration(...),
                    $pendingMigrations,
                ),
            ),
            $this->runWasSuccessful(...),
        );
    }

    private function runMigration(MigrationInterface $pendingMigration): MigrationRunResult
    {
        $startNs = -hrtime(true);

        try {
            $pendingMigration->up($this->connection);
        } catch (Throwable $t) {
            $errorMessage = mb_strimwidth($t->getMessage(), 0, 4096, "...");

            return new MigrationRunFailure(
                migrationName: $pendingMigration::class,
                durationMs: intdiv((int) $startNs + (int) hrtime(true), 1_000_000),
                errorMessage: $errorMessage,
            );
        }

        return new MigrationRunCompleted(
            migrationName: $pendingMigration::class,
            durationMs: intdiv((int) $startNs + (int) hrtime(true), 1_000_000),
        );
    }

    /**
     * @suppress PhanUnextractableAnnotationSuffix
     * @return array{"ledger": LedgerInsertStatus, "migrationRun": MigrationRunResult}
     */
    private function addMigrationRunToLedger(
        MigrationRunResult $result,
    ): array {

        $params = [
            $this->nameParam => $result->name,
            $this->versionParam => $result->version,
            $this->durationParam => $result->durationMs,
            $this->statusParam => $result->status->name,
        ];

        if ($result instanceof MigrationRunFailure) {
            $params[$this->errorMessageParam] = $result->errorMessage;
        }

        $stmt = $this->connection->prepare(
            $result instanceof MigrationRunCompleted
                ? $this->insertMigrationResultStmt
                : $this->insertMigrationResultWithErrorStmt
        );
        $ledgerInsertStatus = $stmt !== false && $stmt->execute($params) === true
            ? LedgerInsertStatus::SUCCESSFUL
            : LedgerInsertStatus::FAILED;
        return ["ledger" => $ledgerInsertStatus, "migrationRun" => $result];
    }

    /**
     * @suppress PhanUnextractableAnnotationSuffix,PhanUnextractableAnnotationElementName
     * @param array{'ledger': LedgerInsertStatus, 'migrationRun': MigrationRunResult} $results
     */
    private function runWasSuccessful(array $results): bool
    {
        ["ledger" => $ledgerInsertResult, "migrationRun" => $migrationRunResult] = $results;
        return $migrationRunResult instanceof MigrationRunCompleted
            && $ledgerInsertResult === LedgerInsertStatus::SUCCESSFUL;
    }
}
