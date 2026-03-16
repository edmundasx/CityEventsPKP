<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\AdminRepository;
use App\Support\AdminEventModerationRules;

final class AdminActionsController
{
    public function eventStatus(): void
    {
        $eventId = (int) ($_POST["event_id"] ?? 0);
        $action = strtolower((string) ($_POST["action"] ?? ""));
        $tab = (string) ($_POST["tab"] ?? "pending");
        $reason = trim((string) ($_POST["rejection_reason"] ?? ""));

        if (AdminEventModerationRules::requiresReason($action) && $reason === "") {
            $this->respondWithEventStatusResult(
                false,
                "Būtina nurodyti atmetimo priežastį.",
                $tab,
            );
            return;
        }

        $repo = new AdminRepository(Db::pdo());
        // Atmetant rengini priezastis yra privaloma ir perduodama i saugojimo logika.
        $ok = $eventId > 0 && $repo->updateEventStatus($eventId, $action, $reason);
        $this->respondWithEventStatusResult($ok, $this->eventStatusMessage($action, $ok), $tab);
    }

    public function userRole(): void
    {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $role = (string) ($_POST["role"] ?? "");
        $currentUserId = (int) ((Auth::user() ?? [])["id"] ?? 0);

        $ok = false;
        if ($userId > 0 && $userId !== $currentUserId) {
            $repo = new AdminRepository(Db::pdo());
            $ok = $repo->updateUserRole($userId, $role);
        }

        $message = $ok
            ? "User role updated."
            : "Failed to update role.";

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(200, [
                "ok" => $ok,
                "message" => $message,
                "data" => [
                    "users" => (new AdminRepository(Db::pdo()))->latestUsers(10),
                ],
            ]);
            return;
        }

        $_SESSION["admin_flash"] = $message;
        header("Location: " . $this->basePath() . "/admin/panel?tab=pending");
    }

    public function panelData(): void
    {
        $tab = (string) ($_GET["tab"] ?? "pending");
        $this->jsonResponse(200, [
            "ok" => true,
            "data" => $this->buildPanelData($tab),
        ]);
    }

    private function basePath(): string
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            return "";
        }
        return $base;
    }

    private function buildPanelData(string $tab): array
    {
        $repo = new AdminRepository(Db::pdo());
        $stats = $repo->stats();

        // Sis endpointas grizta su admin puslapio sekciju duomenimis AJAX atnaujinimui.
        return [
            "tab" => $tab,
            "stats" => $stats,
            "pendingCount" => (int) ($stats["pending_events"] ?? 0),
            "events" => $repo->eventsByTab($tab, 30),
            "users" => $repo->latestUsers(10),
        ];
    }

    private function respondWithEventStatusResult(bool $ok, string $message, string $tab): void
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(200, [
                "ok" => $ok,
                "message" => $message,
                "data" => $this->buildPanelData($tab),
            ]);
            return;
        }

        $_SESSION["admin_flash"] = $message;
        header("Location: " . $this->basePath() . "/admin/panel?tab=" . rawurlencode($tab));
    }

    private function eventStatusMessage(string $action, bool $ok): string
    {
        $action = strtolower($action);

        if ($ok) {
            return match ($action) {
                "approve" => "Renginys patvirtintas.",
                "reject" => "Renginys atmestas.",
                "restore" => "Renginys grazintas i laukimo busena.",
                default => "Renginio busena atnaujinta.",
            };
        }

        return match ($action) {
            "approve" => "Nepavyko patvirtinti renginio.",
            "reject" => "Nepavyko atmesti renginio.",
            "restore" => "Nepavyko grazinti renginio i laukimo busena.",
            default => "Nepavyko atnaujinti renginio busenos.",
        };
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
}
