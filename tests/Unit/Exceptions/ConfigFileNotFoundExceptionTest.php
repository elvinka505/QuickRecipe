<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\ConfigFileNotFoundException;
use App\Exceptions\ConfigException;
use PHPUnit\Framework\TestCase;

class ConfigFileNotFoundExceptionTest extends TestCase
{
    public function testMessageContainsPath(): void
    {
        $e = new ConfigFileNotFoundException('/path/to/.env');
        $this->assertStringContainsString('.env', $e->getMessage());
    }

    public function testExtendsConfigException(): void
    {
        $e = new ConfigFileNotFoundException('/path/to/.env');
        $this->assertInstanceOf(ConfigException::class, $e);
    }

    public function testGetUserMessageIsGeneric(): void
    {
        $e = new ConfigFileNotFoundException('/secret/.env');
        // getUserMessage не должен раскрывать путь
        $this->assertStringNotContainsString('/secret/', $e->getUserMessage());
        $this->assertNotEmpty($e->getUserMessage());
    }

    public function testPreviousException(): void
    {
        $prev = new \RuntimeException('причина');
        $e    = new ConfigFileNotFoundException('/path/.env', 0, $prev);
        $this->assertSame($prev, $e->getPrevious());
    }
}
