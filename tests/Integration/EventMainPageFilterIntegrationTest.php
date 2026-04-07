<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Db;
use App\Repositories\EventRepository;
use PHPUnit\Framework\TestCase;

final class MainPageFilteringIntegrationTest extends TestCase
{
    /**
     * FUNKCINIS: Tikrinama, ar repository teisingai grąžina tik ateities renginius pagrindiniam puslapiui.
     */
    public function testOnlyFutureEventsAreReturnedForMainPage(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        
        try {
            $repo = new EventRepository($pdo);
            
            // 1. Sukuriamas praėjęs renginys
            $this->createEvent($pdo, 'Praėjęs testas', '2000-01-01');
            // 2. Sukuriamas būsimas renginys
            $this->createEvent($pdo, 'Būsimas testas', '2028-01-01');

            $events = $repo->getUpcomingEvents(10);

            $titles = array_column($events, 'title');
            
            $this->assertContains('Būsimas testas', $titles);
            $this->assertNotContains('Praėjęs testas', $titles);
        } finally {
            $pdo->rollBack();
        }
    }

    /**
     * NEFUNKCINIS: Našumo testas — užklausa gauti 50 renginių turi užtrukti < 100ms.
     */
    public function testUpcomingEventsQueryPerformance(): void
    {
        $pdo = Db::pdo();
        $repo = new EventRepository($pdo);

        $start = microtime(true);
        $repo->getUpcomingEvents(50);
        $duration = (microtime(true) - $start) * 1000; // Milisekundės

        $this->assertLessThan(100, $duration, "Pagrindinio puslapio krovimas turėtų būti greitesnis nei 100ms.");
    }

    private function createEvent($pdo, $title, $date): void {
        $stmt = $pdo->prepare("INSERT INTO events (organizer_id, title, event_date, status, category, location) 
                               VALUES (1, ?, ?, 'approved', 'Test', 'Vilnius')");
        $stmt->execute([$title, $date]);
    }
}