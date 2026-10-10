<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');

    if (!valid_csrf($token)) {
        http_response_code(403);
        $error = 'Invalid session. Refresh and try again.';
    } else {
        $email = strtolower(trim(
            (string)($_POST['email'] ?? '')
        ));

        $password = (string)($_POST['password'] ?? '');

        $emailHash = hash('sha256', $email);
        $ipHash = hash(
            'sha256',
            (string)($_SERVER['REMOTE_ADDR'] ?? '')
        );

        $since = date('Y-m-d H:i:s', time() - 900);

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM admin_login_attempts
             WHERE attempted_at >= ?
             AND (email_hash = ? OR ip_hash = ?)'
        );

        $stmt->execute([$since, $emailHash, $ipHash]);

        if ((int)$stmt->fetchColumn() >= 5) {
            $error = 'Too many login attempts. Try again later.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, name, password_hash
                 FROM admins
                 WHERE email = ? AND is_active = 1
                 LIMIT 1'
            );

            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if (
                $admin &&
                password_verify(
                    $password,
                    $admin['password_hash']
                )
            ) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['last_activity'] = time();
                $_SESSION['csrf_token'] = bin2hex(
                    random_bytes(32)
                );

                $stmt = $pdo->prepare(
                    'UPDATE admins
                     SET last_login_at = NOW()
                     WHERE id = ?'
                );

                $stmt->execute([$admin['id']]);

                header('Location: dashboard.php');
                exit;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO admin_login_attempts
                 (email_hash, ip_hash)
                 VALUES (?, ?)'
            );

            $stmt->execute([$emailHash, $ipHash]);

            $error = 'Invalid email or password.';
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
    <title>Admin Login | Rave Nations</title>
    <link rel="stylesheet" href="../style/admin.css">
</head>
<body>
    <main class="auth-page">
        <section class="login-card">
            <div class="brand">RAVE NATIONS</div>

            <p class="eyebrow">ADMINISTRATION</p>
            <h1>Welcome Back</h1>

            <p class="subtitle">
                Sign in to manage Rave Nations.
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

                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="username"
                    required
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

                <button type="submit">Sign In</button>
            </form>

            <p class="footer-note">
                Authorized administrators only.
            </p>
        </section>
    </main>
</body>
</html>
