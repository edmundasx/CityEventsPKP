<?php
declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;

final class EventShowViewTest extends TestCase
{
    public function testEventDetailsPageDisplaysAllMainInformation(): void
    {
        $base = "";
        $event = [
            "id" => 123,
            "title" => "Test Event",
            "description" => "This is a test description.",
            "date" => "2026-03-17",
            "time" => "18:30",
            "location" => "Test Location",
            "price" => "Nemokamai",
            "category" => "Music",
            "district" => "Old Town",
            "image" => "/images/test.jpg",
        ];

        ob_start();
        require __DIR__ . "/../../src/Views/pages/event-show.php";
        $html = ob_get_clean();

        self::assertNotFalse($html);

        // Pavadinimas
        $this->assertStringContainsString(
            "Test Event",
            $html,
            "Puslapyje turi būti rodomas renginio pavadinimas"
        );

        // Data ir laikas
        $this->assertStringContainsString(
            "2026-03-17 18:30",
            $html,
            "Puslapyje turi būti rodoma renginio data ir laikas"
        );

        // Vieta
        $this->assertStringContainsString(
            "Test Location",
            $html,
            "Puslapyje turi būti rodoma renginio vieta"
        );

        // Kaina
        $this->assertStringContainsString(
            "Nemokamai",
            $html,
            "Puslapyje turi būti rodoma renginio kaina"
        );

        // Aprašymas
        $this->assertStringContainsString(
            "This is a test description.",
            $html,
            "Puslapyje turi būti rodomas renginio aprašymas"
        );

        // Kategorija / rajonas (papildoma susijusi informacija)
        $this->assertStringContainsString(
            "Music",
            $html,
            "Puslapyje turi būti rodoma renginio kategorija"
        );
        $this->assertStringContainsString(
            "Old Town",
            $html,
            "Puslapyje turi būti rodomas renginio rajonas"
        );
    }
}

