<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use PDO;

final class EventRepository
{
    private ?array $eventColumns = null;

    public function __construct(private PDO $pdo) {}

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
}
