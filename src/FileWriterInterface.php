<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

/**
 * Adds support for writing to files.
 */
interface FileWriterInterface
{
    /**
     * Write the given content to the targe file.
     */
    public function write(string $filename, string $content): int|false;
}
