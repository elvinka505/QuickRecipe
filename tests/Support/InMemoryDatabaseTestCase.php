<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Database;
use PHPUnit\Framework\TestCase;

abstract class InMemoryDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['DB_PATH'] = ':memory:';
        Database::reset();
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);

        parent::tearDown();
    }
}
