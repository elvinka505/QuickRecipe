<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\DatabaseException;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (!self::$instance instanceof PDO) {
            self::$instance = self::connect();
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    private static function connect(): PDO
    {
        $logger = new Logger();

        try {
            $dbPath = (string) ($_ENV['DB_PATH'] ?? __DIR__ . '/../../database/app.db');

            if ($dbPath === ':memory:') {
                $pdo = new PDO('sqlite::memory:');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                $logger->info('Подключение к in-memory БД успешно');
                self::initSchema($pdo, $logger);

                return $pdo;
            }

            if (!str_starts_with($dbPath, '/')) {
                $dbPath = dirname(__DIR__, 2) . '/' . $dbPath;
            }

            $isNewDb = !file_exists($dbPath);
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $logger->info('Подключение к БД успешно');

            if ($isNewDb) {
                self::initSchema($pdo, $logger);
                self::seed($pdo, $logger);
            }

            return $pdo;
        } catch (PDOException $e) {
            $logger->logException($e);
            throw new DatabaseException('Ошибка подключения к БД: ' . $e->getMessage(), 0, $e);
        }
    }

    private static function initSchema(PDO $pdo, Logger $logger): void
    {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    name       TEXT NOT NULL,
                    email      TEXT UNIQUE NOT NULL,
                    password   TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS recipes (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    title       TEXT NOT NULL,
                    description TEXT,
                    ingredients TEXT NOT NULL,
                    steps       TEXT NOT NULL,
                    cook_time   INTEGER DEFAULT 0,
                    difficulty  TEXT DEFAULT 'easy',
                    category    TEXT DEFAULT 'other',
                    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS favorites (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id    INTEGER NOT NULL,
                    recipe_id  INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
                    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
                    UNIQUE(user_id, recipe_id)
                )
            ");

            $logger->info('Схема БД создана');
        } catch (PDOException $e) {
            $logger->logException($e);
            throw new DatabaseException('Ошибка создания схемы БД: ' . $e->getMessage(), 0, $e);
        }
    }

    private static function seed(PDO $pdo, Logger $logger): void
    {
        try {
            $recipes = [
                [
                    'title' => 'Яичница с помидорами',
                    'description' => 'Быстрый и сытный завтрак',
                    'ingredients' => 'яйца, помидоры, соль, масло',
                    'steps' => "1. Разогрей сковороду.\n2. Обжарь помидоры.\n3. Вбей яйца.\n4. Посоли.",
                    'cook_time' => 10,
                    'difficulty' => 'easy',
                    'category' => 'breakfast',
                ],
                [
                    'title' => 'Паста Карбонара',
                    'description' => 'Классическая итальянская паста',
                    'ingredients' => 'спагетти, бекон, яйца, пармезан, чёрный перец',
                    'steps' => "1. Отвари спагетти.\n2. Обжарь бекон.\n3. Смешай яйца с сыром.\n4. Соедини всё.",
                    'cook_time' => 25,
                    'difficulty' => 'medium',
                    'category' => 'lunch',
                ],
                [
                    'title' => 'Греческий салат',
                    'description' => 'Лёгкий средиземноморский салат',
                    'ingredients' => 'огурцы, помидоры, перец, оливки, фета, оливковое масло',
                    'steps' => "1. Нарежь овощи.\n2. Добавь оливки и фету.\n3. Заправь маслом.",
                    'cook_time' => 15,
                    'difficulty' => 'easy',
                    'category' => 'lunch',
                ],
                [
                    'title' => 'Куриный суп',
                    'description' => 'Согревающий домашний суп',
                    'ingredients' => 'курица, морковь, лук, картофель, соль, перец, зелень',
                    'steps' => "1. Свари курицу.\n2. Добавь овощи.\n3. Посоли, поперчи.\n4. Укрась зеленью.",
                    'cook_time' => 60,
                    'difficulty' => 'easy',
                    'category' => 'dinner',
                ],
                [
                    'title' => 'Шоколадный брауни',
                    'description' => 'Насыщенный шоколадный десерт',
                    'ingredients' => 'шоколад, масло, сахар, яйца, мука',
                    'steps' => "1. Растопи шоколад с маслом.\n" .
                        "2. Добавь сахар и яйца.\n" .
                        "3. Вмешай муку.\n" .
                        "4. Выпекай 25 мин при 180°.",
                    'cook_time' => 40,
                    'difficulty' => 'medium',
                    'category' => 'dessert',
                ],
            ];

            $stmt = $pdo->prepare("
                INSERT INTO recipes (title, description, ingredients, steps, cook_time, difficulty, category)
                VALUES (:title, :description, :ingredients, :steps, :cook_time, :difficulty, :category)
            ");

            foreach ($recipes as $recipe) {
                $stmt->execute($recipe);
            }

            $logger->info('Seed-данные добавлены');
        } catch (PDOException $e) {
            $logger->logException($e);
        }
    }
}
