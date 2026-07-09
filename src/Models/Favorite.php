<?php

namespace App\Models;

use App\Core\Database;
use App\Exceptions\DatabaseException;
use PDOException;

class Favorite
{
    public static function add(int $userId, int $recipeId): void
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                INSERT OR IGNORE INTO favorites (user_id, recipe_id)
                VALUES (:user_id, :recipe_id)
            ");
            $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
        } catch (PDOException $e) {
            throw new DatabaseException('Favorite::add() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function remove(int $userId, int $recipeId): void
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id
            ");
            $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
        } catch (PDOException $e) {
            throw new DatabaseException('Favorite::remove() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function getByUser(int $userId): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT r.* FROM recipes r
                JOIN favorites f ON f.recipe_id = r.id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            return array_map(Recipe::fromRow(...), $stmt->fetchAll());
        } catch (PDOException $e) {
            throw new DatabaseException('Favorite::getByUser() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function exists(int $userId, int $recipeId): bool
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT 1 FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id
            ");
            $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            throw new DatabaseException('Favorite::exists() — ' . $e->getMessage(), 0, $e);
        }
    }
}
