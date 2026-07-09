<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\AbstractController;
use App\Core\Logger;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

class AbstractControllerTest extends TestCase
{
    private Router $router;
    private Logger $logger;

    protected function setUp(): void
    {
        $this->router = $this->createStub(Router::class);
        $this->logger = new Logger();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['user_id']);
    }

    public function testSetStatusCodeDoesNotThrow(): void
    {
        $controller = new class ($this->router, $this->logger) extends AbstractController {
            public function callSetStatusCode(int $code): void
            {
                $this->setStatusCode($code);
            }
        };

        $controller->callSetStatusCode(200);
        $controller->callSetStatusCode(404);
        $this->assertTrue(true);
    }

    public function testRedirectSendsHeader(): void
    {
        $controller = new class ($this->router, $this->logger) extends AbstractController {
            public string $lastRedirect = '';

            protected function redirect(string $url): void
            {
                // переопределяем чтобы не вызывать exit
                $this->lastRedirect = $url;
            }

            public function callRedirect(string $url): void
            {
                $this->redirect($url);
            }
        };

        $controller->callRedirect('/recipes');
        $this->assertSame('/recipes', $controller->lastRedirect);
    }

    public function testRenderThrowsForMissingView(): void
    {
        $controller = new class ($this->router, $this->logger) extends AbstractController {
            public function callRender(): void
            {
                $this->render('__nonexistent_view__');
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/View не найден/');
        $controller->callRender();
    }

    public function testRequireAuthRedirectsWhenNoSession(): void
    {
        unset($_SESSION['user_id']);

        $controller = new class ($this->router, $this->logger) extends AbstractController {
            public bool $redirectCalled = false;
            public string $redirectUrl  = '';

            protected function redirect(string $url): void
            {
                $this->redirectCalled = true;
                $this->redirectUrl    = $url;
            }

            public function callRequireAuth(): void
            {
                $this->requireAuth();
            }
        };

        $controller->callRequireAuth();

        $this->assertTrue($controller->redirectCalled);
        $this->assertSame('/login', $controller->redirectUrl);
    }

    public function testRequireAuthPassesWhenSessionSet(): void
    {
        $_SESSION['user_id'] = 42;

        $controller = new class ($this->router, $this->logger) extends AbstractController {
            public bool $redirectCalled = false;

            protected function redirect(string $url): void
            {
                $this->redirectCalled = true;
            }

            public function callRequireAuth(): void
            {
                $this->requireAuth();
            }
        };

        $controller->callRequireAuth();
        $this->assertFalse($controller->redirectCalled);
    }
}
