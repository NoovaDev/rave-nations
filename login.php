<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if (empty($identifier) || empty($passwordInput)) {
        $errors[] = 'Please enter both your username/email and password.';
    } else {
        try {
            // Find user by username or email using unique parameter names for PDO
            $stmt = $pdo->prepare("
                SELECT id, username, email, password_hash, role, full_name, status 
                FROM user_registration 
                WHERE username = :username OR email = :email 
                LIMIT 1
            ");
            $stmt->execute([
                'username' => $identifier,
                'email' => $identifier
            ]);
            $user = $stmt->fetch();

            if ($user && password_verify($passwordInput, $user['password_hash'])) {
                if ($user['status'] !== 'active') {
                    $errors[] = 'Your account is suspended or inactive.';
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];

                    // Role-Based Path Redirection
                    if ($user['role'] === 'admin') {
                        header('Location: admin/index.php');
                        exit;
                    } elseif ($user['role'] === 'support_agent') {
                        header('Location: admin/index.php');
                        exit;
                    } else {
                        header('Location: admin/index.php');
                        exit;
                    }
                }
            } else {
                $errors[] = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $errors[] = 'A database error occurred. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Rave Nations Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/login.css?v=1.0">
</head>
<body class="login-body">

    <div class="rn-login-container">
        <div class="rn-login-card">
            
            <div class="rn-login-header">
                <h2>RAVE<span>NATIONS</span></h2>
                <p>Staff & Management Portal</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div>• <?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="rn-login-form">
                
                <div class="form-group">
                    <label for="identifier">Username or Email</label>
                    <input type="text" id="identifier" name="identifier" placeholder="e.g. sadev_admin" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="rn-login-btn">
                    Sign In to Dashboard
                </button>

            </form>

            <div class="rn-login-footer-link">
                Don't have an account? <a href="register.php">Register Staff</a>
            </div>

        </div>
    </div>

</body>
</html>