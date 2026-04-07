<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Db;
use App\Repositories\EventRepository;
use PHPUnit\Framework\TestCase;

/** @group integration */
final class MainPageFilteringIntegrationTest extends TestCase
{
    /**
     * FUNKCINIS: Tikrinama, ar repository teisingai grąžina tik ateities renginius.
     */
	/** @group integration */
    public function testOnlyFutureEventsAreReturnedForMainPage(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        
        try {
            $repo = new EventRepository($pdo);
            
            // 1. Sukuriamas praėjęs renginys
            $this->createEvent($pdo, 'Praėjęs testas', '2000-01-01 12:00:00');
            // 2. Sukuriamas būsimas renginys
            $this->createEvent($pdo, 'Būsimas testas', '2028-01-01 12:00:00');

            $events = $repo->homepageEvents(10);

            $titles = array_column($events, 'title');
            
            $this->assertContains('Būsimas testas', $titles);
            $this->assertNotContains('Praėjęs testas', $titles);
        } finally {
            $pdo->rollBack();
        }
    }

    private function createEvent($pdo, $title, $date): void {
    $stmt = $pdo->prepare("INSERT INTO events (
        organizer_id, title, event_date, status, category, location, price, description
    ) VALUES (1, ?, ?, 'approved', 'Test', 'Vilnius', 0.00, 'Testinis aprašymas')");
    
    $stmt->execute([$title, $date]);
}
}