<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Logger;
use App\Core\Router;
use App\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

class RouterCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        $_SERVER = [];
        $_GET = [];
        $_POST = [];

        parent::tearDown();
    }

    public function testGetMethodReturnsRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $router = new Router(new Logger());

        $this->assertSame('POST', $router->getMethod());
    }

    public function testGetQueryParamsEscapesHtml(): void
    {
        $_GET = [
            'q' => '<script>alert(1)</script>',
            'page' => '2',
        ];

        $router = new Router(new Logger());

        $this->assertSame([
            'q' => '&lt;script&gt;alert(1)&lt;/script&gt;',
            'page' => '2',
        ], $router->getQueryParams());
    }

    public function testGetQueryParamReturnsEscapedValue(): void
    {
        $_GET['name'] = '<b>cake</b>';

        $router = new Router(new Logger());

        $this->assertSame('&lt;b&gt;cake&lt;/b&gt;', $router->getQueryParam('name'));
    }

    public function testGetQueryParamReturnsDefaultWhenMissing(): void
    {
        $router = new Router(new Logger());

        $this->assertSame('default', $router->getQueryParam('missing', 'default'));
    }

    public function testResolveThrowsNotFoundExceptionForUnknownRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/missing-page';

        $router = new Router(new Logger());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(404);

        $router->resolve();
    }

    public function testGetAndPostCanBeRegisteredWithoutErrors(): void
    {
        $router = new Router(new Logger());

        $router->get('/test-get', [self::class, 'fakeHandler']);
        $router->post('/test-post', [self::class, 'fakeHandler']);

        $this->assertTrue(true);
    }

    public static function fakeHandler(): void
    {
    }
}
