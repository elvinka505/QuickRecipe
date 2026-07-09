<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Attributes\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    #[DataProvider('methodProvider')]
    public function testRouteNormalizesMethods(string|array $input, array $expected): void
    {
        $route = new Route('/test', $input);

        $this->assertSame('/test', $route->path);
        $this->assertSame($expected, $route->methods);
    }

    public static function methodProvider(): array
    {
        return [
            'single lower-case method' => ['post', ['POST']],
            'single upper-case method' => ['GET', ['GET']],
            'multiple methods' => [['get', 'post'], ['GET', 'POST']],
        ];
    }
}
