<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\DatabaseException;
use PHPUnit\Framework\TestCase;

class DatabaseExceptionTest extends TestCase
{
    public function testUserMessage(): void
    {
        $e = new DatabaseException('DB error');
        $this->assertSame('DB error', $e->getMessage());
        $this->assertSame('Ошибка при обращении к базе данных. Попробуйте позже.', $e->getUserMessage());
    }

    public function testCodeAndPrevious(): void
    {
        $previous = new \PDOException('pdo');
        $e = new DatabaseException('DB error', 500, $previous);

        $this->assertSame(500, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}
