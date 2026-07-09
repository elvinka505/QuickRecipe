<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database;
use PDO;
use Tests\Support\InMemoryDatabaseTestCase;

class DatabaseIntegrationTest extends InMemoryDatabaseTestCase
{
    public function testGetConnectionReturnsPdoInstanceAndCreatesSchema(): void
    {
        $pdo = Database::getConnection();

        $this->assertInstanceOf(PDO::class, $pdo);

        $tables = $pdo->query("
            SELECT name
            FROM sqlite_master
            WHERE type = 'table'
              AND name IN ('users', 'recipes', 'favorites')
            ORDER BY name
        ")->fetchAll(PDO::FETCH_COLUMN);

        $this->assertSame(['favorites', 'recipes', 'users'], $tables);
    }

    public function testGetConnectionReturnsSameInstanceUntilReset(): void
    {
        $first = Database::getConnection();
        $second = Database::getConnection();

        $this->assertSame($first, $second);

        Database::reset();

        $third = Database::getConnection();

        $this->assertNotSame($first, $third);
    }

    public function testSchemaAllowsInsertIntoAllTables(): void
    {
        $pdo = Database::getConnection();

        $pdo->exec("
            INSERT INTO users (name, email, password)
            VALUES ('Test User', 'schema@example.com', 'pass')
        ");

        $pdo->exec("
            INSERT INTO recipes (title, description, ingredients, steps, cook_time, difficulty, category)
            VALUES ('Schema Recipe', 'Desc', 'salt', 'Cook', 10, 'easy', 'other')
        ");

        $userId = (int) $pdo->lastInsertId();

        $recipeId = (int) $pdo->query("
            SELECT id FROM recipes WHERE title = 'Schema Recipe' LIMIT 1
        ")->fetchColumn();

        $pdo->exec("
            INSERT INTO favorites (user_id, recipe_id)
            VALUES ({$userId}, {$recipeId})
        ");

        $count = (int) $pdo->query("SELECT COUNT(*) FROM favorites")->fetchColumn();

        $this->assertSame(1, $count);
    }
}
