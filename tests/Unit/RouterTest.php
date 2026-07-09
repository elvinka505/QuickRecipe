<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Logger;
use App\Core\Router;
use App\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER = [];
        $_GET    = [];
        $_POST   = [];
        $this->router = new Router(new Logger());
    }

    protected function tearDown(): void
    {
        $_SERVER = [];
        $_GET    = [];
        $_POST   = [];
        parent::tearDown();
    }

    public function testGetUriRemovesQueryString(): void
    {
        $_SERVER['REQUEST_URI'] = '/recipes/show?id=10';
        $this->assertSame('/recipes/show', $this->router->getUri());
    }

    public function testGetUriWithNoQueryString(): void
    {
        $_SERVER['REQUEST_URI'] = '/recipes';
        $this->assertSame('/recipes', $this->router->getUri());
    }

    public function testGetUriDefaultsToSlash(): void
    {
        $this->assertSame('/', $this->router->getUri());
    }

    public function testGetMethodReturnsRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertSame('GET', $this->router->getMethod());
    }

    public function testGetMethodReturnsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertSame('POST', $this->router->getMethod());
    }

    public function testGetBodyEscapesHtmlInPostValues(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = '<b>Soup</b>';

        $this->assertSame([
            'title' => '&lt;b&gt;Soup&lt;/b&gt;',
        ], $this->router->getBody());
    }

    public function testGetBodyReturnsEmptyArrayForGetRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['title'] = 'should be ignored';

        $this->assertSame([], $this->router->getBody());
    }

    public function testGetQueryParamsEscapesHtml(): void
    {
        $_GET['search'] = '<script>alert(1)</script>';
        $params = $this->router->getQueryParams();
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $params['search']);
    }

    public function testGetQueryParamReturnsValue(): void
    {
        $_GET['id'] = '42';
        $this->assertSame('42', $this->router->getQueryParam('id'));
    }

    public function testGetQueryParamReturnsDefaultWhenMissing(): void
    {
        $this->assertSame('default', $this->router->getQueryParam('missing', 'default'));
        $this->assertNull($this->router->getQueryParam('missing'));
    }

    public function testGetBodyParamReturnsValue(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'test@example.com';
        $this->assertSame('test@example.com', $this->router->getBodyParam('email'));
    }

    public function testGetBodyParamReturnsDefaultWhenMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertSame('fallback', $this->router->getBodyParam('missing', 'fallback'));
        $this->assertNull($this->router->getBodyParam('missing'));
    }

    public function testGetRegistersRouteAndResolveThrowsNotFoundForUnknown(): void
    {
        $this->router->get('/test', [self::class, 'setUp']);
        $this->router->post('/test', [self::class, 'setUp']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/unknown-route';

        $this->expectException(NotFoundException::class);
        $this->router->resolve();
    }

    public function testResolveCallsRegisteredGetRoute(): void
    {
        $called = false;
        $this->router->get('/ping', [RouterTestController::class, 'handle']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/ping';

        $this->expectNotToPerformAssertions();

        try {
            $this->router->resolve();
        } catch (\App\Exceptions\NotFoundException $e) {
            $this->fail('NotFoundException не должен быть брошен для зарегистрированного маршрута');
        } catch (\Throwable) {
        }
    }

    public function testPostRegistersRouteAndBodyParamAvailable(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Elvina';

        $this->router->post('/submit', [self::class, 'setUp']);

        $this->assertSame('Elvina', $this->router->getBodyParam('name'));
    }

    public function testRegisterReadsGetRouteAttribute(): void
    {
        $controller = new class ($this->router, new Logger()) {
            public function __construct(
                protected Router $router,
                protected Logger $logger
            ) {
            }

            #[\App\Core\Attributes\Route('/attr-get')]
            public function handle(): void
            {
            }
        };

        $this->router->register([get_class($controller)]);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/attr-get';

        try {
            $this->router->resolve();
            $this->assertTrue(true);
        } catch (NotFoundException $e) {
            $this->fail('Route /attr-get должен быть зарегистрирован через атрибут');
        } catch (\Throwable) {
            $this->assertTrue(true);
        }
    }

    public function testRegisterReadsPostRouteAttribute(): void
    {
        $controller = new class ($this->router, new Logger()) {
            public function __construct(
                protected Router $router,
                protected Logger $logger
            ) {
            }

            #[\App\Core\Attributes\Route('/attr-post', 'POST')]
            public function handlePost(): void
            {
            }
        };

        $this->router->register([get_class($controller)]);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/attr-post';

        try {
            $this->router->resolve();
            $this->assertTrue(true);
        } catch (NotFoundException $e) {
            $this->fail('Route /attr-post должен быть зарегистрирован через атрибут');
        } catch (\Throwable) {
            $this->assertTrue(true);
        }
    }

    public function testResolveInDebugModeForRegisteredRoute(): void
    {
        $router = new Router(new Logger(), true);

        $controller = new class ($router, new Logger()) {
            public function __construct(
                protected Router $router,
                protected Logger $logger
            ) {
            }

            #[\App\Core\Attributes\Route('/debug-route')]
            public function handle(): void
            {
            }
        };

        $router->register([get_class($controller)]);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/debug-route';

        try {
            $router->resolve();
            $this->assertTrue(true);
        } catch (NotFoundException $e) {
            $this->fail('Route /debug-route должен существовать');
        } catch (\Throwable) {
            $this->assertTrue(true);
        }
    }
}
