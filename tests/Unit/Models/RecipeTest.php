<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Core\Database;
use App\Models\Recipe;
use PHPUnit\Framework\TestCase;

class RecipeTest extends TestCase
{
    protected function setUp(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = ':memory:';
        Database::getConnection();
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
    }

    private function makeRecipe(string $title = 'Тест', string $ingredients = 'соль'): Recipe
    {
        $r              = new Recipe();
        $r->title       = $title;
        $r->ingredients = $ingredients;
        $r->steps       = 'шаги';
        $r->save();
        return $r;
    }

    public function testSaveInserts(): void
    {
        $r = $this->makeRecipe();
        $this->assertNotNull($r->id);
        $this->assertGreaterThan(0, $r->id);
    }

    public function testSaveUpdates(): void
    {
        $r        = $this->makeRecipe();
        $r->title = 'Обновлено';
        $r->save();

        $found = Recipe::find($r->id);
        $this->assertSame('Обновлено', $found->title);
    }

    public function testFind(): void
    {
        $r     = $this->makeRecipe('Суп');
        $found = Recipe::find($r->id);
        $this->assertInstanceOf(Recipe::class, $found);
        $this->assertSame('Суп', $found->title);
    }

    public function testFindReturnsNull(): void
    {
        $this->assertNull(Recipe::find(99999));
    }

    public function testAll(): void
    {
        $this->makeRecipe();
        $all = Recipe::all();
        $this->assertNotEmpty($all);
        $this->assertInstanceOf(Recipe::class, $all[0]);
    }

    public function testDelete(): void
    {
        $r  = $this->makeRecipe();
        $id = $r->id;
        $r->delete();
        $this->assertNull(Recipe::find($id));
    }

    public function testDeleteReturnsFalseWhenNoId(): void
    {
        $r = new Recipe();
        $this->assertFalse($r->delete());
    }

    public function testSearchByIngredients(): void
    {
        $this->makeRecipe('Салат', 'огурцы, помидоры');
        $this->makeRecipe('Суп', 'морковь, лук');

        $results = Recipe::searchByIngredients('огурцы');
        $this->assertCount(1, $results);
        $this->assertSame('Салат', $results[0]->title);
    }

    public function testSearchByIngredientsEmptyReturnsAll(): void
    {
        $this->makeRecipe('A', 'x');
        $this->makeRecipe('B', 'y');

        $results = Recipe::searchByIngredients('');
        $this->assertCount(2, $results);
    }

    public function testSearchByIngredientsMultipleKeywords(): void
    {
        $this->makeRecipe('Паста', 'спагетти, бекон');
        $this->makeRecipe('Салат', 'огурцы');

        $results = Recipe::searchByIngredients('бекон, огурцы');
        $this->assertCount(2, $results);
    }

    public function testFromRow(): void
    {
        $r = Recipe::fromRow([
            'id' => 1, 'title' => 'T', 'description' => 'D',
            'ingredients' => 'I', 'steps' => 'S',
            'cook_time' => 10, 'difficulty' => 'easy', 'category' => 'lunch',
        ]);
        $this->assertSame(1, $r->id);
        $this->assertSame('T', $r->title);
    }
}
