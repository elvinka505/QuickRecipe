<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controllers\FavoritesController;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Router;
use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class FavoritesControllerTest extends TestCase
{
    private Router $router;
    private Logger $logger;

    protected function setUp(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = ':memory:';
        Database::getConnection();

        $this->router = $this->createStub(Router::class);
        $this->logger = new Logger();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        unset($_SESSION['user_id'], $_SESSION['user_name']);
        $_POST = [];
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
        unset($_SESSION['user_id'], $_SESSION['user_name']);
        $_POST = [];
    }

    private function makeController(): FavoritesController
    {
        return new class ($this->router, $this->logger) extends FavoritesController {
            public array $rendered  = [];
            public array $redirects = [];

            protected function render(string $view, array $data = []): void
            {
                $this->rendered[] = ['view' => $view, 'data' => $data];
            }

            protected function redirect(string $url): void
            {
                $this->redirects[] = $url;
            }
        };
    }

    public function testListRedirectsWhenNotLoggedIn(): void
    {
        unset($_SESSION['user_id']);

        $ctrl = $this->makeController();
        $ctrl->list();

        $this->assertContains('/login', $ctrl->redirects);
    }

    public function testListRendersWhenLoggedIn(): void
    {
        $user           = new User();
        $user->name     = 'Elvina';
        $user->email    = 'e@test.com';
        $user->password = 'hash';
        $user->save();

        $_SESSION['user_id'] = $user->id;

        $ctrl = $this->makeController();
        $ctrl->list();

        $this->assertSame('favorites/list', $ctrl->rendered[0]['view']);
        $this->assertSame('Избранное', $ctrl->rendered[0]['data']['title']);
    }

    public function testAddWithoutAuthRedirects(): void
    {
        unset($_SESSION['user_id']);

        $ctrl = $this->makeController();
        $ctrl->add();

        $this->assertNotEmpty($ctrl->redirects);
    }

    public function testAddAddsToFavoritesAndRedirects(): void
    {
        $user           = new User();
        $user->name     = 'Elvina';
        $user->email    = 'e@test.com';
        $user->password = 'hash';
        $user->save();

        $recipe              = new Recipe();
        $recipe->title       = 'Soup';
        $recipe->ingredients = 'water';
        $recipe->steps       = 'boil';
        $recipe->save();

        $_SESSION['user_id'] = $user->id;

        $this->router
            ->method('getBody')
            ->willReturn(['recipe_id' => (string) $recipe->id]);

        $ctrl = $this->makeController();
        $ctrl->add();

        $favorites = Favorite::getByUser((int) $user->id);
        $this->assertCount(1, $favorites);
        $this->assertContains('/favorites', $ctrl->redirects);
    }

    public function testRemoveWithoutAuthRedirects(): void
    {
        unset($_SESSION['user_id']);

        $ctrl = $this->makeController();
        $ctrl->remove();

        $this->assertNotEmpty($ctrl->redirects);
    }

    public function testRemoveRemovesFromFavoritesAndRedirects(): void
    {
        $user           = new User();
        $user->name     = 'Elvina';
        $user->email    = 'e@test.com';
        $user->password = 'hash';
        $user->save();

        $recipe              = new Recipe();
        $recipe->title       = 'Soup';
        $recipe->ingredients = 'water';
        $recipe->steps       = 'boil';
        $recipe->save();

        Favorite::add((int) $user->id, (int) $recipe->id);

        $_SESSION['user_id'] = $user->id;

        $this->router
            ->method('getBody')
            ->willReturn(['recipe_id' => (string) $recipe->id]);

        $ctrl = $this->makeController();
        $ctrl->remove();

        $favorites = Favorite::getByUser((int) $user->id);
        $this->assertCount(0, $favorites);
        $this->assertContains('/favorites', $ctrl->redirects);
    }
}
