<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Local setup only. Never expose this page publicly.
$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($remoteAddress, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local setup only.');
}

// Allow creation only when no administrator exists.
$stmt = $pdo->query('SELECT COUNT(*) FROM admins');
$adminCount = (int)$stmt->fetchColumn();

if ($adminCount > 0) {
    http_response_code(403);
    exit('Admin setup is already completed. Delete this file.');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');

    if (!valid_csrf($token)) {
        $error = 'Invalid request. Refresh the page.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($name === '' || strlen($name) > 100) {
            $error = 'Enter a valid administrator name.';
        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 190
        ) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($password) < 12) {
            $error = 'Password must contain at least 12 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                'INSERT INTO admins
                 (name, email, password_hash)
                 VALUES (?, ?, ?)'
            );

            $stmt->execute([$name, $email, $hash]);

            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Admin | Rave Nations</title>

    <link rel="stylesheet" href="../style/admin.css">
</head>
<body>

<main class="auth-page">
    <section class="login-card">

        <div class="brand">RAVE NATIONS</div>
        <p class="eyebrow">INITIAL ADMIN SETUP</p>

        <?php if ($success): ?>

            <h1>Account Created</h1>

            <p class="subtitle">
                Your administrator account has been created.
                Delete create_admin.php before logging in.
            </p>

            <a class="setup-link" href="login.php">
                Go to Admin Login
            </a>

        <?php else: ?>

            <h1>Create Administrator</h1>

            <p class="subtitle">
                Configure your first administrator account.
            </p>

            <?php if ($error !== ''): ?>
                <div class="alert" role="alert">
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        csrf_token(),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <label for="name">Administrator Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    required
                >

                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="190"
                    autocomplete="username"
                    required
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="12"
                    autocomplete="new-password"
                    required
                >

                <label for="confirm_password">
                    Confirm Password
                </label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    minlength="12"
                    autocomplete="new-password"
                    required
                >

                <button type="submit">
                    Create Admin Account
                </button>

            </form>

            <p class="footer-note">
                One-time local administrator setup.
            </p>

        <?php endif; ?>

    </section>
</main>

</body>
</html>
