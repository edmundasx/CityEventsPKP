<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Db;
use App\Repositories\EventRepository;
use PHPUnit\Framework\TestCase;

/** @group integration */
final class HomePageIntegrationTests extends TestCase
{
    /** @group integration */
    public function testHomepageEventsAreChronologicalAndFreePriceIsMapped(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $now = new \DateTimeImmutable("now");
            $past = $now->modify("-2 days")->format("Y-m-d H:i:s");
            $soon = $now->modify("+2 hours")->format("Y-m-d H:i:s");
            $later = $now->modify("+1 day")->format("Y-m-d H:i:s");

            $this->createEvent(
                $pdo,
                "US-Past Event",
                $past,
                10.00,
                "business",
                "Vilnius",
            );
            $this->createEvent(
                $pdo,
                "US-Free Soon Event",
                $soon,
                0.00,
                "music",
                "Kaunas",
            );
            $this->createEvent(
                $pdo,
                "US-Paid Later Event",
                $later,
                12.50,
                "education",
                "Klaipėda",
            );

            $repo = new EventRepository($pdo);
            $events = $repo->homepageEvents(50, true, 0);

            $titles = array_column($events, "title");

            $this->assertContains("US-Free Soon Event", $titles);
            $this->assertContains("US-Paid Later Event", $titles);
            $this->assertNotContains("US-Past Event", $titles);

            $soonIndex = array_search("US-Free Soon Event", $titles, true);
            $laterIndex = array_search("US-Paid Later Event", $titles, true);
            $this->assertIsInt($soonIndex);
            $this->assertIsInt($laterIndex);
            $this->assertLessThan(
                $laterIndex,
                $soonIndex,
                "Renginiai turi būti rikiuojami chronologiškai (artimiausi pradžioje).",
            );

            $freeEvent = null;
            foreach ($events as $event) {
                if (($event["title"] ?? "") === "US-Free Soon Event") {
                    $freeEvent = $event;
                    break;
                }
            }
            $this->assertNotNull($freeEvent);
            $this->assertSame(
                "Nemokamai",
                $freeEvent["price"] ?? null,
                "Jei kaina == 0.00, turi būti rodoma 'Nemokamai'.",
            );
        } finally {
            $pdo->rollBack();
        }
    }

    /** @group integration */
    public function testEventTilesContainRequiredFieldsAndEmptyStateMessage(): void
    {
        $events = [
            [
                "id" => 501,
                "title" => "Vilniaus technologijų forumas",
                "date" => "2026-12-01",
                "time" => "18:30",
                "location" => "Vilnius",
                "price" => "Nemokamai",
                "image" => "/images/test-event.jpg",
                "category" => "business",
            ],
        ];

        $gridId = "eventsGridUserStory";
        $gridClass = "events-grid";
        $gridExtraClass = "";
        $emptyText = "Šiuo metu renginių nėra.";
        $basePath = "/events";

        ob_start();
        require __DIR__ . "/../../src/Views/partials/events-grid.php";
        $html = (string) ob_get_clean();

        $this->assertStringContainsString("Vilniaus technologijų forumas", $html);
        $this->assertStringContainsString("2026-12-01", $html);
        $this->assertStringContainsString("18:30", $html);
        $this->assertStringContainsString("Vilnius", $html);
        $this->assertStringContainsString("Business", $html);
        $this->assertStringContainsString('href="/events/501"', $html);
        $this->assertStringContainsString(
            'data-category="business"',
            $html,
            "Kortelėje turi būti kategorijos žyma.",
        );

        $events = [];
        ob_start();
        require __DIR__ . "/../../src/Views/partials/events-grid.php";
        $emptyHtml = (string) ob_get_clean();

        $this->assertStringContainsString(
            "Šiuo metu renginių nėra.",
            $emptyHtml,
            "Kai renginių nėra, turi būti rodoma nustatyta žinutė.",
        );
    }

    /** @group integration */
    public function testPriceMappingLogicIntegration(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO events (organizer_id, title, price, status, event_date, category, location, description)
                 VALUES (1, 'Nemokamas testas', 0.00, 'approved', '2027-01-01 10:00:00', 'Test', 'Kaunas', 'Testinis aprašymas')",
            );
            $stmt->execute();
            $id = (int) $pdo->lastInsertId();

            $repo = new EventRepository($pdo);
            $event = $repo->findById($id);

            $this->assertNotNull($event, "Renginys nebuvo rastas DB.");
            $this->assertEquals("Nemokamai", $event["price"], "Kaina 0.00 duomenų bazėje turi būti atvaizduojama kaip 'Nemokamai'.");
        } finally {
            $pdo->rollBack();
        }
    }

    /** @group integration */
    public function testOnlyFutureEventsAreReturnedForMainPage(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $repo = new EventRepository($pdo);

            $this->createBasicEvent($pdo, "Praėjęs testas", "2000-01-01 12:00:00");
            $this->createBasicEvent($pdo, "Būsimas testas", "2028-01-01 12:00:00");

            $events = $repo->homepageEvents(10);
            $titles = array_column($events, "title");

            $this->assertContains("Būsimas testas", $titles);
            $this->assertNotContains("Praėjęs testas", $titles);
        } finally {
            $pdo->rollBack();
        }
    }

    private function createBasicEvent(\PDO $pdo, string $title, string $date): void
    {
        $stmt = $pdo->prepare(
            "INSERT INTO events (
                organizer_id, title, event_date, status, category, location, price, description
            ) VALUES (1, ?, ?, 'approved', 'Test', 'Vilnius', 0.00, 'Testinis aprašymas')",
        );

        $stmt->execute([$title, $date]);
    }

    private function createEvent(
        \PDO $pdo,
        string $title,
        string $eventDate,
        float $price,
        string $category,
        string $location,
    ): void {
        $stmt = $pdo->prepare(
            "INSERT INTO events (
                organizer_id, title, event_date, status, category, location, price, description
            ) VALUES (
                1, :title, :event_date, 'approved', :category, :location, :price, 'User story integration test'
            )",
        );

        $stmt->execute([
            ":title" => $title,
            ":event_date" => $eventDate,
            ":category" => $category,
            ":location" => $location,
            ":price" => $price,
        ]);
    }
}

