<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\UserPanelRepository;

final class UserPanelActionsController
{
    public function toggleFavorite(): void
    {
        $userId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        $eventId = (int) ($_POST["event_id"] ?? 0);
        $returnTo = $this->safeReturn((string) ($_POST["return_to"] ?? ""));
        $favorited = false;
        $ok = false;

        if ($userId > 0 && $eventId > 0) {
            $repo = new UserPanelRepository(Db::pdo());
            $favorited = $repo->toggleFavorite($userId, $eventId);
            $ok = true;
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(200, [
                "ok" => $ok,
                "event_id" => $eventId,
                "favorited" => $favorited,
            ]);
            return;
        }

        header("Location: " . $returnTo);
    }

    public function markNotificationRead(): void
    {
        $userId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        $notificationId = (int) ($_POST["notification_id"] ?? 0);
        $returnTo = $this->safeReturn((string) ($_POST["return_to"] ?? ""));

        if ($userId > 0 && $notificationId > 0) {
            $repo = new UserPanelRepository(Db::pdo());
            $repo->markNotificationRead($userId, $notificationId);
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(200, [
                    "ok" => true,
                    "notification_id" => $notificationId,
                ]);
                return;
            }
        } elseif ($this->isAjaxRequest()) {
            $this->jsonResponse(200, [
                "ok" => false,
                "notification_id" => $notificationId,
            ]);
            return;
        }

        header("Location: " . $returnTo);
    }

    private function safeReturn(string $returnTo): string
    {
        $base = $this->basePath();
        if ($returnTo === "" || !str_starts_with($returnTo, $base . "/")) {
            return $base . "/user/panel";
        }
        return $returnTo;
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
}
