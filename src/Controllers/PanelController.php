<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\AdminRepository;
use App\Repositories\OrganizerPanelRepository;
use App\Repositories\UserPanelRepository;

final class PanelController
{
    public function user(): void
    {
        $title = "User Panel";
        $view = __DIR__ . "/../Views/pages/panels/user/index.php";
        $pageScripts = ["/cityevents/public/assets/js/user-panel.js"];

        $authUser = Auth::user() ?? [];
        $userId = (int) ($authUser["id"] ?? 0);
        $recPage = max(1, (int) ($_GET["rec_page"] ?? 1));
        $recPerPage = 18 * $recPage;

        $repo = new UserPanelRepository(Db::pdo());
        $favoriteEvents = $repo->favoriteEvents($userId, 6);
        $recommendedResult = $repo->recommendedEvents($userId, 1, $recPerPage);
        $recommendedEvents = is_array($recommendedResult["items"] ?? null)
            ? $recommendedResult["items"]
            : [];
        $recommendedHasMore = (bool) ($recommendedResult["has_more"] ?? false);
        $notifications = $repo->notifications($userId, 12);
        $calendar = $this->calendarGrid();
        $monthLabel = $this->monthLabel();

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function organizer(): void
    {
        $title = "Organizer Panel";
        $view = __DIR__ . "/../Views/pages/panels/organizer/index.php";

        $authUser = Auth::user() ?? [];
        $organizerId = (int) ($authUser["id"] ?? 0);

        $repo = new OrganizerPanelRepository(Db::pdo());
        $stats = $repo->statsForOrganizer($organizerId);
        $myEvents = $repo->myEvents($organizerId, 8);
        $calendar = $this->calendarGrid();
        $monthLabel = $this->monthLabel();

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function admin(): void
    {
        $title = "Admin panelis";
        $view = __DIR__ . "/../Views/pages/panels/admin/index.php";
        $pageScripts = ["/cityevents/public/assets/js/admin.js"];
        $tab = (string) ($_GET["tab"] ?? "pending");
        $authUser = Auth::user() ?? [];

        $repo = new AdminRepository(Db::pdo());
        $stats = $repo->stats();
        $events = $repo->eventsByTab($tab, 30);
        $users = $repo->latestUsers(10);
        $recentActivity = $repo->recentActivity(8);
        $calendar = $this->calendarGrid();
        $monthLabel = $this->monthLabel();
        $adminFlash = $_SESSION["admin_flash"] ?? null;
        unset($_SESSION["admin_flash"]);

        require __DIR__ . "/../Views/layouts/main.php";
    }

    private function render(string $panelTitle): void
    {
        $title = $panelTitle;
        $view = __DIR__ . "/../Views/pages/panels/shared/placeholder.php";
        require __DIR__ . "/../Views/layouts/main.php";
    }

    private function monthLabel(): string
    {
        $months = [
            1 => "sausis",
            2 => "vasaris",
            3 => "kovas",
            4 => "balandis",
            5 => "geguze",
            6 => "birzelis",
            7 => "liepa",
            8 => "rugpjutis",
            9 => "rugsejis",
            10 => "spalis",
            11 => "lapkritis",
            12 => "gruodis",
        ];
        $month = (int) date("n");
        return date("Y") . " m. " . ($months[$month] ?? "");
    }

    private function calendarGrid(): array
    {
        $firstWeekday = (int) date("N", strtotime(date("Y-m-01")));
        $daysInMonth = (int) date("t");

        $cells = [];
        for ($i = 1; $i < $firstWeekday; $i++) {
            $cells[] = null;
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cells[] = $d;
        }
        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return array_chunk($cells, 7);
    }
}
