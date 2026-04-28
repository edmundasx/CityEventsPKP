<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Db;
use App\Repositories\EventRepository;
use App\Support\AppBasePath;
use App\Support\LithuaniaPlaces;

final class HomeController
{
    public function index(): void
    {
        $base = AppBasePath::fromServer();

        // Layout expects these variables
        $title = "Home";
        $view = __DIR__ . "/../Views/pages/home.php";
        $enableLoginModal = true;

        $repo = new EventRepository(Db::pdo());
        $events = $repo->homepageEvents(null, true, 1); // all approved events from now -1h
        $mapEvents = $repo->mapEvents(true, 1);

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
            if (trim((string) ($event["image"] ?? "")) === "" && $map !== null) {
                $event["image"] = (string) ($map["cover_image"] ?? "");
            }
        }
        unset($event);

        $homeMapEvents = array_map(
            static fn(array $event): array => [
                "id" => (int) ($event["id"] ?? 0),
                "title" => (string) ($event["title"] ?? ""),
                "location" => (string) ($event["location"] ?? ""),
                "date" => (string) ($event["date"] ?? ""),
                "time" => (string) ($event["time"] ?? ""),
                "price" => (string) ($event["price"] ?? ""),
                "image" => (string) ($event["image"] ?? ""),
                "category" => (string) ($event["category"] ?? ""),
                "district" => (string) ($event["district"] ?? ""),
                "organizer_name" => (string) ($event["organizer_name"] ?? ""),
                "tags" => (string) ($event["tags"] ?? ""),
                "lat" => isset($event["lat"]) ? (float) $event["lat"] : null,
                "lng" => isset($event["lng"]) ? (float) $event["lng"] : null,
            ],
            $events,
        );

        $searchIndexJson = json_encode(
            array_map(
                static fn(array $e): array => [
                    "id" => (int) ($e["id"] ?? 0),
                    "title" => (string) ($e["title"] ?? ""),
                    "organizer_name" => (string) ($e["organizer_name"] ?? ""),
                    "location" => (string) ($e["location"] ?? ""),
                    "category" => (string) ($e["category"] ?? ""),
                    "district" => (string) ($e["district"] ?? ""),
                    "tags" => (string) ($e["tags"] ?? ""),
                ],
                $homeMapEvents,
            ),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        if ($searchIndexJson === false) {
            $searchIndexJson = "[]";
        }

        $ltPlacesJson = json_encode(
            LithuaniaPlaces::all(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        if ($ltPlacesJson === false) {
            $ltPlacesJson = "[]";
        }

        $ltMapTargetsJson = json_encode(
            LithuaniaPlaces::mapTargets(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        if ($ltMapTargetsJson === false) {
            $ltMapTargetsJson = "[]";
        }

        $categoryPopularity = [];
        foreach ($homeMapEvents as $event) {
            $rawCategory = (string) ($event["category"] ?? "");
            if ($rawCategory === "") {
                continue;
            }
            $parts = preg_split("/\s*,\s*/u", $rawCategory) ?: [];
            foreach ($parts as $part) {
                $label = trim((string) $part);
                if ($label === "") {
                    continue;
                }
                $key = mb_strtolower($label, "UTF-8");
                if (!isset($categoryPopularity[$key])) {
                    $categoryPopularity[$key] = [
                        "key" => $key,
                        "label" => $label,
                        "count" => 0,
                    ];
                }
                $categoryPopularity[$key]["count"]++;
            }
        }
        usort($categoryPopularity, static function (array $a, array $b): int {
            if (($a["count"] ?? 0) === ($b["count"] ?? 0)) {
                return strcmp((string) ($a["label"] ?? ""), (string) ($b["label"] ?? ""));
            }
            return ($b["count"] ?? 0) <=> ($a["count"] ?? 0);
        });

        $categoryPopularity = array_slice(array_values($categoryPopularity), 0, 20);

        $publicRoot = dirname(__DIR__, 2) . "/public";
        $homeMapJsPath = $publicRoot . "/assets/js/home-map.js";
        $homeMapJsVer = is_file($homeMapJsPath) ? (string) filemtime($homeMapJsPath) : "1";

        $pageStyles = [
            "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css",
        ];
        $pageScripts = [
            "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js",
            $base . "/assets/js/home-map.js?v=" . $homeMapJsVer,
        ];

        require __DIR__ . "/../Views/layouts/main.php";
    }
}
