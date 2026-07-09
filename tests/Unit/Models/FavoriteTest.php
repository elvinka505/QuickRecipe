<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Core\Database;
use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class FavoriteTest extends TestCase
{
    private int $userId;
    private int $recipeId;

    protected function setUp(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = ':memory:';
        $pdo = Database::getConnection();

        $u           = new User();
        $u->name     = 'Test';
        $u->email    = 'fav@test.com';
        $u->password = 'hash';
        $u->save();
        $this->userId = $u->id;

        $r              = new Recipe();
        $r->title       = 'Рецепт';
        $r->ingredients = 'x';
        $r->steps       = 'y';
        $r->save();
        $this->recipeId = $r->id;
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
    }

    public function testAddFavorite(): void
    {
        Favorite::add($this->userId, $this->recipeId);
        $this->assertTrue(Favorite::exists($this->userId, $this->recipeId));
    }

    public function testAddIgnoresDuplicate(): void
    {
        Favorite::add($this->userId, $this->recipeId);
        Favorite::add($this->userId, $this->recipeId); // INSERT OR IGNORE — не бросает
        $this->assertTrue(Favorite::exists($this->userId, $this->recipeId));
    }

    public function testRemoveFavorite(): void
    {
        Favorite::add($this->userId, $this->recipeId);
        Favorite::remove($this->userId, $this->recipeId);
        $this->assertFalse(Favorite::exists($this->userId, $this->recipeId));
    }

    public function testExistsReturnsFalseWhenNotAdded(): void
    {
        $this->assertFalse(Favorite::exists($this->userId, $this->recipeId));
    }

    public function testGetByUser(): void
    {
        Favorite::add($this->userId, $this->recipeId);
        $favorites = Favorite::getByUser($this->userId);
        $this->assertCount(1, $favorites);
        $this->assertInstanceOf(Recipe::class, $favorites[0]);
        $this->assertSame($this->recipeId, $favorites[0]->id);
    }

    public function testGetByUserReturnsEmptyWhenNoFavorites(): void
    {
        $favorites = Favorite::getByUser($this->userId);
        $this->assertSame([], $favorites);
    }
}
