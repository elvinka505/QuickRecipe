<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\AuthException;
use PHPUnit\Framework\TestCase;

class AuthExceptionTest extends TestCase
{
    public function testAuthExceptionMessage(): void
    {
        $e = new AuthException('Ошибка авторизации');
        $this->assertSame('Ошибка авторизации', $e->getMessage());
    }

    public function testAuthExceptionHasCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('prev');
        $e = new AuthException('Ошибка авторизации', 401, $previous);

        $this->assertSame(401, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}
