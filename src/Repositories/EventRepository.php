<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use PDO;

final class EventRepository
{
    private ?array $eventColumns = null;

    public function __construct(private PDO $pdo) {}

    public static function reminderOptions(): array
    {
        return [
            15 => "15 min.",
            30 => "30 min.",
            60 => "1 val.",
            120 => "2 val.",
            360 => "6 val.",
            720 => "12 val.",
            1440 => "1 diena",
            2880 => "2 dienos",
            10080 => "1 savaite",
        ];
    }

    public static function allowedReminderMinutes(): array
    {
        return array_keys(self::reminderOptions());
    }

    public function getCategoryCounts(): array
    {
        $sql = "SELECT category, COUNT(*) as count FROM events WHERE status = 'approved' AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY category ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['category']] = (int)$row['count'];
        }
        return $result;
    }

    public function findReminderForUser(int $userId, int $eventId): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                "
                SELECT id, user_id, event_id, minutes_before, remind_at
                FROM event_reminders
                WHERE user_id = :uid AND event_id = :eid
                LIMIT 1
            "
            );
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return null;
            }

            return [
                "id" => (int) ($row["id"] ?? 0),
                "user_id" => (int) ($row["user_id"] ?? 0),
                "event_id" => (int) ($row["event_id"] ?? 0),
                "minutes_before" => (int) ($row["minutes_before"] ?? 0),
                "remind_at" => (string) ($row["remind_at"] ?? ""),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    public function isEventUpcoming(int $eventId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "
                SELECT event_date
                FROM events
                WHERE id = :eid
                LIMIT 1
            "
            );
            $stmt->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row) || empty($row["event_date"])) {
                return false;
            }

            $eventAt = new DateTimeImmutable((string) $row["event_date"]);
            $now = new DateTimeImmutable("now");

            return $eventAt >= $now;
        } catch (\Throwable) {
            return false;
        }
    }

    public function saveReminder(int $userId, int $eventId, int $minutesBefore): bool
    {
        if (!in_array($minutesBefore, self::allowedReminderMinutes(), true)) {
            return false;
        }

        try {
            $eventStmt = $this->pdo->prepare(
                "
                SELECT event_date
                FROM events
                WHERE id = :eid
                LIMIT 1
            "
            );
            $eventStmt->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $eventStmt->execute();
            $eventRow = $eventStmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($eventRow) || empty($eventRow["event_date"])) {
                return false;
            }

            $eventAt = new DateTimeImmutable((string) $eventRow["event_date"]);
            $remindAt = $eventAt
                ->modify("-{$minutesBefore} minutes")
                ->format("Y-m-d H:i:s");
            $now = (new DateTimeImmutable("now"))->format("Y-m-d H:i:s");

            $existingStmt = $this->pdo->prepare(
                "
                SELECT id
                FROM event_reminders
                WHERE user_id = :uid AND event_id = :eid
                LIMIT 1
            "
            );
            $existingStmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $existingStmt->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $existingStmt->execute();
            $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($existing) && isset($existing["id"])) {
                $update = $this->pdo->prepare(
                    "
                    UPDATE event_reminders
                    SET minutes_before = :minutes_before,
                        remind_at = :remind_at,
                        updated_at = :updated_at
                    WHERE id = :id
                "
                );
                $update->bindValue(":minutes_before", $minutesBefore, PDO::PARAM_INT);
                $update->bindValue(":remind_at", $remindAt);
                $update->bindValue(":updated_at", $now);
                $update->bindValue(":id", (int) $existing["id"], PDO::PARAM_INT);

                return $update->execute();
            }

            $insert = $this->pdo->prepare(
                "
                INSERT INTO event_reminders (
                    user_id,
                    event_id,
                    minutes_before,
                    remind_at,
                    created_at,
                    updated_at
                )
                VALUES (
                    :uid,
                    :eid,
                    :minutes_before,
                    :remind_at,
                    :created_at,
                    :updated_at
                )
            "
            );
            $insert->bindValue(":uid", $userId, PDO::PARAM_INT);
            $insert->bindValue(":eid", $eventId, PDO::PARAM_INT);
            $insert->bindValue(":minutes_before", $minutesBefore, PDO::PARAM_INT);
            $insert->bindValue(":remind_at", $remindAt);
            $insert->bindValue(":created_at", $now);
            $insert->bindValue(":updated_at", $now);

            return $insert->execute();
        } catch (\Throwable) {
            return false;
        }
    }

    public function deleteReminder(int $userId, int $eventId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "
                DELETE FROM event_reminders
                WHERE user_id = :uid AND event_id = :eid
            "
            );
            $stmt->bindValue(":uid", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":eid", $eventId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Throwable) {
            return false;
        }
    }

    public function homepageEvents(
        ?int $limit = 12,
        bool $onlyFuture = true,
        int $lookbackHours = 0,
    ): array {
        $futureClause = $onlyFuture
            ? "AND e.event_date >= DATE_SUB(NOW(), INTERVAL :lookback_hours HOUR)"
            : "";
        $limitClause = $limit !== null ? "LIMIT :limit" : "";
        $columns = $this->eventColumns();

        $sql = "
            SELECT
                e.id,
                e.title,
                e.location,
                e.event_date,
                e.price,
                e.cover_image,
                u.name AS organizer_name,
                " .
            $this->selectColumn($columns, "category") .
            ",
                " .
            $this->selectColumn($columns, "district") .
            ",
                " .
            $this->selectColumn($columns, "tags") .
            "
            FROM events e
            LEFT JOIN users u ON u.id = e.organizer_id
            WHERE e.status = 'approved'
            {$futureClause}
            ORDER BY e.event_date ASC
            {$limitClause}
        ";

        $stmt = $this->pdo->prepare($sql);
        if ($onlyFuture) {
            $stmt->bindValue(
                ":lookback_hours",
                max(0, $lookbackHours),
                PDO::PARAM_INT,
            );
        }
        if ($limit !== null) {
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $r): array {
            $dt = new DateTimeImmutable($r["event_date"]);

            $price = (float) $r["price"];
            $priceLabel =
                $price <= 0.0
                    ? "Nemokamai"
                    : "€" . number_format($price, 2, ".", "");

            return [
                "id" => (int) $r["id"],
                "title" => (string) $r["title"],
                "date" => $dt->format("Y-m-d"),
                "time" => $dt->format("H:i"),
                "location" => (string) $r["location"],
                "price" => $priceLabel,
                "image" => (string) ($r["cover_image"] ?? ""),
                "organizer_name" => (string) ($r["organizer_name"] ?? ""),
                "category" => (string) ($r["category"] ?? ""),
                "district" => (string) ($r["district"] ?? ""),
                "tags" => (string) ($r["tags"] ?? ""),
            ];
        }, $rows);
    }

    public function mapEvents(
        bool $onlyFuture = true,
        int $lookbackHours = 0,
    ): array
    {
        $futureClause = $onlyFuture
            ? "AND e.event_date >= DATE_SUB(NOW(), INTERVAL :lookback_hours HOUR)"
            : "";
        $columns = $this->eventColumns();

        $sql =
            "
            SELECT
                e.id,
                e.title,
                e.location,
                e.event_date,
                e.price,
                u.name AS organizer_name,
                " .
            $this->selectColumn($columns, "category") .
            ",
                " .
            $this->selectColumn($columns, "district") .
            ",
                " .
            $this->selectColumn($columns, "tags") .
            ",
                " .
            $this->selectColumn($columns, "lat") .
            ",
                " .
            $this->selectColumn($columns, "lng") .
            ",
                e.cover_image
            FROM events e
            LEFT JOIN users u ON u.id = e.organizer_id
            WHERE e.status = 'approved'
            {$futureClause}
            ORDER BY e.event_date ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        if ($onlyFuture) {
            $stmt->bindValue(
                ":lookback_hours",
                max(0, $lookbackHours),
                PDO::PARAM_INT,
            );
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $r): array {
            $dt = new DateTimeImmutable($r["event_date"]);
            $price = (float) $r["price"];

            $lat = $r["lat"];
            $lng = $r["lng"];

            return [
                "id" => (int) $r["id"],
                "title" => (string) $r["title"],
                "location" => (string) $r["location"],
                "event_date" => $dt->format("Y-m-d"),
                "event_time" => $dt->format("H:i"),
                "price_eur" => $price,
                "is_free" => $price <= 0.0,
                "category" => (string) ($r["category"] ?? ""),
                "district" => (string) ($r["district"] ?? ""),
                "organizer_name" => (string) ($r["organizer_name"] ?? ""),
                "tags" => (string) ($r["tags"] ?? ""),
                "lat" => $lat === null ? null : (float) $lat,
                "lng" => $lng === null ? null : (float) $lng,
                "cover_image" => (string) ($r["cover_image"] ?? ""),
            ];
        }, $rows);
    }

    public function findById(int $id): ?array
    {
        $columns = $this->eventColumns();

        $sql =
            "
            SELECT
                e.id,
                e.title,
                e.location,
                e.event_date,
                e.price,
                " .
            $this->selectColumn($columns, "description") .
            ",
                " .
            $this->selectColumn($columns, "category") .
            ",
                " .
            $this->selectColumn($columns, "district") .
            ",
                " .
            $this->selectColumn($columns, "lat") .
            ",
                " .
            $this->selectColumn($columns, "lng") .
            ",
                e.cover_image
            FROM events e
            WHERE e.status = 'approved'
              AND e.id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $dt = new DateTimeImmutable($row["event_date"]);
        $price = (float) $row["price"];
        $priceLabel =
            $price <= 0.0
                ? "Nemokamai"
                : "€" . number_format($price, 2, ".", "");

        $lat = $row["lat"] ?? null;
        $lng = $row["lng"] ?? null;

        return [
            "id" => (int) $row["id"],
            "title" => (string) $row["title"],
            "description" => (string) ($row["description"] ?? ""),
            "location" => (string) $row["location"],
            "date" => $dt->format("Y-m-d"),
            "time" => $dt->format("H:i"),
            "price" => $priceLabel,
            "price_raw" => $price,
            "category" => (string) ($row["category"] ?? ""),
            "district" => (string) ($row["district"] ?? ""),
            "lat" => $lat === null ? null : (float) $lat,
            "lng" => $lng === null ? null : (float) $lng,
            "image" => (string) ($row["cover_image"] ?? ""),
        ];
    }

    private function eventColumns(): array
    {
        if ($this->eventColumns !== null) {
            return $this->eventColumns;
        }

        $stmt = $this->pdo->query("SHOW COLUMNS FROM events");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->eventColumns = array_map(
            static fn(array $row): string => (string) $row["Field"],
            $rows,
        );

        return $this->eventColumns;
    }

    private function selectColumn(array $columns, string $name): string
    {
        if (in_array($name, $columns, true)) {
            return "e.{$name}";
        }

        return "NULL AS {$name}";
    }

    public function filterEvents(array $filters = []): array
    {
        $where = ["e.status = 'approved'"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "e.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "e.event_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "e.event_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (isset($filters['price_min'])) {
            $where[] = "e.price >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }
        if (isset($filters['price_max'])) {
            $where[] = "e.price <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "SELECT e.id, e.title, e.location, e.event_date, e.price, e.category, e.cover_image FROM events e $whereSql ORDER BY e.event_date ASC";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $r): array {
            $dt = new DateTimeImmutable($r["event_date"]);
            $price = (float) $r["price"];
            $priceLabel = $price <= 0.0 ? "Nemokamai" : "€" . number_format($price, 2, ".", "");
            return [
                "id" => (int) $r["id"],
                "title" => (string) $r["title"],
                "date" => $dt->format("Y-m-d"),
                "time" => $dt->format("H:i"),
                "location" => (string) $r["location"],
                "price" => $priceLabel,
                "category" => (string) ($r["category"] ?? ""),
                "image" => (string) ($r["cover_image"] ?? ""),
            ];
        }, $rows);
    }

    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM events WHERE status = 'approved' AND category IS NOT NULL AND category != '' ORDER BY category ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $row['category'], $rows);
    }

    public function getPriceRange(): array
    {
        $sql = "SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM events WHERE status = 'approved'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'min' => (float)($row['min_price'] ?? 0),
            'max' => (float)($row['max_price'] ?? 0),
        ];
    }
}
