<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamLogger::class)]
final class StreamLoggerTest extends TestCase
{
    private $stream;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stream = fopen("php://memory", "+w");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        fclose($this->stream);
    }

    #[Test]
    #[TestDox("Shall write alert to stream")]
    #[TestWith(["\e[33mALERT\e[0m" . PHP_EOL, "ALERT"])]
    public function writesAlert(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->alert($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write a critical message to stream")]
    #[TestWith(["\e[1;33mCRITICAL\e[0m" . PHP_EOL, "CRITICAL"])]
    public function writesCritical(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->critical($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write a debug message to stream")]
    #[TestWith(["\e[33mDEBUG\e[0m" . PHP_EOL, "DEBUG"])]
    public function writesDebug(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->debug($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write an emergency message to stream")]
    #[TestWith(["\e[1;101mEMERGENCY\e[0m" . PHP_EOL, "EMERGENCY"])]
    public function writesEmergency(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->emergency($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write an error message to stream")]
    #[TestWith(["\e[31mERROR\e[0m" . PHP_EOL, "ERROR"])]
    public function writesError(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->error($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write an info message to stream")]
    #[TestWith(["\e[1;38mINFO\e[0m" . PHP_EOL, "INFO"])]
    public function writesInfo(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->info($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write a log message to stream")]
    #[TestWith(["\e[33mALERT\e[0m" . PHP_EOL, "ALERT", "alert"])]
    #[TestWith(["\e[1;33mCRITICAL\e[0m" . PHP_EOL, "CRITICAL", "critical"])]
    #[TestWith(["\e[33mDEBUG\e[0m" . PHP_EOL, "DEBUG", "debug"])]
    #[TestWith(["\e[1;101mEMERGENCY\e[0m" . PHP_EOL, "EMERGENCY", "emergency"])]
    #[TestWith(["\e[31mERROR\e[0m" . PHP_EOL, "ERROR", "error"])]
    #[TestWith(["\e[1;38mINFO\e[0m" . PHP_EOL, "INFO", "info"])]
    #[TestWith(["\e[1;33mNOTICE\e[0m" . PHP_EOL, "NOTICE", "notice"])]
    #[TestWith(["\e[1;103mWARNING\e[0m" . PHP_EOL, "WARNING", "warning"])]
    public function writesLog(
        string $expectedOutput,
        string $message,
        string $level,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->log($level, $message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write a notice message to stream")]
    #[TestWith(["\e[1;33mNOTICE\e[0m" . PHP_EOL, "NOTICE"])]
    public function writesNotice(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->notice($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }

    #[Test]
    #[TestDox("Shall write a warning message to stream")]
    #[TestWith(["\e[1;103mWARNING\e[0m" . PHP_EOL, "WARNING"])]
    public function writesWarning(
        string $expectedOutput,
        string $message,
    ) {
        $sut = new StreamLogger($this->stream);

        $sut->warning($message);

        rewind($this->stream);
        $this->assertSame($expectedOutput, stream_get_contents($this->stream));
    }
}
