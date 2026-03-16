<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\AdminRepository;

final class AdminActionsController
{
    public function eventStatus(): void
    {
        $eventId = (int) ($_POST["event_id"] ?? 0);
        $action = (string) ($_POST["action"] ?? "");
        $tab = (string) ($_POST["tab"] ?? "pending");
        $reason = (string) ($_POST["rejection_reason"] ?? "");

        $repo = new AdminRepository(Db::pdo());
        $ok = $eventId > 0 && $repo->updateEventStatus($eventId, $action, $reason);

        $message = $ok
            ? "Event status updated."
            : "Failed to update event status.";

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
        return [
            "tab" => $tab,
            "stats" => $stats,
            "pendingCount" => (int) ($stats["pending_events"] ?? 0),
            "events" => $repo->eventsByTab($tab, 30),
            "users" => $repo->latestUsers(10),
        ];
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
