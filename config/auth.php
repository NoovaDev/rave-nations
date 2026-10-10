<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/database.php';

function require_admin(PDO $pdo): array
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: /rave/admin/login.php');
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, email
         FROM admins
         WHERE id = ? AND is_active = 1
         LIMIT 1'
    );

    $stmt->execute([(int)$_SESSION['admin_id']]);

    $admin = $stmt->fetch();

    if (!$admin) {
        $_SESSION = [];
        session_regenerate_id(true);

        header('Location: /rave/admin/login.php');
        exit;
    }

    return $admin;
}

/**
 * Verify CSRF token for POST requests.
 */
function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !valid_csrf($submittedToken)
    ) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }
}

/**
 * Redirect to an admin page.
 */
function go(string $page): never
{
    $allowedPages = [
        'dashboard.php',
        'events.php',
        'event_form.php'
    ];

    if (!in_array($page, $allowedPages, true)) {
        http_response_code(400);
        exit('Invalid redirect.');
    }

    header('Location: /rave/admin/' . $page, true, 303);
    exit;
}

