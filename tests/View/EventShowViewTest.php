<?php
declare(strict_types=1);

namespace Tests\View;

use PHPUnit\Framework\TestCase;

final class EventShowViewTest extends TestCase
{
    public function testEventDetailsPageDisplaysAllMainInformation(): void
    {
        $base = "";
        $isLoggedIn = false;
        $isPastEvent = false;
        $hasReminder = false;
        $currentReminderMinutes = null;
        $reminderOptions = [
            15 => "15 min.",
            30 => "30 min.",
            60 => "1 val.",
            120 => "2 val.",
            360 => "6 val.",
            720 => "12 val.",
            1440 => "1 diena",
        ];

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
            "2026-03-17",
            $html,
            "Puslapyje turi buti rodoma renginio data"
        );
        $this->assertStringContainsString(
            "18:30",
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

        // Neprisijungusio vartotojo priminimo ikonėle turi buti uzpilkinta.
        $this->assertStringContainsString(
            "bg-slate-200 text-slate-400",
            $html,
            "Neprisijungusio vartotojo priminimo ikonėle turi buti pilka"
        );

    }

    public function testLoggedInUserSeesExtendedReminderOptions(): void
    {
        $base = "";
        $isLoggedIn = true;
        $isPastEvent = false;
        $hasReminder = true;
        $currentReminderMinutes = 60;
        $reminderOptions = [
            15 => "15 min.",
            30 => "30 min.",
            60 => "1 val.",
            120 => "2 val.",
            360 => "6 val.",
            720 => "12 val.",
            1440 => "1 diena",
        ];

        $event = [
            "id" => 99,
            "title" => "Future Event",
            "description" => "Example",
            "date" => "2026-07-11",
            "time" => "19:00",
            "location" => "Center",
            "price" => "€10.00",
            "category" => "Tech",
            "district" => "West",
            "image" => "/images/example.jpg",
        ];

        ob_start();
        require __DIR__ . "/../../src/Views/pages/event-show.php";
        $html = ob_get_clean();

        self::assertNotFalse($html);
        $this->assertStringContainsString("Nustatytas:", $html);
        $this->assertStringContainsString("15 min.", $html);
        $this->assertStringContainsString("2 val.", $html);
        $this->assertStringContainsString("12 val.", $html);
        $this->assertStringContainsString("Istrinti priminima", $html);
    }
}

