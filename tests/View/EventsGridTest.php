<?php
declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;

final class EventsGridTest extends TestCase
{
    public function testEventCardLinksToEventDetailsPage(): void
    {
        $events = [
            [
                "id" => 123,
                "title" => "Test Event",
                "date" => "2026-03-17",
                "time" => "18:30",
                "location" => "Test Location",
                "price" => "Nemokamai",
                "image" => "/images/test.jpg",
                "category" => "music",
            ],
        ];

        $gridId = "eventsGridTest";
        $gridClass = "events-grid";
        $gridExtraClass = "";
        $emptyText = "No events";
        $basePath = "/events";

        ob_start();
        require __DIR__ . "/../../src/Views/partials/events-grid.php";
        $html = ob_get_clean();

        self::assertNotFalse($html);
        $this->assertStringContainsString(
            'href="/events/123"',
            $html,
            "Renginio kortelės nuoroda turi vesti į /events/123"
        );

        $this->assertStringContainsString(
            "Test Event",
            $html,
            "Renginio pavadinimas turi būti atvaizduotas kortelėje"
        );

        $this->assertStringContainsString(
            "Music",
            $html,
            "Renginio kategorija turi būti atvaizduota kortelėje"
        );
        $this->assertStringContainsString(
            'data-category="music"',
            $html,
            "Kortelė turi turėti data-category atributą"
        );
    }
}

