<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Db;
use App\Repositories\EventRepository;

final class EventController
{
    public function index(): void
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        $title = "Events";
        $view = __DIR__ . "/../Views/pages/events.php";
        $enableLoginModal = true;

        $repo = new EventRepository(Db::pdo());
        $events = $repo->homepageEvents(50, true);

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function show(int $id): void
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        $repo = new EventRepository(Db::pdo());
        $event = $repo->findById($id);

        if ($event === null) {
            http_response_code(404);
            echo "Event not found";
            return;
        }

        $title = $event["title"] ?? "Event details";
        $view = __DIR__ . "/../Views/pages/event-show.php";
        $enableLoginModal = true;

        require __DIR__ . "/../Views/layouts/main.php";
    }
}

