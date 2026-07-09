<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Database;
use App\Exceptions\DatabaseException;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = ':memory:';
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
    }

    public function testGetConnectionReturnsPdo(): void
    {
        $pdo = Database::getConnection();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testGetConnectionReturnsSameInstance(): void
    {
        $pdo1 = Database::getConnection();
        $pdo2 = Database::getConnection();
        $this->assertSame($pdo1, $pdo2);
    }

    public function testResetClearsInstance(): void
    {
        $pdo1 = Database::getConnection();
        Database::reset();
        $pdo2 = Database::getConnection();
        $this->assertNotSame($pdo1, $pdo2);
    }

    public function testSchemaCreatedOnMemoryDb(): void
    {
        $pdo    = Database::getConnection();
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertContains('users', $tables);
        $this->assertContains('recipes', $tables);
        $this->assertContains('favorites', $tables);
    }

    public function testCanInsertAndSelectUser(): void
    {
        $pdo = Database::getConnection();
        $pdo->exec("INSERT INTO users (name, email, password) VALUES ('Elvina', 'e@test.com', 'hash')");
        $user = $pdo->query("SELECT * FROM users WHERE email = 'e@test.com'")->fetch();
        $this->assertSame('Elvina', $user['name']);
    }

    public function testCanInsertAndSelectRecipe(): void
    {
        $pdo = Database::getConnection();
        $pdo->exec("INSERT INTO recipes (title, ingredients, steps) VALUES ('Тест', 'ингр', 'шаги')");
        $recipe = $pdo->query("SELECT * FROM recipes WHERE title = 'Тест'")->fetch();
        $this->assertSame('Тест', $recipe['title']);
    }

    public function testFavoritesUniqueConstraint(): void
    {
        $pdo = Database::getConnection();
        $pdo->exec("INSERT INTO users (name, email, password) VALUES ('U', 'u@u.com', 'h')");
        $pdo->exec("INSERT INTO recipes (title, ingredients, steps) VALUES ('R', 'i', 's')");
        $pdo->exec("INSERT INTO favorites (user_id, recipe_id) VALUES (1, 1)");

        $this->expectException(\PDOException::class);
        $pdo->exec("INSERT INTO favorites (user_id, recipe_id) VALUES (1, 1)");
    }

    public function testFileDbCreatesAndConnects(): void
    {
        Database::reset();
        $tmpPath = sys_get_temp_dir() . '/quickrecipe_test_' . uniqid() . '.db';
        $_ENV['DB_PATH'] = $tmpPath;

        try {
            $pdo = Database::getConnection();
            $this->assertInstanceOf(\PDO::class, $pdo);
            $this->assertFileExists($tmpPath);

            // seed должен был отработать — проверяем что рецепты есть
            $count = (int) $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
            $this->assertGreaterThan(0, $count);
        } finally {
            Database::reset();
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
            $_ENV['DB_PATH'] = ':memory:';
        }
    }

    public function testInvalidDsnThrowsDatabaseException(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = '/root/no_permission_ever/test.db';

        if (posix_getuid() === 0) {
            $this->markTestSkipped('Запущено от root — тест пропущен');
        }

        $this->expectException(\App\Exceptions\DatabaseException::class);
        Database::getConnection();
    }
}
