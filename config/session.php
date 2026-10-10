<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('RAVE_ADMIN_SESSION');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/rave/admin/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

header('Cache-Control: no-store, private');

if (
    isset($_SESSION['admin_id']) &&
    time() - (int)($_SESSION['last_activity'] ?? 0) > 1800
) {
    $_SESSION = [];
    session_regenerate_id(true);
}

if (isset($_SESSION['admin_id'])) {
    $_SESSION['last_activity'] = time();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function valid_csrf(string $token): bool
{
    return $token !== ''
        && hash_equals(csrf_token(), $token);
}
