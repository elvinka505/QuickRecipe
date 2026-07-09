<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Attributes\Route;
use App\Models\Favorite;
use App\Models\Recipe;

class RecipeController extends AbstractController
{
    #[Route('/recipes')]
    public function list(): void
    {
        $recipes = Recipe::all();

        $favoriteIds = [];
        if (!empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
            $favorites = Favorite::getByUser($userId);
            $favoriteIds = array_map(fn ($recipe) => $recipe->id, $favorites);
        }

        $this->render('recipes/list', [
            'title' => 'Все рецепты',
            'recipes' => $recipes,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    #[Route('/recipes/search')]
    public function search(): void
    {
        $ingredients = $this->router->getQueryParam('ingredients', '');
        $results = $ingredients ? Recipe::searchByIngredients($ingredients) : [];

        $this->render('recipes/search', [
            'title' => 'Поиск рецептов',
            'ingredients' => $ingredients,
            'results' => $results,
        ]);
    }

    #[Route('/recipes/search', 'POST')]
    public function handleSearch(): void
    {
        $ingredients = $this->router->getBodyParam('ingredients', '');
        $this->redirect('/recipes/search?ingredients=' . urlencode((string) $ingredients));
    }

    #[Route('/recipes/show')]
    public function show(): void
    {
        $id = (int) $this->router->getQueryParam('id', '0');
        $recipe = Recipe::find($id);

        $this->render('recipes/show', [
            'title' => $recipe instanceof Recipe ? $recipe->title : 'Рецепт не найден',
            'recipe' => $recipe,
        ]);
    }

    #[Route('/recipes/create')]
    public function createForm(): void
    {
        $this->requireAuth();
        $this->render('recipes/create', ['title' => 'Новый рецепт']);
    }

    #[Route('/recipes/create', 'POST')]
    public function create(): void
    {
        $this->requireAuth();

        $title = trim((string) $this->router->getBodyParam('title', ''));
        $description = trim((string) $this->router->getBodyParam('description', ''));
        $ingredients = trim((string) $this->router->getBodyParam('ingredients', ''));
        $steps = trim((string) $this->router->getBodyParam('steps', ''));
        $cookTime = (int) $this->router->getBodyParam('cook_time', '0');
        $difficulty = $this->router->getBodyParam('difficulty', 'easy');
        $category = $this->router->getBodyParam('category', 'other');

        if (
            $title === ''
            || $title === '0'
            || $ingredients === ''
            || $ingredients === '0'
            || $steps === ''
            || $steps === '0'
        ) {
            $this->render('recipes/create', [
                'title' => 'Новый рецепт',
                'error' => 'Заполните обязательные поля: название, ингредиенты, шаги',
            ]);
            return;
        }

        $recipe = new Recipe();
        $recipe->title = $title;
        $recipe->description = $description;
        $recipe->ingredients = $ingredients;
        $recipe->steps = $steps;
        $recipe->cook_time = $cookTime;
        $recipe->difficulty = $difficulty;
        $recipe->category = $category;
        $recipe->save();

        $this->redirect('/recipes');
    }
}
