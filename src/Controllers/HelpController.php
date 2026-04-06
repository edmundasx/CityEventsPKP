<?php
declare(strict_types=1);

namespace App\Controllers;

final class HelpController
{
    public function index(): void
    {
        $base = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? ""), "/");
        if ($base === "" || $base === "." || $base === "/") {
            $base = "";
        }

        $title = "Help";
        $view = __DIR__ . "/../Views/pages/help.php";

        require __DIR__ . "/../Views/layouts/main.php";
    }
}
