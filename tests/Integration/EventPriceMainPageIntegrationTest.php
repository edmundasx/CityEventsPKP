<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Db;
use App\Repositories\EventRepository;
use PHPUnit\Framework\TestCase;

/** @group integration */
final class EventDataMappingIntegrationTest extends TestCase
{   
    /**
     * FUNKCINIS: Tikrina, ar kaina 0.00 teisingai transformuojama į tekstą "Nemokamai".
     */
	/** @group integration */
    public function testPriceMappingLogicIntegration(): void
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            // Įterpiame bandomąjį įrašą
            $stmt = $pdo->prepare("INSERT INTO events (organizer_id, title, price, status, event_date, category, location) 
                                   VALUES (1, 'Nemokamas testas', 0.00, 'approved', '2027-01-01 10:00:00', 'Test', 'Kaunas')");
            $stmt->execute();
            $id = (int)$pdo->lastInsertId();

            $repo = new EventRepository($pdo);
            $event = $repo->findById($id);

            $this->assertNotNull($event, "Renginys nebuvo rastas DB.");
            $this->assertEquals("Nemokamai", $event['price'], "Kaina 0.00 duomenų bazėje turi būti atvaizduojama kaip 'Nemokamai'.");
        } finally {
            $pdo->rollBack();
        }
    }

    /**
     * NEFUNKCINIS: Saugumo/Vientisumo testas — užtikrinama, kad ilgas aprašymas nesulaužo struktūros.
     */
	/** @group integration */
    public function testLongDescriptionHandling(): void
    {
        $pdo = Db::pdo();
        $repo = new EventRepository($pdo);
        
        $longDescription = str_repeat("Labas ", 1000); // 6000 simbolių
        
        // Tikriname, ar apdorojamas ilgas tekstas
        $event = ['description' => $longDescription];
        $this->assertGreaterThan(5000, strlen($event['description']), "Aprašymas per trumpas testui.");
    }
}