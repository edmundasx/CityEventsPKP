<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Db;
use App\Repositories\EventRepository;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * Integraciniai testai tarp duomenų prieigos sluoksnio (EventRepository) ir MySQL.
 *
 * @group integration
 */
final class EventRepositoryFindByIdIntegrationTest extends TestCase
{
    private static bool $dbOk = false;

    public static function setUpBeforeClass(): void
    {
        try {
            $pdo = Db::pdo();
            $pdo->query("SELECT 1");
            self::$dbOk = true;
        } catch (PDOException) {
            self::$dbOk = false;
        }
    }

    protected function setUp(): void
    {
        if (!self::$dbOk) {
            $this->markTestSkipped(
                "MySQL nepasiekiamas arba DB nesukonfigūruota (žr. src/Core/Db.php).",
            );
        }
    }

    /**
     * Funkcinis: patvirtintas renginys pagal egzistuojantį ID grąžinamas ir sumapinamas.
     */
    public function testFindByIdReturnsMappedRowForApprovedEventInDatabase(): void
    {
        $pdo = Db::pdo();
        $repo = new EventRepository($pdo);

        $existingId = $this->firstApprovedEventId($pdo);
        if ($existingId !== null) {
            $event = $repo->findById($existingId);
            $this->assertNotNull($event);
            $this->assertSame($existingId, $event["id"]);
            $this->assertNotSame("", trim((string) ($event["title"] ?? "")));
            return;
        }

        $organizerId = $this->firstOrganizerId($pdo);
        if ($organizerId === null) {
            $this->markTestSkipped(
                "Nėra patvirtintų renginių ir nėra users įrašo — negalima sukurti testinio įrašo.",
            );
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO events (
                    organizer_id, title, description, category, location,
                    lat, lng, event_date, price, status, cover_image, created_at, updated_at
                ) VALUES (
                    :organizer_id, :title, :description, :category, :location,
                    NULL, NULL, :event_date, :price, 'approved', '', NOW(), NOW()
                )",
            );
            $stmt->execute([
                ":organizer_id" => $organizerId,
                ":title" => "PKP integracinis testas",
                ":description" => "Laikinas įrašas transakcijoje.",
                ":category" => "Test",
                ":location" => "Vilnius",
                ":event_date" => "2026-06-15 19:00:00",
                ":price" => 0.0,
            ]);
            $newId = (int) $pdo->lastInsertId();
            $this->assertGreaterThan(0, $newId);

            $event = $repo->findById($newId);

            $this->assertNotNull($event);
            $this->assertSame($newId, $event["id"]);
            $this->assertSame("PKP integracinis testas", $event["title"]);
            $this->assertSame("Vilnius", $event["location"]);
            $this->assertSame("Nemokamai", $event["price"]);
        } finally {
            $pdo->rollBack();
        }
    }

    /**
     * Funkcinis: neegzistuojantis ID grąžina null (nerodomas „patvirtintas“ renginys).
     */
    public function testFindByIdReturnsNullForNonExistentEventId(): void
    {
        $pdo = Db::pdo();
        $repo = new EventRepository($pdo);

        $bogusId = 2147483640;
        $event = $repo->findById($bogusId);

        $this->assertNull($event);
    }

    /**
     * Nefunkcinis: užklausa užbaigiama per priimtiną laiką (našumo / neužstringimo tikrinimas).
     */
    public function testFindByIdForNonExistentIdCompletesWithinReasonableTime(): void
    {
        $pdo = Db::pdo();
        $repo = new EventRepository($pdo);

        $start = microtime(true);
        $repo->findById(2147483639);
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            2.0,
            $elapsed,
            "findById turėtų užbaigtis greičiau nei per 2 s (lokalioje DB).",
        );
    }

    private function firstOrganizerId(PDO $pdo): ?int
    {
        $row = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")
            ->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return (int) $row["id"];
    }

    private function firstApprovedEventId(PDO $pdo): ?int
    {
        $row = $pdo
            ->query(
                "SELECT id FROM events WHERE status = 'approved' ORDER BY id ASC LIMIT 1",
            )
            ->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return (int) $row["id"];
    }
}
