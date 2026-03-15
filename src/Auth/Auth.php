<?php
declare(strict_types=1);

namespace App\Auth;

use App\Core\Db;
use App\Repositories\UserRepository;

final class Auth
{
    private const SESSION_KEY = "auth_user";

    public static function user(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user["role"] ?? null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = [
            "id" => (int) ($user["id"] ?? 0),
            "name" => (string) ($user["name"] ?? ""),
            "email" => (string) ($user["email"] ?? ""),
            "role" => (string) ($user["role"] ?? "user"),
        ];
    }

    public static function attempt(string $email, string $password): bool
    {
        $repo = new UserRepository(Db::pdo());
        $user = $repo->findByEmail($email);
        if ($user === null) {
            return false;
        }

        if (!password_verify($password, (string) ($user["password"] ?? ""))) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }
}
