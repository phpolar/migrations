<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Adds stream writing support.
 */
final class StreamLogger implements LoggerInterface
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private $stream,
    ) {
    }

    private function writef(
        string $format,
        string|Stringable $message,
    ): void {
        fprintf(
            $this->stream,
            $format . PHP_EOL,
            $message,
        );
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[33m%s\e[0m", $message);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[1;33m%s\e[0m", $message);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[33m%s\e[0m", $message);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[1;101m%s\e[0m", $message);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[31m%s\e[0m", $message);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[1;38m%s\e[0m", $message);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        match ($level) {
            "alert" => $this->alert($message, $context),
            "critical" => $this->critical($message, $context),
            "debug" => $this->debug($message, $context),
            "emergency" => $this->emergency($message, $context),
            "error" => $this->error($message, $context),
            "info" => $this->info($message, $context),
            "notice" => $this->notice($message, $context),
            "warning" => $this->warning($message, $context),
        };
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[1;33m%s\e[0m", $message);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->writef("\e[1;103m%s\e[0m", $message);
    }
}
