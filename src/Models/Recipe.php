<?php

namespace App\Models;

use App\Core\Database;
use App\Exceptions\DatabaseException;
use PDOException;

class Recipe
{
    public ?int $id          = null;
    public string $title       = '';
    public string $description = '';
    public string $ingredients = '';
    public string $steps       = '';
    public int $cook_time   = 0;
    public string $difficulty  = 'easy';
    public string $category    = 'other';

    public function save(): bool
    {
        $pdo = Database::getConnection();
        try {
            if ($this->id === null) {
                $stmt = $pdo->prepare("
                    INSERT INTO recipes (title, description, ingredients, steps, cook_time, difficulty, category)
                    VALUES (:title, :description, :ingredients, :steps, :cook_time, :difficulty, :category)
                ");
                $result   = $stmt->execute($this->toArray());
                $this->id = (int) $pdo->lastInsertId();
            } else {
                $stmt   = $pdo->prepare("
                    UPDATE recipes
                    SET title=:title, description=:description, ingredients=:ingredients,
                        steps=:steps, cook_time=:cook_time, difficulty=:difficulty, category=:category
                    WHERE id=:id
                ");
                $result = $stmt->execute(array_merge($this->toArray(), [':id' => $this->id]));
            }
            return $result;
        } catch (PDOException $e) {
            throw new DatabaseException('Recipe::save() — ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id");
            return $stmt->execute([':id' => $this->id]);
        } catch (PDOException $e) {
            throw new DatabaseException('Recipe::delete() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function find(int $id): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ? self::fromRow($row) : null;
        } catch (PDOException $e) {
            throw new DatabaseException('Recipe::find() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->query("SELECT * FROM recipes ORDER BY created_at DESC");
            return array_map(self::fromRow(...), $stmt->fetchAll());
        } catch (PDOException $e) {
            throw new DatabaseException('Recipe::all() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function searchByIngredients(string $query): array
    {
        $pdo      = Database::getConnection();
        $keywords = array_filter(array_map(trim(...), explode(',', $query)));
        if ($keywords === []) {
            return self::all();
        }

        try {
            $conditions = [];
            $params     = [];
            foreach ($keywords as $i => $kw) {
                $conditions[] = "ingredients LIKE :kw{$i}";
                $params[":kw{$i}"] = '%' . $kw . '%';
            }
            $sql  = "SELECT * FROM recipes WHERE " . implode(' OR ', $conditions);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return array_map(self::fromRow(...), $stmt->fetchAll());
        } catch (PDOException $e) {
            throw new DatabaseException('Recipe::searchByIngredients() — ' . $e->getMessage(), 0, $e);
        }
    }

    private function toArray(): array
    {
        return [
            ':title'       => $this->title,
            ':description' => $this->description,
            ':ingredients' => $this->ingredients,
            ':steps'       => $this->steps,
            ':cook_time'   => $this->cook_time,
            ':difficulty'  => $this->difficulty,
            ':category'    => $this->category,
        ];
    }

    public static function fromRow(array $row): self
    {
        $r              = new self();
        $r->id          = (int) $row['id'];
        $r->title       = $row['title'];
        $r->description = $row['description'] ?? '';
        $r->ingredients = $row['ingredients'];
        $r->steps       = $row['steps'];
        $r->cook_time   = (int) ($row['cook_time'] ?? 0);
        $r->difficulty  = $row['difficulty'] ?? 'easy';
        $r->category    = $row['category'] ?? 'other';
        return $r;
    }
}
