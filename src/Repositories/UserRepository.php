<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT id, name, email, password, role
            FROM users
            WHERE email = :email
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function create(string $name, string $email, string $passwordHash, string $role): ?array
    {
        $sql = "
            INSERT INTO users (name, email, password, role)
            VALUES (:name, :email, :password, :role)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $passwordHash, PDO::PARAM_STR);
        $stmt->bindValue(":role", $role, PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException) {
            return null;
        }

        $id = (int) $this->pdo->lastInsertId();
        if ($id <= 0) {
            return null;
        }

        return [
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "role" => $role,
        ];
    }
}
