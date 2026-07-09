<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controllers\RecipeController;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Router;
use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class RecipeControllerTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
        unset($_SESSION['user_id'], $_SESSION['user_name']);
    }

    private function makeController(): RecipeController
    {
        return new class ($this->router, $this->logger) extends RecipeController {
            public array $rendered = [];
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

    private function seedRecipe(string $title = 'Тест', string $ingredients = 'соль'): Recipe
    {
        $recipe = new Recipe();
        $recipe->title = $title;
        $recipe->description = 'Описание';
        $recipe->ingredients = $ingredients;
        $recipe->steps = 'Шаги';
        $recipe->cook_time = 10;
        $recipe->difficulty = 'easy';
        $recipe->category = 'other';
        $recipe->save();

        return $recipe;
    }

    private function seedUser(): User
    {
        $user = new User();
        $user->name = 'Elvina';
        $user->email = 'user@test.com';
        $user->password = 'hash';
        $user->save();

        return $user;
    }

    public function testListRendersRecipes(): void
    {
        $this->seedRecipe();

        $ctrl = $this->makeController();

        $ctrl->list();

        $this->assertSame('recipes/list', $ctrl->rendered[0]['view']);
        $this->assertNotEmpty($ctrl->rendered[0]['data']['recipes']);
        $this->assertSame([], $ctrl->rendered[0]['data']['favoriteIds']);
    }

    public function testListWithSessionLoadsFavorites(): void
    {
        $user = $this->seedUser();
        $recipe = $this->seedRecipe();

        Favorite::add($user->id, $recipe->id);
        $_SESSION['user_id'] = $user->id;

        $ctrl = $this->makeController();

        $ctrl->list();

        $this->assertSame('recipes/list', $ctrl->rendered[0]['view']);
        $this->assertContains($recipe->id, $ctrl->rendered[0]['data']['favoriteIds']);
    }

    public function testSearchRendersEmptyWhenNoQuery(): void
    {
        $this->router
            ->method('getQueryParam')
            ->willReturn('');

        $ctrl = $this->makeController();

        $ctrl->search();

        $this->assertSame('recipes/search', $ctrl->rendered[0]['view']);
        $this->assertSame([], $ctrl->rendered[0]['data']['results']);
    }

    public function testSearchRendersResultsWithQuery(): void
    {
        $this->seedRecipe('Салат', 'огурцы, помидоры');

        $this->router
            ->method('getQueryParam')
            ->willReturn('огурцы');

        $ctrl = $this->makeController();

        $ctrl->search();

        $this->assertSame('recipes/search', $ctrl->rendered[0]['view']);
        $this->assertCount(1, $ctrl->rendered[0]['data']['results']);
    }

    public function testHandleSearchRedirects(): void
    {
        $this->router
            ->method('getBodyParam')
            ->willReturn('огурцы, помидоры');

        $ctrl = $this->makeController();

        $ctrl->handleSearch();

        $this->assertStringContainsString('/recipes/search?ingredients=', $ctrl->redirects[0]);
    }

    public function testShowRendersRecipe(): void
    {
        $recipe = $this->seedRecipe('Борщ');

        $this->router
            ->method('getQueryParam')
            ->willReturn((string) $recipe->id);

        $ctrl = $this->makeController();

        $ctrl->show();

        $this->assertSame('recipes/show', $ctrl->rendered[0]['view']);
        $this->assertSame('Борщ', $ctrl->rendered[0]['data']['title']);
        $this->assertInstanceOf(Recipe::class, $ctrl->rendered[0]['data']['recipe']);
    }

    public function testShowWithInvalidIdRendersNotFoundTitle(): void
    {
        $this->router
            ->method('getQueryParam')
            ->willReturn('99999');

        $ctrl = $this->makeController();

        $ctrl->show();

        $this->assertSame('recipes/show', $ctrl->rendered[0]['view']);
        $this->assertSame('Рецепт не найден', $ctrl->rendered[0]['data']['title']);
        $this->assertNull($ctrl->rendered[0]['data']['recipe']);
    }

    public function testCreateFormRequiresAuth(): void
    {
        $ctrl = $this->makeController();

        $ctrl->createForm();

        $this->assertContains('/login', $ctrl->redirects);
    }

    public function testCreateFormRendersWhenAuthorized(): void
    {
        $_SESSION['user_id'] = 1;

        $ctrl = $this->makeController();

        $ctrl->createForm();

        $this->assertSame('recipes/create', $ctrl->rendered[0]['view']);
        $this->assertSame('Новый рецепт', $ctrl->rendered[0]['data']['title']);
    }

    public function testCreateWithEmptyFieldsShowsError(): void
    {
        $_SESSION['user_id'] = 1;

        $this->router
            ->method('getBodyParam')
            ->willReturn('');

        $ctrl = $this->makeController();

        $ctrl->create();

        $this->assertSame('recipes/create', $ctrl->rendered[0]['view']);
        $this->assertSame(
            'Заполните обязательные поля: название, ингредиенты, шаги',
            $ctrl->rendered[0]['data']['error']
        );
    }

    public function testCreateSuccessRedirects(): void
    {
        $_SESSION['user_id'] = 1;

        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['title', '', 'Новый рецепт'],
                ['description', '', 'Описание'],
                ['ingredients', '', 'соль, перец'],
                ['steps', '', '1. Сделать'],
                ['cook_time', 0, '15'],
                ['difficulty', 'easy', 'easy'],
                ['category', 'other', 'lunch'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->create();

        $this->assertContains('/recipes', $ctrl->redirects);

        $all = Recipe::all();
        $this->assertNotEmpty($all);
    }
}
