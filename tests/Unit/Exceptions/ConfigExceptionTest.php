<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\ConfigException;
use PHPUnit\Framework\TestCase;

class ConfigExceptionTest extends TestCase
{
    public function testUserMessage(): void
    {
        $e = new ConfigException('Ошибка конфигурации');
        $this->assertSame('Ошибка конфигурации', $e->getMessage());
        $this->assertSame('Ошибка конфигурации: Ошибка конфигурации', $e->getUserMessage());
    }

    public function testCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('prev');
        $e = new ConfigException('Ошибка конфигурации', 123, $previous);

        $this->assertSame(123, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}
