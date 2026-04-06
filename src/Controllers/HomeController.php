<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Db;
use App\Repositories\EventRepository;

final class HomeController
{
    public function index(): void
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        // Layout expects these variables
        $title = "Home";
        $view = __DIR__ . "/../Views/pages/home.php";
        $enableLoginModal = true;

        $repo = new EventRepository(Db::pdo());
        $events = $repo->homepageEvents(null, true); // all future approved events
        $mapEvents = $repo->mapEvents(true);

        $mapById = [];
        foreach ($mapEvents as $event) {
            $mapById[(int) ($event["id"] ?? 0)] = $event;
        }

        foreach ($events as &$event) {
            $id = (int) ($event["id"] ?? 0);
            $map = $mapById[$id] ?? null;
            $event["category"] = (string) ($map["category"] ?? "");
            $event["district"] = (string) ($map["district"] ?? "");
            $event["lat"] = isset($map["lat"]) ? (float) $map["lat"] : null;
            $event["lng"] = isset($map["lng"]) ? (float) $map["lng"] : null;
        }
        unset($event);

        $homeMapEvents = array_map(
            static fn(array $event): array => [
                "id" => (int) ($event["id"] ?? 0),
                "title" => (string) ($event["title"] ?? ""),
                "location" => (string) ($event["location"] ?? ""),
                "date" => (string) ($event["date"] ?? ""),
                "time" => (string) ($event["time"] ?? ""),
                "category" => (string) ($event["category"] ?? ""),
                "lat" => isset($event["lat"]) ? (float) $event["lat"] : null,
                "lng" => isset($event["lng"]) ? (float) $event["lng"] : null,
            ],
            $events,
        );

        $pageStyles = [
            "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css",
        ];
        $pageScripts = [
            "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js",
            $base . "/assets/js/home-map.js",
        ];

        require __DIR__ . "/../Views/layouts/main.php";
    }
}
