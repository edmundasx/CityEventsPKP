<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\EventRepository;

final class EventReminderController
{
    public function save(string $id): void
    {
        $eventId = (int) $id;
        $userId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        $minutesBefore = (int) ($_POST["minutes_before"] ?? 0);
        $returnTo = $this->safeReturn(
            (string) ($_POST["return_to"] ?? ""),
            $eventId,
        );

        if ($userId <= 0 || $eventId <= 0) {
            $this->respond(false, $eventId, $minutesBefore, $returnTo, "Neteisinga uzklausa.");
            return;
        }

        if (!in_array($minutesBefore, EventRepository::allowedReminderMinutes(), true)) {
            $this->respond(false, $eventId, $minutesBefore, $returnTo, "Pasirinktas laikas negalimas.");
            return;
        }

        $repo = new EventRepository(Db::pdo());
        if (!$repo->isEventUpcoming($eventId)) {
            $this->respond(false, $eventId, $minutesBefore, $returnTo, "Renginys jau pasibaige.");
            return;
        }

        $ok = $repo->saveReminder($userId, $eventId, $minutesBefore);
        $this->respond(
            $ok,
            $eventId,
            $minutesBefore,
            $returnTo,
            $ok ? "" : "Nepavyko issaugoti priminimo.",
        );
    }

    public function delete(string $id): void
    {
        $eventId = (int) $id;
        $userId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        $returnTo = $this->safeReturn(
            (string) ($_POST["return_to"] ?? ""),
            $eventId,
        );

        if ($userId <= 0 || $eventId <= 0) {
            $this->respond(false, $eventId, null, $returnTo, "Neteisinga uzklausa.");
            return;
        }

        $repo = new EventRepository(Db::pdo());
        if (!$repo->isEventUpcoming($eventId)) {
            $this->respond(false, $eventId, null, $returnTo, "Renginys jau pasibaige.");
            return;
        }

        $ok = $repo->deleteReminder($userId, $eventId);
        $this->respond($ok, $eventId, null, $returnTo, $ok ? "" : "Nepavyko istrinti priminimo.");
    }

    private function respond(
        bool $ok,
        int $eventId,
        ?int $minutesBefore,
        string $returnTo,
        string $message,
    ): void {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(200, [
                "ok" => $ok,
                "event_id" => $eventId,
                "minutes_before" => $minutesBefore,
                "message" => $message,
                "redirect" => $returnTo,
            ]);
            return;
        }

        header("Location: " . $returnTo);
    }

    private function safeReturn(string $returnTo, int $eventId): string
    {
        $base = $this->basePath();
        $fallback = $base . "/events/" . $eventId;

        if ($returnTo === "" || !str_starts_with($returnTo, $base . "/")) {
            return $fallback;
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
