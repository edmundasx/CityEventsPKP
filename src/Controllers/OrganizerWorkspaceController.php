<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Db;
use App\Repositories\OrganizerWorkspaceRepository;
use RuntimeException;

final class OrganizerWorkspaceController
{
    public function createForm(): void
    {
        $title = "Create event";
        $view = __DIR__ . "/../Views/pages/panels/organizer/create-event.php";
        $flashSuccess = $_SESSION["flash_success"] ?? null;
        $flashError = $_SESSION["flash_error"] ?? null;
        $old = $_SESSION["old_input"] ?? [];
        unset($_SESSION["flash_success"], $_SESSION["flash_error"], $_SESSION["old_input"]);
        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function create(): void
    {
        $organizerId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        if ($organizerId <= 0) {
            throw new RuntimeException("Organizer session missing.");
        }

        $input = [
            "title" => trim((string) ($_POST["title"] ?? "")),
            "description" => trim((string) ($_POST["description"] ?? "")),
            "category" => trim((string) ($_POST["category"] ?? "")),
            "location" => trim((string) ($_POST["location"] ?? "")),
            "lat" => trim((string) ($_POST["lat"] ?? "")),
            "lng" => trim((string) ($_POST["lng"] ?? "")),
            "event_date" => trim((string) ($_POST["event_date"] ?? "")),
            "price" => trim((string) ($_POST["price"] ?? "0")),
            "cover_image" => trim((string) ($_POST["cover_image"] ?? "")),
        ];

        if (
            $input["title"] === "" ||
            $input["description"] === "" ||
            $input["category"] === "" ||
            $input["location"] === "" ||
            $input["event_date"] === ""
        ) {
            $_SESSION["flash_error"] = "Fill in all required fields.";
            $_SESSION["old_input"] = $input;
            header("Location: " . $this->basePath() . "/organizer/events/create");
            return;
        }

        $repo = new OrganizerWorkspaceRepository(Db::pdo());
        $repo->createEvent($organizerId, [
            ...$input,
            "lat" => $input["lat"] === "" ? null : (float) $input["lat"],
            "lng" => $input["lng"] === "" ? null : (float) $input["lng"],
            "price" => $input["price"] === "" ? 0 : (float) $input["price"],
        ]);

        $_SESSION["flash_success"] = "Event created and submitted for approval.";
        header("Location: " . $this->basePath() . "/organizer/events");
    }

    public function events(): void
    {
        $organizerId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        if ($organizerId <= 0) {
            throw new RuntimeException("Organizer session missing.");
        }

        $repo = new OrganizerWorkspaceRepository(Db::pdo());
        $events = $repo->listEventsByOrganizer($organizerId);

        $title = "My Events";
        $view = __DIR__ . "/../Views/pages/panels/organizer/events-manage.php";
        $flashSuccess = $_SESSION["flash_success"] ?? null;
        unset($_SESSION["flash_success"]);

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function profileForm(): void
    {
        $organizerId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        if ($organizerId <= 0) {
            throw new RuntimeException("Organizer session missing.");
        }

        $repo = new OrganizerWorkspaceRepository(Db::pdo());
        $profile = $repo->getUserById($organizerId);
        if ($profile === null) {
            throw new RuntimeException("Organizer profile not found.");
        }

        $title = "Edit profile";
        $view = __DIR__ . "/../Views/pages/panels/organizer/profile-edit.php";
        $flashSuccess = $_SESSION["flash_success"] ?? null;
        $flashError = $_SESSION["flash_error"] ?? null;
        unset($_SESSION["flash_success"], $_SESSION["flash_error"]);

        require __DIR__ . "/../Views/layouts/main.php";
    }

    public function profileUpdate(): void
    {
        $organizerId = (int) ((Auth::user() ?? [])["id"] ?? 0);
        if ($organizerId <= 0) {
            throw new RuntimeException("Organizer session missing.");
        }

        $name = trim((string) ($_POST["name"] ?? ""));
        $phone = trim((string) ($_POST["phone"] ?? ""));

        if ($name === "") {
            $_SESSION["flash_error"] = "Name negali buti tuscias.";
            header("Location: " . $this->basePath() . "/organizer/profile");
            return;
        }

        $repo = new OrganizerWorkspaceRepository(Db::pdo());
        $repo->updateProfile($organizerId, $name, $phone !== "" ? $phone : null);

        $_SESSION["flash_success"] = "Profile updated successfully.";
        header("Location: " . $this->basePath() . "/organizer/profile");
    }

    private function basePath(): string
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            return "";
        }
        return $base;
    }
}
