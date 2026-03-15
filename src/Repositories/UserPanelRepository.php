<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

final class UserPanelRepository
{
    public function __construct(private PDO $pdo) {}

    public function favoriteEvents(int $userId, int $limit = 6): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    e.id,
                    e.title,
                    e.category,
                    e.location,
                    e.event_date,
                    e.cover_image,
                    u.name AS organizer_name,
                    f.tag AS favorite_tag
                FROM favorites f
                INNER JOIN events e ON e.id = f.event_id
                LEFT JOIN users u ON u.id = e.organizer_id
                WHERE f.user_id = :uid
                ORDER BY f.created_at DESC
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

    public function recommendedEvents(
        int $userId,
        int $page = 1,
        int $perPage = 18,
    ): array
    {
        try {
            $page = max(1, $page);
            $perPage = max(1, min(30, $perPage));
            $offset = ($page - 1) * $perPage;
            $limitPlusOne = $perPage + 1;

            $stmt = $this->pdo->prepare("
                SELECT
                    e.id,
                    e.title,
                    e.category,
                    e.location,
                    e.event_date,
                    e.cover_image,
                    u.name AS organizer_name,
                    CASE WHEN f.id IS NULL THEN 0 ELSE 1 END AS is_favorite
                FROM events e
                LEFT JOIN users u ON u.id = e.organizer_id
                LEFT JOIN favorites f
                    ON f.event_id = e.id
                    AND f.user_id = :uid
                WHERE e.status = 'approved'
                  AND e.event_date >= NOW()
                ORDER BY e.event_date ASC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":limit", $limitPlusOne, PDO::PARAM_INT);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!is_array($rows)) {
                return ["items" => [], "has_more" => false];
            }

            $hasMore = count($rows) > $perPage;
            if ($hasMore) {
                array_pop($rows);
            }

            return ["items" => $rows, "has_more" => $hasMore];
        } catch (Throwable) {
            return ["items" => [], "has_more" => false];
        }
    }

    public function notifications(int $userId, int $limit = 12): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, message, type, is_read, created_at
                FROM notifications
                WHERE user_id = :uid
                ORDER BY created_at DESC
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

    public function markNotificationRead(int $userId, int $notificationId): void
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE notifications
                SET is_read = 1
                WHERE id = :id AND user_id = :uid
                LIMIT 1
            ");
            $stmt->bindValue(":id", $notificationId, PDO::PARAM_INT);
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Throwable) {
        }
    }

    public function toggleFavorite(int $userId, int $eventId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id
                FROM favorites
                WHERE user_id = :uid AND event_id = :eid
                LIMIT 1
            ");
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($existing) && isset($existing["id"])) {
                $del = $this->pdo->prepare("
                    DELETE FROM favorites
                    WHERE id = :id
                    LIMIT 1
                ");
                $del->bindValue(":id", (int) $existing["id"], PDO::PARAM_INT);
                $del->execute();
                return false;
            }

            $ins = $this->pdo->prepare("
                INSERT INTO favorites (event_id, user_id, tag, created_at)
                VALUES (:eid, :uid, '', NOW())
            ");
            $ins->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $ins->bindValue(":uid", $userId, PDO::PARAM_INT);
            $ins->execute();
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
