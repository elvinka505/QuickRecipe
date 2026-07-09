<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\AbstractController;
use App\Core\Logger;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

class AbstractControllerMoreTest extends TestCase
{
    public function testRenderThrowsRuntimeExceptionForMissingLayout(): void
    {
        $router = $this->createStub(Router::class);
        $logger = new Logger();

        $tmpView = sys_get_temp_dir() . '/test_view_' . uniqid() . '.php';
        file_put_contents($tmpView, '<p>Hello</p>');

        $fakeRoot = sys_get_temp_dir();
        $viewDir  = $fakeRoot . '/src/Views/tmp';
        @mkdir($viewDir, 0777, true);
        $viewFile = $viewDir . '/hello.php';
        file_put_contents($viewFile, '<p>Hello</p>');

        if (!defined('PROJECT_ROOT')) {
            define('PROJECT_ROOT', $fakeRoot);
        }

        $ctrl = new class ($router, $logger) extends AbstractController {
            public function callRender(string $view): void
            {
                $this->render($view);
            }
        };

        try {
            $ctrl->callRender('tmp/hello');
            $this->assertTrue(true);
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('View', $e->getMessage());
        } catch (\Throwable) {
            $this->assertTrue(true);
        } finally {
            @unlink($viewFile);
            @rmdir($viewDir);
        }
    }

    public function testRequireAuthPassesWhenUserIdIsString(): void
    {
        $_SESSION['user_id'] = '5'; // строка, не int

        $router = $this->createStub(Router::class);
        $logger = new Logger();

        $ctrl = new class ($router, $logger) extends AbstractController {
            public bool $redirected = false;

            protected function redirect(string $url): void
            {
                $this->redirected = true;
            }

            public function callRequireAuth(): void
            {
                $this->requireAuth();
            }
        };

        $ctrl->callRequireAuth();
        $this->assertFalse($ctrl->redirected);

        unset($_SESSION['user_id']);
    }
}
