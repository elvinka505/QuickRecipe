<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\User;
use Tests\Support\InMemoryDatabaseTestCase;

class FavoriteIntegrationTest extends InMemoryDatabaseTestCase
{
    public function testFavoriteCanBeAddedCheckedAndRemoved(): void
    {
        $user = new User();
        $user->name = 'Elvina';
        $user->email = 'fav@example.com';
        $user->password = 'hashed';
        $user->save();

        $recipe = new Recipe();
        $recipe->title = 'Пюре';
        $recipe->description = 'Картофельное';
        $recipe->ingredients = 'картофель, масло';
        $recipe->steps = 'Сварить и размять';
        $recipe->save();

        Favorite::add((int) $user->id, (int) $recipe->id);

        $this->assertTrue(Favorite::exists((int) $user->id, (int) $recipe->id));

        $favorites = Favorite::getByUser((int) $user->id);
        $this->assertCount(1, $favorites);
        $this->assertSame('Пюре', $favorites[0]->title);

        Favorite::remove((int) $user->id, (int) $recipe->id);

        $this->assertFalse(Favorite::exists((int) $user->id, (int) $recipe->id));
    }

    public function testAddIsIgnoredWhenFavoriteAlreadyExists(): void
    {
        $user = new User();
        $user->name = 'Repeat';
        $user->email = 'repeat@example.com';
        $user->password = 'pass';
        $user->save();

        $recipe = new Recipe();
        $recipe->title = 'Повтор';
        $recipe->ingredients = 'картофель';
        $recipe->steps = 'Шаги';
        $recipe->save();

        Favorite::add((int) $user->id, (int) $recipe->id);
        Favorite::add((int) $user->id, (int) $recipe->id);

        $favorites = Favorite::getByUser((int) $user->id);

        $this->assertCount(1, $favorites);
        $this->assertTrue(Favorite::exists((int) $user->id, (int) $recipe->id));
    }

    public function testGetByUserReturnsEmptyArrayWhenNoFavorites(): void
    {
        $user = new User();
        $user->name = 'No Favorites';
        $user->email = 'nofav@example.com';
        $user->password = 'pass';
        $user->save();

        $favorites = Favorite::getByUser((int) $user->id);

        $this->assertSame([], $favorites);
        $this->assertFalse(Favorite::exists((int) $user->id, 9999));
    }

    public function testRemoveNonExistingFavoriteDoesNotCrash(): void
    {
        $user = new User();
        $user->name = 'Ghost';
        $user->email = 'ghost@example.com';
        $user->password = 'pass';
        $user->save();

        Favorite::remove((int) $user->id, 12345);

        $this->assertSame([], Favorite::getByUser((int) $user->id));
    }

    public function testExistsReturnsFalseAfterRemovingFavorite(): void
    {
        $user = new \App\Models\User();
        $user->name = 'Tester';
        $user->email = 'tester-fav@example.com';
        $user->password = 'pass';
        $user->save();

        $recipe = new \App\Models\Recipe();
        $recipe->title = 'Fav recipe';
        $recipe->ingredients = 'egg';
        $recipe->steps = 'cook';
        $recipe->save();

        \App\Models\Favorite::add((int) $user->id, (int) $recipe->id);
        $this->assertTrue(\App\Models\Favorite::exists((int) $user->id, (int) $recipe->id));

        \App\Models\Favorite::remove((int) $user->id, (int) $recipe->id);

        $this->assertFalse(\App\Models\Favorite::exists((int) $user->id, (int) $recipe->id));
    }
}
