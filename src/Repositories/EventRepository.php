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
        int $limit = 12,
        bool $onlyFuture = true,
    ): array {
        $futureClause = $onlyFuture ? "AND e.event_date >= NOW()" : "";

        $sql = "
            SELECT e.id, e.title, e.location, e.event_date, e.price, e.cover_image
            FROM events e
            WHERE e.status = 'approved'
            {$futureClause}
            ORDER BY e.event_date ASC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
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
            ];
        }, $rows);
    }

    public function mapEvents(bool $onlyFuture = true): array
    {
        $futureClause = $onlyFuture ? "AND e.event_date >= NOW()" : "";
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
            {$futureClause}
            ORDER BY e.event_date ASC
        ";

        $stmt = $this->pdo->prepare($sql);
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

    public function filterEvents(array $filters = []): array
    {
        $where = ["e.status = 'approved'"];
        $params = [];

        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "e.category = :category";
            $params[':category'] = $filters['category'];
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $where[] = "e.event_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "e.event_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Price range filter
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