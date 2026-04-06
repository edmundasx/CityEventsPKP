<?php
declare(strict_types=1);

namespace App\Controllers;

final class EventController
{
    public function show(array $params): void
    {
        $id = (int) ($params["id"] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        $title = "";
        $view = __DIR__ . "/../Views/pages/events/blank.php";
        require __DIR__ . "/../Views/layouts/main.php";
    }
}
