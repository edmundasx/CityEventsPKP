<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Db;
use App\Core\Router;

// Bendras integracinių testų bootstrap: sukuria izoliuotą testinę DB,
// užpildo bazinius duomenis ir leidžia maršrutą kviesti per tikrą routerį.
function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message .
            ' Expected: ' . var_export($expected, true) .
            ' Actual: ' . var_export($actual, true),
        );
    }
}

function adminTestConfig(): array
{
    return [
        'host' => getenv('CITYEVENTS_TEST_DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('CITYEVENTS_TEST_DB_PORT') ?: '3306'),
        'database' => getenv('CITYEVENTS_TEST_DB_NAME') ?: 'cityevents_admin_panel_test',
        'user' => getenv('CITYEVENTS_TEST_DB_USER') ?: 'root',
        'password' => getenv('CITYEVENTS_TEST_DB_PASSWORD') ?: '',
    ];
}

function adminTestBootstrapDatabase(): PDO
{
    $config = adminTestConfig();
    $database = (string) $config['database'];
    if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
        throw new RuntimeException('Unsafe test database name.');
    }

    $serverPdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;charset=utf8mb4',
            $config['host'],
            $config['port'],
        ),
        (string) $config['user'],
        (string) $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );

    $serverPdo->exec(
        'CREATE DATABASE IF NOT EXISTS `' .
        $database .
        '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
    );

    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $database,
        ),
        (string) $config['user'],
        (string) $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );

    // Kiekvienas testas pradeda nuo švarios schemos, kad scenarijai
    // nepriklausytų vienas nuo kito ir būtų kartojami tiek lokaliai, tiek CI.
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('DROP TABLE IF EXISTS events');
    $pdo->exec('DROP TABLE IF EXISTS users');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL DEFAULT '',
            role VARCHAR(50) NOT NULL DEFAULT 'user',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            organizer_id INT NULL,
            title VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL DEFAULT '',
            event_date DATETIME NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            rejection_reason TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_events_status (status),
            INDEX idx_events_organizer_id (organizer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $userStmt = $pdo->prepare("
        INSERT INTO users (id, name, email, password, role, created_at, updated_at)
        VALUES (:id, :name, :email, :password, :role, NOW(), NOW())
    ");

    $userStmt->execute([
        ':id' => 1,
        ':name' => 'Admin User',
        ':email' => 'admin@example.com',
        ':password' => '',
        ':role' => 'admin',
    ]);
    $userStmt->execute([
        ':id' => 2,
        ':name' => 'Organizer User',
        ':email' => 'organizer@example.com',
        ':password' => '',
        ':role' => 'organizer',
    ]);

    $eventStmt = $pdo->prepare("
        INSERT INTO events (
            id,
            organizer_id,
            title,
            location,
            event_date,
            status,
            rejection_reason,
            created_at,
            updated_at
        )
        VALUES (
            :id,
            :organizer_id,
            :title,
            :location,
            :event_date,
            :status,
            :rejection_reason,
            NOW(),
            NOW()
        )
    ");

    $eventStmt->execute([
        ':id' => 101,
        ':organizer_id' => 2,
        ':title' => 'Pending admin review event',
        ':location' => 'Vilnius',
        ':event_date' => '2026-05-01 18:00:00',
        ':status' => 'pending',
        ':rejection_reason' => null,
    ]);

    forceAdminTestPdo($pdo);

    return $pdo;
}

function forceAdminTestPdo(PDO $pdo): void
{
    // Testuose priverstinai pakeičiame statinį aplikacijos PDO,
    // kad controlleris ir repository naudotų testinę duomenų bazę.
    $reflection = new ReflectionClass(Db::class);
    $property = $reflection->getProperty('pdo');
    $property->setAccessible(true);
    $property->setValue(null, $pdo);
}

function adminSession(): array
{
    return [
        'id' => 1,
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => 'admin',
    ];
}

function dispatchAdminRoute(
    string $method,
    string $uri,
    array $post = [],
    array $get = [],
): array {
    // Čia simuliuojame AJAX užklausą į tikrą maršrutą:
    // session -> middleware -> controller -> repository -> DB -> JSON response.
    $_GET = $get;
    $_POST = $post;
    $_SESSION = [
        'auth_user' => adminSession(),
    ];
    $_SERVER = [
        'REQUEST_METHOD' => strtoupper($method),
        'REQUEST_URI' => $uri,
        'SCRIPT_NAME' => '/index.php',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json',
    ];

    http_response_code(200);

    $router = new Router('');
    $registerRoutes = require __DIR__ . '/../routes/web.php';
    $registerRoutes($router);

    ob_start();
    $router->dispatch(strtoupper($method), $uri);
    $body = (string) ob_get_clean();

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Expected JSON response, got: ' . $body);
    }

    return [
        'status' => http_response_code(),
        'body' => $body,
        'json' => $decoded,
    ];
}

function fetchEventRecord(PDO $pdo, int $eventId): array
{
    $stmt = $pdo->prepare('
        SELECT id, status, rejection_reason
        FROM events
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->execute([
        ':id' => $eventId,
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) {
        throw new RuntimeException('Event fixture was not found.');
    }

    return $row;
}
