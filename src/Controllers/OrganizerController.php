<?php
declare(strict_types=1);

namespace App\Controllers;

final class OrganizerController
{
    public function index(): void
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        $title = "For Organizers";
        $view = __DIR__ . "/../Views/pages/organizers.php";

        require __DIR__ . "/../Views/layouts/main.php";
    }
}
