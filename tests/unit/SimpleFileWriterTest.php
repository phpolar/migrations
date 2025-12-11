<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimpleFileWriter::class)]
final class SimpleFileWriterTest extends TestCase
{
    const FILE_NAME = __DIR__ . "/../__files__/my-test-file.txt";

    protected function setUp(): void
    {
        parent::setUp();
        file_exists(self::FILE_NAME) && unlink(self::FILE_NAME);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        file_exists(self::FILE_NAME) && unlink(self::FILE_NAME);
    }

    #[Test]
    #[TestDox("Shall write content to the specified file")]
    #[TestWith(["SOME_FILE_CONTENT", self::FILE_NAME, 17])]
    public function writes(
        string $fileContents,
        string $fileToWrite,
        int $expectedSize,
    ) {
        $sut = new SimpleFileWriter();

        $result = $sut->write($fileToWrite, $fileContents);

        $this->assertFileExists($fileToWrite);
        $this->assertFileMatchesFormat($fileContents, $fileToWrite);
        $this->assertSame($expectedSize, $result);
    }
}
