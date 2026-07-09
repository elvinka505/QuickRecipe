<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\ConfigValidationException;
use PHPUnit\Framework\TestCase;

class ConfigValidationExceptionTest extends TestCase
{
    public function testMessageContainsMissingKeys(): void
    {
        $e = new ConfigValidationException(['DB_PATH', 'APP_KEY']);
        $this->assertStringContainsString('DB_PATH', $e->getMessage());
        $this->assertStringContainsString('APP_KEY', $e->getMessage());
    }

    public function testGetMissingKeys(): void
    {
        $keys = ['DB_PATH', 'APP_KEY'];
        $e    = new ConfigValidationException($keys);
        $this->assertSame($keys, $e->getMissingKeys());
    }

    public function testGetUserMessage(): void
    {
        $e = new ConfigValidationException(['APP_KEY']);
        $this->assertStringContainsString('APP_KEY', $e->getUserMessage());
    }

    public function testPreviousException(): void
    {
        $prev = new \InvalidArgumentException('причина');
        $e    = new ConfigValidationException(['X'], 0, $prev);
        $this->assertSame($prev, $e->getPrevious());
    }
}
