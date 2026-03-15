<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Auth\Auth;

final class RoleMiddleware
{
    /** @param array<int, string> $roles */
    public function __construct(private array $roles = []) {}

    public function handle(array $params = []): bool
    {
        if (!Auth::check()) {
            header("Location: " . $this->basePath() . "/login");
            return false;
        }

        $role = Auth::role();
        if (!in_array($role, $this->roles, true)) {
            http_response_code(403);
            echo "403 Forbidden";
            return false;
        }

        return true;
    }

    private function basePath(): string
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            return "";
        }
        return $base;
    }
}
