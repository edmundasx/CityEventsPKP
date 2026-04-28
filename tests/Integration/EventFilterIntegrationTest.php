<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Controllers\EventController;
use App\Core\Db;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
final class EventFilterIntegrationTest extends TestCase
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
            $this->markTestSkipped("MySQL nepasiekiamas arba DB nesukonfigūruota.");
        }
    }

    private function getOrganizerId(PDO $pdo): int
    {
        $row = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            return (int) $row["id"];
        }

        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Test Org', 'org@test.com', 'pass', 'organizer')");
        return (int) $pdo->lastInsertId();
    }

    public function testFilterEndpointShowsCorrectEvents(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $orgId = $this->getOrganizerId($pdo);

            $stmt = $pdo->prepare(
                "INSERT INTO events (
                    organizer_id, title, description, category, location,
                    lat, lng, event_date, price, status, cover_image, created_at, updated_at
                ) VALUES 
                (?, 'PKP Rock Concert', '', 'Music', 'Vilnius', NULL, NULL, '2026-05-10 20:00:00', 50.00, 'approved', '', NOW(), NOW()),
                (?, 'PKP Art Exhibition', '', 'Art', 'Vilnius', NULL, NULL, '2026-06-15 10:00:00', 0.00, 'approved', '', NOW(), NOW()),
                (?, 'PKP Tech Meetup', '', 'Technology', 'Vilnius', NULL, NULL, '2026-07-01 18:00:00', 15.00, 'approved', '', NOW(), NOW()),
                (?, 'PKP Jazz Night', '', 'Music', 'Vilnius', NULL, NULL, '2026-05-20 21:00:00', 30.00, 'approved', '', NOW(), NOW())"
            );
            $stmt->execute([$orgId, $orgId, $orgId, $orgId]);

            $_GET = [
                'category' => 'Music',
                'price_max' => '50'
            ];
            $_SERVER['SCRIPT_NAME'] = '/public/index.php';

            ob_start();
            $controller = new EventController();
            $controller->filter();
            $output = ob_get_clean();

            $this->assertStringContainsString('PKP Rock Concert', (string)$output);
            $this->assertStringContainsString('PKP Jazz Night', (string)$output);
            $this->assertStringNotContainsString('PKP Art Exhibition', (string)$output);
            $this->assertStringNotContainsString('PKP Tech Meetup', (string)$output);
        } finally {
            $pdo->rollBack();
        }
    }

    public function testFilterReturnsEmptyWhenNoMatch(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $orgId = $this->getOrganizerId($pdo);

            $stmt = $pdo->prepare(
                "INSERT INTO events (
                    organizer_id, title, description, category, location,
                    lat, lng, event_date, price, status, cover_image, created_at, updated_at
                ) VALUES 
                (?, 'PKP Rock Concert', '', 'Music', 'Vilnius', NULL, NULL, '2026-05-10 20:00:00', 50.00, 'approved', '', NOW(), NOW()),
                (?, 'PKP Jazz Night', '', 'Music', 'Vilnius', NULL, NULL, '2026-05-20 21:00:00', 30.00, 'approved', '', NOW(), NOW())"
            );
            $stmt->execute([$orgId, $orgId]);

            $_GET = [
                'category' => 'Sports'
            ];
            $_SERVER['SCRIPT_NAME'] = '/public/index.php';

            ob_start();
            $controller = new EventController();
            $controller->filter();
            $output = ob_get_clean();

            $this->assertStringNotContainsString('PKP Rock Concert', (string)$output);
            $this->assertStringNotContainsString('PKP Jazz Night', (string)$output);
        } finally {
            $pdo->rollBack();
        }
    }

    public function testFilterHandlesInvalidPrice(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $_GET = [
                'price_max' => 'invalid'
            ];
            $_SERVER['SCRIPT_NAME'] = '/public/index.php';

            ob_start();
            $controller = new EventController();
            $controller->filter();
            $output = ob_get_clean();

            $this->assertNotEmpty($output);
        } finally {
            $pdo->rollBack();
        }
    }
}
