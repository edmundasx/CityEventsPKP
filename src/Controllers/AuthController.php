<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\UserRepository;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header("Location: " . $this->basePath() . "/");
            return;
        }

        $title = "Login";
        $view = __DIR__ . "/../Views/pages/login.php";
        $loginError = $_SESSION["login_error"] ?? null;
        unset($_SESSION["login_error"]);

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function showSignup(): void
    {
        if (Auth::check()) {
            header("Location: " . $this->basePath() . "/");
            return;
        }

        $title = "Registration";
        $view = __DIR__ . "/../Views/pages/signup.php";
        $registerError = $_SESSION["register_error"] ?? null;
        unset($_SESSION["register_error"]);

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function login(): void
    {
        $email = trim((string) ($_POST["email"] ?? ""));
        $password = (string) ($_POST["password"] ?? "");

        if ($email === "" || $password === "") {
            $message = "Enter email and password.";
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(422, [
                    "ok" => false,
                    "message" => $message,
                ]);
                return;
            }
            $_SESSION["login_error"] = $message;
            header("Location: " . $this->basePath() . "/login");
            return;
        }

        if (!Auth::attempt($email, $password)) {
            $message = "Invalid login credentials.";
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(401, [
                    "ok" => false,
                    "message" => $message,
                ]);
                return;
            }
            $_SESSION["login_error"] = $message;
            header("Location: " . $this->basePath() . "/login");
            return;
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(200, [
                "ok" => true,
                "redirect" => $this->basePath() . "/",
            ]);
            return;
        }

        header("Location: " . $this->basePath() . "/");
    }

    public function register(): void
    {
        $name = trim((string) ($_POST["name"] ?? ""));
        $email = trim((string) ($_POST["email"] ?? ""));
        $password = (string) ($_POST["password"] ?? "");
        $confirmPassword = (string) ($_POST["confirm_password"] ?? "");
        $role = strtolower(trim((string) ($_POST["role"] ?? "user")));

        $error = $this->validateRegistrationInput(
            $name,
            $email,
            $password,
            $confirmPassword,
            $role,
        );
        if ($error !== null) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(422, [
                    "ok" => false,
                    "message" => $error,
                ]);
                return;
            }
            $_SESSION["register_error"] = $error;
            header("Location: " . $this->basePath() . "/signup");
            return;
        }

        $repo = new UserRepository(Db::pdo());
        if ($repo->findByEmail($email) !== null) {
            $message = "Toks el. pastas jau naudojamas.";
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(409, [
                    "ok" => false,
                    "message" => $message,
                ]);
                return;
            }
            $_SESSION["register_error"] = $message;
            header("Location: " . $this->basePath() . "/signup");
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = $repo->create($name, $email, $passwordHash, $role);
        if ($user === null) {
            $message = "Registration failed. Please try again.";
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(500, [
                    "ok" => false,
                    "message" => $message,
                ]);
                return;
            }
            $_SESSION["register_error"] = $message;
            header("Location: " . $this->basePath() . "/signup");
            return;
        }

        Auth::login($user);
        $redirect = $this->redirectForRole($role);

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(201, [
                "ok" => true,
                "redirect" => $redirect,
            ]);
            return;
        }

        header("Location: " . $redirect);
    }

    public function logout(): void
    {
        Auth::logout();
        header("Location: " . $this->basePath() . "/");
    }

    private function basePath(): string
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            return "";
        }
        return $base;
    }

    private function isAjaxRequest(): bool
    {
        $xhrHeader = (string) ($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "");
        $accept = (string) ($_SERVER["HTTP_ACCEPT"] ?? "");

        return strtolower($xhrHeader) === "xmlhttprequest" ||
            str_contains(strtolower($accept), "application/json");
    }

    private function jsonResponse(int $status, array $payload): void
    {
        http_response_code($status);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function validateRegistrationInput(
        string $name,
        string $email,
        string $password,
        string $confirmPassword,
        string $role,
    ): ?string
    {
        if ($name === "" || $email === "" || $password === "" || $confirmPassword === "") {
            return "Fill in all fields.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Netinkamas el. pasto formatas.";
        }

        if (strlen($password) < 6) {
            return "Password turi buti bent 6 simboliu.";
        }

        if ($password !== $confirmPassword) {
            return "Passwords do not match.";
        }

        if (!in_array($role, ["user", "organizer"], true)) {
            return "Pasirinkite paskyros tipa.";
        }

        return null;
    }

    private function redirectForRole(string $role): string
    {
        if ($role === "organizer") {
            return $this->basePath() . "/organizer/panel";
        }
        return $this->basePath() . "/user/panel";
    }
}
