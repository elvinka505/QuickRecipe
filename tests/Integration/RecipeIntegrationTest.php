<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Recipe;
use Tests\Support\InMemoryDatabaseTestCase;

class RecipeIntegrationTest extends InMemoryDatabaseTestCase
{
    public function testRecipeCanBeSavedAndFound(): void
    {
        $recipe = new Recipe();
        $recipe->title = 'Тестовый суп';
        $recipe->description = 'Описание';
        $recipe->ingredients = 'вода, соль';
        $recipe->steps = 'Сварить';
        $recipe->cook_time = 15;
        $recipe->difficulty = 'easy';
        $recipe->category = 'dinner';

        $saved = $recipe->save();
        $found = Recipe::find((int) $recipe->id);

        $this->assertTrue($saved);
        $this->assertNotNull($recipe->id);
        $this->assertNotNull($found);
        $this->assertSame('Тестовый суп', $found->title);
        $this->assertSame('вода, соль', $found->ingredients);
    }

    public function testSearchByIngredientsReturnsMatchingRecipes(): void
    {
        $recipe = new Recipe();
        $recipe->title = 'Омлет';
        $recipe->description = 'Завтрак';
        $recipe->ingredients = 'яйца, молоко, соль';
        $recipe->steps = 'Смешать и пожарить';
        $recipe->save();

        $results = Recipe::searchByIngredients('молоко');

        $this->assertNotEmpty($results);
        $this->assertSame('Омлет', $results[0]->title);
    }

    public function testAllReturnsSavedRecipes(): void
    {
        $first = new Recipe();
        $first->title = 'Борщ';
        $first->description = 'Суп';
        $first->ingredients = 'свекла, вода';
        $first->steps = 'Сварить';
        $first->save();

        $second = new Recipe();
        $second->title = 'Каша';
        $second->description = 'Завтрак';
        $second->ingredients = 'овсянка, молоко';
        $second->steps = 'Сварить';
        $second->save();

        $recipes = Recipe::all();

        $this->assertCount(2, $recipes);
        $this->assertContainsOnlyInstancesOf(Recipe::class, $recipes);
    }

    public function testUpdateChangesSavedRecipe(): void
    {
        $recipe = new Recipe();
        $recipe->title = 'Старый рецепт';
        $recipe->description = 'Описание';
        $recipe->ingredients = 'соль';
        $recipe->steps = 'Шаги';
        $recipe->save();

        $recipe->title = 'Новый рецепт';
        $recipe->ingredients = 'соль, перец';

        $this->assertTrue($recipe->save());

        $updated = Recipe::find((int) $recipe->id);

        $this->assertNotNull($updated);
        $this->assertSame('Новый рецепт', $updated->title);
        $this->assertSame('соль, перец', $updated->ingredients);
    }

    public function testDeleteRemovesRecipe(): void
    {
        $recipe = new Recipe();
        $recipe->title = 'Удаляемый рецепт';
        $recipe->description = 'Описание';
        $recipe->ingredients = 'ингредиенты';
        $recipe->steps = 'шаги';
        $recipe->save();

        $id = (int) $recipe->id;

        $this->assertTrue($recipe->delete());
        $this->assertNull(Recipe::find($id));
    }

    public function testDeleteReturnsFalseWhenRecipeHasNoId(): void
    {
        $recipe = new Recipe();

        $this->assertFalse($recipe->delete());
    }

    public function testSearchByIngredientsWithEmptyQueryReturnsAllRecipes(): void
    {
        $first = new Recipe();
        $first->title = 'Рецепт 1';
        $first->ingredients = 'яйца';
        $first->steps = 'Шаги';
        $first->save();

        $second = new Recipe();
        $second->title = 'Рецепт 2';
        $second->ingredients = 'молоко';
        $second->steps = 'Шаги';
        $second->save();

        $results = Recipe::searchByIngredients('');

        $this->assertCount(2, $results);
    }
}
