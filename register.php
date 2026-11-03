<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $usernameInput = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'support_agent';

    // Basic Validation
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    }
    if (empty($usernameInput)) {
        $errors[] = 'Username is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (strlen($passwordInput) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($passwordInput !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    if (!in_array($role, ['admin', 'support_agent'], true)) {
        $role = 'support_agent';
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM user_registration WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute(['username' => $usernameInput, 'email' => $email]);

            if ($stmt->fetch()) {
                $errors[] = 'Username or Email is already registered.';
            } else {
                $passwordHash = password_hash($passwordInput, PASSWORD_BCRYPT);

                $insertStmt = $pdo->prepare("
                    INSERT INTO user_registration (full_name, username, email, password_hash, role, status)
                    VALUES (:full_name, :username, :email, :password_hash, :role, 'active')
                ");

                $insertStmt->execute([
                    'full_name' => $fullName,
                    'username' => $usernameInput,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'role' => $role
                ]);

                $success = 'Account created successfully! You can now log in.';
            }
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
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
    <title>Create Account | Rave Nations Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/auth.css?v=1.0">
</head>
<body class="auth-body">

    <div class="rn-auth-container">
        <div class="rn-auth-card">
            
            <div class="rn-auth-header">
                <h2>RAVE<span>NATIONS</span></h2>
                <p>Staff Account Registration</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div>• <?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="rn-auth-form">
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="e.g. Sadev Induwara" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="e.g. sadev_admin" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@ravenations.lk" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">System Role</label>
                    <select id="role" name="role">
                        <option value="support_agent">Customer Support Agent</option>
                        <option value="admin">Administration</option>
                    </select>
                </div>

                <button type="submit" class="rn-auth-btn">
                    Create Staff Account
                </button>

            </form>

            <div class="rn-auth-footer-link">
                Already have an account? <a href="login.php">Sign In</a>
            </div>

        </div>
    </div>

</body>
</html>