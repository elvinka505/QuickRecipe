<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controllers\HomeController;
use App\Core\Logger;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    public function testIndexRendersHomePage(): void
    {
        $router = $this->createStub(Router::class);
        $logger = new Logger();

        $ctrl = new class ($router, $logger) extends HomeController {
            public array $rendered = [];

            protected function render(string $view, array $data = []): void
            {
                $this->rendered[] = ['view' => $view, 'data' => $data];
            }
        };

        $ctrl->index();

        $this->assertSame('home/index', $ctrl->rendered[0]['view']);
        $this->assertStringContainsString('QuickRecipe', $ctrl->rendered[0]['data']['title']);
    }
}
