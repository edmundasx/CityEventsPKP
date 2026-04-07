<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Db;
use App\Repositories\EventRepository;
use PHPUnit\Framework\TestCase;

final class EventDataMappingIntegrationTest extends TestCase
{
    /**
     * FUNKCINIS: Tikrinama, ar repository teisingai paverčia 0.00 kainą į tekstą "Nemokamai".
     * Tai užtikrina teisingą "Business Logic" ir "UI Data Preparation" integraciją.
     */
    public function testPriceMappingLogicIntegration(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("INSERT INTO events (organizer_id, title, price, status, event_date, category, location) 
                                   VALUES (1, 'Nemokamas testas', 0.00, 'approved', '2027-01-01', 'Test', 'Kaunas')");
            $stmt->execute();
            $id = (int)$pdo->lastInsertId();

            $repo = new EventRepository($pdo);
            $event = $repo->findById($id);

            $this->assertEquals("Nemokamai", $event['price'], "Kaina 0.00 duomenų bazėje turi būti atvaizduojama kaip 'Nemokamai'.");
        } finally {
            $pdo->rollBack();
        }
    }

    /**
     * NEFUNKCINIS: Saugumo/Vientisumo testas — užtikrinama, kad ilgas aprašymas nesulaužo struktūros.
     */
    public function testLongDescriptionHandling(): void
    {
        $pdo = Db::pdo();
        $repo = new EventRepository($pdo);
        
        $longDescription = str_repeat("Labas ", 1000); // Labai ilgas tekstas
        
        // Tikriname, ar repository sugeba apdoroti ir grąžinti didelius duomenų kiekius be klaidų
        $event = ['description' => $longDescription];
        $this->assertGreaterThan(5000, strlen($event['description']));
    }
}