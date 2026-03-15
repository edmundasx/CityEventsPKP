<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

final class OrganizerPanelRepository
{
    private ?array $eventColumns = null;
    private ?string $ownerColumn = null;

    public function __construct(private PDO $pdo) {}

    public function statsForOrganizer(int $userId): array
    {
        $owner = $this->ownerColumn();
        if ($owner === null) {
            return [
                "total" => 0,
                "approved" => 0,
                "pending" => 0,
                "rejected" => 0,
            ];
        }

        return [
            "total" => $this->countBySql(
                "SELECT COUNT(*) FROM events WHERE {$owner} = :uid",
                $userId,
            ),
            "approved" => $this->countBySql(
                "SELECT COUNT(*) FROM events WHERE {$owner} = :uid AND status = 'approved'",
                $userId,
            ),
            "pending" => $this->countBySql(
                "SELECT COUNT(*) FROM events WHERE {$owner} = :uid AND status = 'pending'",
                $userId,
            ),
            "rejected" => $this->countBySql(
                "SELECT COUNT(*) FROM events WHERE {$owner} = :uid AND status IN ('rejected','declined','rejected_by_admin')",
                $userId,
            ),
        ];
    }

    public function myEvents(int $userId, int $limit = 8): array
    {
        $owner = $this->ownerColumn();
        if ($owner === null) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, title, location, event_date, status
                FROM events
                WHERE {$owner} = :uid
                ORDER BY event_date DESC
                LIMIT :limit
            ");
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function countBySql(string $sql, int $userId): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    private function ownerColumn(): ?string
    {
        if ($this->ownerColumn !== null) {
            return $this->ownerColumn;
        }

        $columns = $this->eventColumns();
        foreach (["organizer_id", "user_id", "created_by"] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $this->ownerColumn = $candidate;
                return $this->ownerColumn;
            }
        }

        return null;
    }

    private function eventColumns(): array
    {
        if ($this->eventColumns !== null) {
            return $this->eventColumns;
        }

        try {
            $rows = $this->pdo->query("SHOW COLUMNS FROM events")->fetchAll(
                PDO::FETCH_ASSOC,
            );
            $this->eventColumns = array_map(
                static fn(array $row): string => (string) ($row["Field"] ?? ""),
                is_array($rows) ? $rows : [],
            );
            return $this->eventColumns;
        } catch (Throwable) {
            $this->eventColumns = [];
            return [];
        }
    }
}
