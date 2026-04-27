<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\EventRepository;
use DateTimeImmutable;

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
        $events = $repo->filterEvents([]);
        $categories = $repo->getCategories();
        $categoryCounts = $repo->getCategoryCounts();
        $priceRange = $repo->getPriceRange();

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function filter(): void
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        $title = "Events";
        $view = __DIR__ . "/../Views/pages/events.php";
        $enableLoginModal = true;

        $repo = new EventRepository(Db::pdo());
        $filters = [
            'category' => $_GET['category'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'price_min' => isset($_GET['price_min']) ? (float) $_GET['price_min'] : null,
            'price_max' => isset($_GET['price_max']) ? (float) $_GET['price_max'] : null,
        ];
        $events = $repo->filterEvents($filters);
        $categories = $repo->getCategories();
        $categoryCounts = $repo->getCategoryCounts();
        $priceRange = $repo->getPriceRange();

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function show(string $id): void
    {
        $id = (int) $id;

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

        $authUser = Auth::user();
        $isLoggedIn = is_array($authUser) && isset($authUser["id"]);
        $currentReminder = null;

        if ($isLoggedIn) {
            $currentReminder = $repo->findReminderForUser((int) $authUser["id"], $id);
        }

        $eventAtRaw = trim((string) (($event["date"] ?? "") . " " . ($event["time"] ?? "")));
        $isPastEvent = false;
        if ($eventAtRaw !== "") {
            $eventAt = new DateTimeImmutable($eventAtRaw);
            $isPastEvent = $eventAt < new DateTimeImmutable("now");
        }

        $hasReminder = is_array($currentReminder);
        $currentReminderMinutes = $hasReminder
            ? (int) ($currentReminder["minutes_before"] ?? 0)
            : null;
        $reminderOptions = EventRepository::reminderOptions();

        $title = $event["title"] ?? "Event details";
        $view = __DIR__ . "/../Views/pages/event-show.php";
        $enableLoginModal = true;

        require __DIR__ . "/../Views/layouts/main.php";
    }
}

