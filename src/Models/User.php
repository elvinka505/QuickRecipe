<?php

namespace App\Models;

use App\Core\Database;
use App\Exceptions\DatabaseException;
use PDOException;

class User
{
    public ?int $id       = null;
    public string $name     = '';
    public string $email    = '';
    public string $password = '';

    public function save(): bool
    {
        $pdo = Database::getConnection();
        try {
            if ($this->id === null) {
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password)
                    VALUES (:name, :email, :password)
                ");
                $result   = $stmt->execute([
                    ':name'     => $this->name,
                    ':email'    => $this->email,
                    ':password' => $this->password,
                ]);
                $this->id = (int) $pdo->lastInsertId();
            } else {
                $stmt   = $pdo->prepare("
                    UPDATE users SET name=:name, email=:email, password=:password WHERE id=:id
                ");
                $result = $stmt->execute([
                    ':name'     => $this->name,
                    ':email'    => $this->email,
                    ':password' => $this->password,
                    ':id'       => $this->id,
                ]);
            }
            return $result;
        } catch (PDOException $e) {
            throw new DatabaseException('User::save() — ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute([':id' => $this->id]);
        } catch (PDOException $e) {
            throw new DatabaseException('User::delete() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function find(int $id): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ? self::fromRow($row) : null;
        } catch (PDOException $e) {
            throw new DatabaseException('User::find() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function findByEmail(string $email): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch();
            return $row ? self::fromRow($row) : null;
        } catch (PDOException $e) {
            throw new DatabaseException('User::findByEmail() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
            return array_map(self::fromRow(...), $stmt->fetchAll());
        } catch (PDOException $e) {
            throw new DatabaseException('User::all() — ' . $e->getMessage(), 0, $e);
        }
    }

    public static function fromRow(array $row): self
    {
        $u           = new self();
        $u->id       = (int) $row['id'];
        $u->name     = $row['name'];
        $u->email    = $row['email'];
        $u->password = $row['password'];
        return $u;
    }
}
