<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

class NotFoundExceptionTest extends TestCase
{
    public function testDefaultMessageAndCode(): void
    {
        $e = new NotFoundException();
        $this->assertSame(404, $e->getCode());
        $this->assertSame('Страница не найден(а)', $e->getMessage());
    }

    public function testCustomResourceName(): void
    {
        $e = new NotFoundException('Рецепт', 404);
        $this->assertSame('Рецепт не найден(а)', $e->getMessage());
        $this->assertSame('Рецепт не найден(а)', $e->getUserMessage());
    }

    public function testCustomCode(): void
    {
        $e = new NotFoundException('Страница', 410);
        $this->assertSame(410, $e->getCode());
    }

    public function testPreviousException(): void
    {
        $prev = new \RuntimeException('причина');
        $e    = new NotFoundException('Файл', 404, $prev);
        $this->assertSame($prev, $e->getPrevious());
    }
}
