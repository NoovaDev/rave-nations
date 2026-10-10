<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

require_admin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id) {
    http_response_code(400);
    exit('Invalid event ID.');
}

$stmt = $pdo->prepare(
    'SELECT poster FROM events WHERE id = ?'
);

$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

$stmt = $pdo->prepare(
    'DELETE FROM events WHERE id = ?'
);

$stmt->execute([$id]);

// Existing poster files are retained.
// A separate cleanup process can remove unused files.

go('events.php');
