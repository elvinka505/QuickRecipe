<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Recipe;
use PHPUnit\Framework\TestCase;

class RecipeFromRowTest extends TestCase
{
    public function testFromRowBuildsRecipeObject(): void
    {
        $recipe = Recipe::fromRow([
            'id' => 7,
            'title' => 'Суп',
            'description' => 'Тёплый суп',
            'ingredients' => 'вода, соль',
            'steps' => 'Сварить',
            'cook_time' => 20,
            'difficulty' => 'easy',
            'category' => 'dinner',
        ]);

        $this->assertSame(7, $recipe->id);
        $this->assertSame('Суп', $recipe->title);
        $this->assertSame('Тёплый суп', $recipe->description);
        $this->assertSame('вода, соль', $recipe->ingredients);
        $this->assertSame('Сварить', $recipe->steps);
        $this->assertSame(20, $recipe->cook_time);
        $this->assertSame('easy', $recipe->difficulty);
        $this->assertSame('dinner', $recipe->category);
    }
}
