<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class OrganizerWorkspaceRepository
{
    public function __construct(private PDO $pdo) {}

    public function createEvent(int $organizerId, array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO events (
                organizer_id,
                title,
                description,
                category,
                location,
                lat,
                lng,
                event_date,
                price,
                status,
                cover_image,
                created_at,
                updated_at
            )
            VALUES (
                :organizer_id,
                :title,
                :description,
                :category,
                :location,
                :lat,
                :lng,
                :event_date,
                :price,
                'pending',
                :cover_image,
                NOW(),
                NOW()
            )
        ");

        $stmt->bindValue(":organizer_id", $organizerId, PDO::PARAM_INT);
        $stmt->bindValue(":title", (string) ($data["title"] ?? ""), PDO::PARAM_STR);
        $stmt->bindValue(
            ":description",
            (string) ($data["description"] ?? ""),
            PDO::PARAM_STR,
        );
        $stmt->bindValue(
            ":category",
            (string) ($data["category"] ?? ""),
            PDO::PARAM_STR,
        );
        $stmt->bindValue(
            ":location",
            (string) ($data["location"] ?? ""),
            PDO::PARAM_STR,
        );
        if ($data["lat"] === null || $data["lat"] === "") {
            $stmt->bindValue(":lat", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":lat", (float) $data["lat"]);
        }
        if ($data["lng"] === null || $data["lng"] === "") {
            $stmt->bindValue(":lng", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":lng", (float) $data["lng"]);
        }
        $stmt->bindValue(
            ":event_date",
            (string) ($data["event_date"] ?? ""),
            PDO::PARAM_STR,
        );
        $stmt->bindValue(":price", (float) ($data["price"] ?? 0));
        $stmt->bindValue(
            ":cover_image",
            (string) ($data["cover_image"] ?? ""),
            PDO::PARAM_STR,
        );

        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    public function listEventsByOrganizer(int $organizerId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, title, category, location, event_date, price, status, created_at
            FROM events
            WHERE organizer_id = :organizer_id
            ORDER BY event_date DESC
        ");
        $stmt->bindValue(":organizer_id", $organizerId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, email, role, phone
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function updateProfile(int $id, string $name, ?string $phone): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET name = :name,
                phone = :phone,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        if ($phone === null || trim($phone) === "") {
            $stmt->bindValue(":phone", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":phone", $phone, PDO::PARAM_STR);
        }
        $stmt->execute();
    }
}
