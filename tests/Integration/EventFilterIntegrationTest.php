<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Controllers\EventController;
use App\Core\Db;
use App\Repositories\EventRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class EventFilterIntegrationTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Db::setPdo($this->pdo);

        $this->pdo->exec("
            CREATE TABLE events (
                id INTEGER PRIMARY KEY,
                title TEXT,
                location TEXT,
                event_date DATETIME,
                price REAL,
                category TEXT,
                status TEXT,
                cover_image TEXT
            )
        ");

        $this->pdo->exec("
            INSERT INTO events (title, event_date, price, category, status) VALUES
            ('Rock Concert', '2026-05-10 20:00:00', 50.00, 'Music', 'approved'),
            ('Art Exhibition', '2026-06-15 10:00:00', 0.00, 'Art', 'approved'),
            ('Tech Meetup', '2026-07-01 18:00:00', 15.00, 'Technology', 'approved'),
            ('Jazz Night', '2026-05-20 21:00:00', 30.00, 'Music', 'approved')
        ");
    }

    public function testFilterEndpointShowsCorrectEvents(): void
    {
        // Simulate GET parameters for filtering
        $_GET = [
            'category' => 'Music',
            'price_max' => '50'
        ];
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        ob_start();
        $controller = new EventController();
        $controller->filter();
        $output = ob_get_clean();

        // Assert that only the correct events are shown in the output
        $this->assertStringContainsString('Rock Concert', $output);
        $this->assertStringContainsString('Jazz Night', $output);
        $this->assertStringNotContainsString('Art Exhibition', $output);
        $this->assertStringNotContainsString('Tech Meetup', $output);
    }

    public function testFilterReturnsEmptyWhenNoMatch(): void
    {
        $_GET = [
            'category' => 'Sports'
        ];

        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        ob_start();
        $controller = new EventController();
        $controller->filter();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Rock Concert', $output);
        $this->assertStringNotContainsString('Jazz Night', $output);
    }

    public function testFilterHandlesInvalidPrice(): void
    {
        $_GET = [
            'price_max' => 'invalid'
        ];

        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        ob_start();
        $controller = new EventController();
        $controller->filter();
        $output = ob_get_clean();

        $this->assertNotEmpty($output); // sistema nelūžta
    }

}
