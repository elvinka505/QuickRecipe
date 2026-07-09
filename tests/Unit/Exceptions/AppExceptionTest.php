<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\AppException;
use PHPUnit\Framework\TestCase;

class AppExceptionTest extends TestCase
{
    public function testDefaultUserMessage(): void
    {
        $e = new AppException('Техническая ошибка');
        $this->assertSame('Техническая ошибка', $e->getMessage());
        $this->assertSame('Произошла внутренняя ошибка. Попробуйте позже.', $e->getUserMessage());
    }

    public function testCodeAndPreviousException(): void
    {
        $previous = new \RuntimeException('prev');
        $e = new AppException('Ошибка', 500, $previous);

        $this->assertSame(500, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}
