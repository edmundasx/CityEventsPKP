<?php
declare(strict_types=1);

ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

session_start();

try {
    // Composer autoload if available, else fallback PSR-4 for App\
    $composerAutoload = __DIR__ . "/../vendor/autoload.php";
    if (is_file($composerAutoload)) {
        require $composerAutoload;
    } else {
        spl_autoload_register(function (string $class): void {
            $prefix = "App\\";
            $baseDir = __DIR__ . "/../src/";

            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace("\\", "/", $relative) . ".php";

            if (is_file($file)) {
                require $file;
            }
        });
    }

    $router = new \App\Core\Router();

    $router->setBasePath(\App\Support\AppBasePath::fromServer());

    $register = require __DIR__ . "/../routes/web.php";
    if (!is_callable($register)) {
        throw new RuntimeException("routes/web.php must return a callable");
    }
    $register($router);

    $router->dispatch();
} catch (Throwable $e) {
    http_response_code(500);
    echo "<pre style='white-space:pre-wrap;color:#ff6b35;background:#0b1220;padding:16px;border-radius:12px;'>";
    echo "500 ERROR:\n\n";
    echo $e->getMessage() . "\n\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
