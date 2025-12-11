<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

/**
 * Handles simple file writing.
 */
final class SimpleFileWriter implements FileWriterInterface
{
    public function write(string $filename, string $contents): int|false
    {
        return file_put_contents(
            filename: $filename,
            data: $contents,
        );
    }
}
