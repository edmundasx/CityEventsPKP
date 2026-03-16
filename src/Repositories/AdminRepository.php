<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

final class AdminRepository
{
    public function __construct(private PDO $pdo) {}

    public function stats(): array
    {
        return [
            "total_events" => $this->safeCount("SELECT COUNT(*) FROM events"),
            "pending_events" => $this->safeCount(
                "SELECT COUNT(*) FROM events WHERE status = 'pending'",
            ),
            "approved_events" => $this->safeCount(
                "SELECT COUNT(*) FROM events WHERE status = 'approved'",
            ),
            "rejected_events" => $this->safeCount(
                "SELECT COUNT(*) FROM events WHERE status IN ('rejected','declined','rejected_by_admin')",
            ),
            "total_users" => $this->safeCount("SELECT COUNT(*) FROM users"),
        ];
    }

    public function eventsByTab(string $tab = "pending", int $limit = 30): array
    {
        try {
            // Numatytoji administratoriaus skydelio uzklausa grazina laukiancius patvirtinimo renginius.
            [$whereSql] = $this->tabFilter($tab);
            $stmt = $this->pdo->prepare("
                SELECT
                    e.id,
                    e.title,
                    e.location,
                    e.event_date,
                    e.status,
                    e.updated_at,
                    u.name AS organizer_name
                FROM events
                LEFT JOIN users u ON u.id = e.organizer_id
                WHERE {$whereSql}
                ORDER BY e.updated_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (Throwable) {
            return [];
        }
    }

    public function updateEventStatus(
        int $eventId,
        string $action,
        ?string $rejectionReason = null,
    ): bool {
        $action = strtolower($action);
        if (!in_array($action, ["approve", "reject", "restore"], true)) {
            return false;
        }

        // Patvirtinimo funkcijai leidziame tik numatytus perejimus tarp renginio busenu.
        $currentStatus = $this->eventStatusById($eventId);
        if ($currentStatus === null || !$this->canApplyAction($currentStatus, $action)) {
            return false;
        }

        $status = match ($action) {
            "approve" => "approved",
            "reject" => "rejected",
            default => "pending",
        };

        try {
            $stmt = $this->pdo->prepare("
                UPDATE events
                SET status = :status,
                    rejection_reason = :reason,
                    updated_at = NOW()
                WHERE id = :id
                LIMIT 1
            ");
            $stmt->bindValue(":id", $eventId, PDO::PARAM_INT);
            $stmt->bindValue(":status", $status, PDO::PARAM_STR);
            if ($status === "rejected") {
                $stmt->bindValue(
                    ":reason",
                    $rejectionReason !== null && trim($rejectionReason) !== ""
                        ? trim($rejectionReason)
                        : "Atmesta administratoriaus",
                    PDO::PARAM_STR,
                );
            } else {
                $stmt->bindValue(":reason", null, PDO::PARAM_NULL);
            }
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function latestUsers(int $limit = 10): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, email, role, created_at
                FROM users
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (Throwable) {
            return [];
        }
    }

    public function updateUserRole(int $userId, string $role): bool
    {
        $role = strtolower(trim($role));
        if (!in_array($role, ["user", "organizer", "admin"], true)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE users
                SET role = :role,
                    updated_at = NOW()
                WHERE id = :id
                LIMIT 1
            ");
            $stmt->bindValue(":id", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":role", $role, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function recentActivity(int $limit = 8): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT title, status, updated_at
                FROM events
                ORDER BY updated_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function tabFilter(string $tab): array
    {
        return match (strtolower($tab)) {
            "approved" => ["e.status = 'approved'"],
            "rejected" => ["e.status IN ('rejected','declined','rejected_by_admin')"],
            default => ["e.status = 'pending'"],
        };
    }

    private function eventStatusById(int $eventId): ?string
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT status
                FROM events
                WHERE id = :id
                LIMIT 1
            ");
            $stmt->bindValue(":id", $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $status = $stmt->fetchColumn();

            return is_string($status) ? strtolower($status) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function canApplyAction(string $currentStatus, string $action): bool
    {
        return match ($action) {
            "approve" => $currentStatus === "pending",
            "reject" => in_array($currentStatus, ["pending", "approved"], true),
            "restore" => in_array($currentStatus, ["rejected", "declined", "rejected_by_admin"], true),
            default => false,
        };
    }

    private function safeCount(string $sql): int
    {
        try {
            $value = $this->pdo->query($sql)->fetchColumn();
            return (int) $value;
        } catch (Throwable) {
            return 0;
        }
    }
}
