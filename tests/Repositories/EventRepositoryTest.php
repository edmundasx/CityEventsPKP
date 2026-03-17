<?php
declare(strict_types=1);

namespace Tests\Repositories;

use App\Repositories\EventRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class EventRepositoryTest extends TestCase
{
    private PDO $pdo;
    private EventRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE events (
                id INTEGER PRIMARY KEY,
                title TEXT,
                location TEXT,
                event_date DATETIME,
                price REAL,
                category TEXT,
                district TEXT,
                lat REAL,
                lng REAL,
                cover_image TEXT,
                status TEXT
            )
        ");

        $this->repository = new EventRepository($this->pdo);

        // Seed some data
        $this->pdo->exec("
            INSERT INTO events (title, event_date, price, category, status) VALUES
            ('Rock Concert', '2026-05-10 20:00:00', 50.00, 'Music', 'approved'),
            ('Art Exhibition', '2026-06-15 10:00:00', 0.00, 'Art', 'approved'),
            ('Tech Meetup', '2026-07-01 18:00:00', 15.00, 'Technology', 'approved'),
            ('Pending Event', '2026-08-01 10:00:00', 10.00, 'Music', 'pending')
        ");
    }

    public function testFilterByCategory(): void
    {
        $filters = ['category' => 'Music'];
        $events = $this->repository->filterEvents($filters);

        $this->assertCount(1, $events);
        $this->assertSame('Rock Concert', $events[0]['title']);
    }

    public function testFilterByDateRange(): void
    {
        $filters = [
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30'
        ];
        $events = $this->repository->filterEvents($filters);

        $this->assertCount(1, $events);
        $this->assertSame('Art Exhibition', $events[0]['title']);
    }

    public function testFilterByMaxPrice(): void
    {
        $filters = ['price_max' => 20.00];
        $events = $this->repository->filterEvents($filters);

        // Should return Art Exhibition (0) and Tech Meetup (15)
        $this->assertCount(2, $events);
        $titles = array_column($events, 'title');
        $this->assertContains('Art Exhibition', $titles);
        $this->assertContains('Tech Meetup', $titles);
    }

    public function testCombinedFilters(): void
    {
        $filters = [
            'category' => 'Technology',
            'price_max' => 20.00,
            'date_from' => '2026-01-01'
        ];
        $events = $this->repository->filterEvents($filters);

        $this->assertCount(1, $events);
        $this->assertSame('Tech Meetup', $events[0]['title']);
    }

    public function testOnlyApprovedEventsAreReturned(): void
    {
        $filters = []; // Empty filters should return all approved
        $events = $this->repository->filterEvents($filters);

        $this->assertCount(3, $events); // 3 approved, 1 pending
    }
}
