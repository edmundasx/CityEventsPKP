<?php
declare(strict_types=1);

namespace App\Controllers;
use App\Core\Db;
use App\Repositories\EventRepository;
final class MapController
{
    public function index(array $params = []): void
    {
        // Base path like "/cityevents/public" (works behind index.php front controller)
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }
        $title = "Map";

        // Provide ALL data your partials might rely on (so they can't silently output nothing)

        $repo = new EventRepository(Db::pdo());
        $events = $repo->mapEvents(true);
        $breadcrumbs = [
            ["label" => "Events", "href" => $base . "/home#events"],
            ["label" => "Map", "href" => $base . "/map"],
        ];

        $filters = [
            "q" => (string) ($params["q"] ?? ""),
            "category" => (string) ($params["category"] ?? ""),
            "date" => (string) ($params["date"] ?? ""),
            "district" => (string) ($params["district"] ?? ""),
        ];

        $pageStyles = [
            "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css",
        ];
        $pageScripts = [
            "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js",
            $base . "/assets/js/map-page.js",
        ];

        // Paths (fail hard if wrong — otherwise you get "empty main" confusion)
        $view = realpath(__DIR__ . "/../Views/pages/map.php");
        $layout = realpath(__DIR__ . "/../Views/layouts/main.php");

        if ($view === false) {
            http_response_code(500);
            throw new \RuntimeException(
                "View not found: src/Views/pages/map.php",
            );
        }
        if ($layout === false) {
            http_response_code(500);
            throw new \RuntimeException(
                "Layout not found: src/Views/layouts/main.php",
            );
        }

        require $layout;
    }
}
