<?php
declare(strict_types=1);

/**
 * Allowed staff roles in user_registration.
 */
function rnGetAllowedStaffRoles(): array
{
    return [
        'admin'         => 'Admin',
        'support_agent' => 'Customer Support Agent',
        'gate_keeper'   => 'Gate Keeper (QR Scanner)',
    ];
}

/**
 * Validate staff role.
 */
function rnIsValidStaffRole(string $role): bool
{
    return array_key_exists($role, rnGetAllowedStaffRoles());
}

/**
 * Handle AJAX requests.
 */
function handleStaffAjaxRequests(
    PDO $pdo,
    string $currentRole,
    int $currentUserId
): void {

    header('Content-Type: application/json; charset=utf-8');

    $action = $_GET['ajax_action'] ?? '';

    if ($currentRole !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized access.'
        ]);
        exit;
    }

    try {

        // 1. Fetch staff details
        if ($action === 'get_staff' && isset($_GET['id'])) {

            $staffId = (int) $_GET['id'];

            $stmt = $pdo->prepare("
                SELECT id, username, email, role, full_name, status, created_at
                FROM user_registration
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$staffId]);

            $staff = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode($staff ?: []);
            exit;
        }

        // 2. Toggle staff status
        if ($action === 'toggle_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $staffId = (int) ($_POST['id'] ?? 0);

            if ($staffId <= 0 || $staffId === $currentUserId) {
                echo json_encode([
                    'success' => false,
                    'error' => 'You cannot deactivate your own account.'
                ]);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE user_registration
                SET status = IF(status = 'active', 'inactive', 'active')
                WHERE id = ?
            ");

            $stmt->execute([$staffId]);

            echo json_encode([
                'success' => $stmt->rowCount() > 0
            ]);
            exit;
        }

        // 3. Delete staff
        if ($action === 'delete_staff' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $staffId = (int) ($_POST['id'] ?? 0);

            if ($staffId <= 0 || $staffId === $currentUserId) {
                echo json_encode([
                    'success' => false,
                    'error' => 'You cannot delete your own account.'
                ]);
                exit;
            }

            $stmt = $pdo->prepare("
                DELETE FROM user_registration
                WHERE id = ?
            ");

            $stmt->execute([$staffId]);

            echo json_encode([
                'success' => $stmt->rowCount() > 0,
                'error' => $stmt->rowCount() > 0 ? '' : 'Staff account not found.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => false,
            'error' => 'Unknown action.'
        ]);
        exit;

    } catch (Throwable $e) {

        error_log('Staff AJAX error: ' . $e->getMessage());

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'error' => 'A database error occurred. Please try again.'
        ]);

        exit;
    }
}


/**
 * Register a new staff member.
 */
function handleCreateStaff(PDO $pdo, string $currentRole): array
{
    if ($currentRole !== 'admin') {
        return [
            'message' => '',
            'error' => 'Unauthorized action.'
        ];
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? 'support_agent');
    $password = (string) ($_POST['password'] ?? '');
    $status   = trim($_POST['status'] ?? 'active');

    // Validate role
    if (!rnIsValidStaffRole($role)) {
        return [
            'message' => '',
            'error' => 'Invalid role selected.'
        ];
    }

    // Validate status
    if (!in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }

    // Required fields
    if (
        $fullName === '' ||
        $username === '' ||
        $email === '' ||
        $password === ''
    ) {
        return [
            'message' => '',
            'error' => 'Please fill out all required fields.'
        ];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'message' => '',
            'error' => 'Please enter a valid email address.'
        ];
    }

    if (strlen($password) < 6) {
        return [
            'message' => '',
            'error' => 'Password must be at least 6 characters long.'
        ];
    }

    try {

        // Check duplicate username or email
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM user_registration
            WHERE username = ? OR email = ?
        ");

        $checkStmt->execute([$username, $email]);

        if ((int) $checkStmt->fetchColumn() > 0) {
            return [
                'message' => '',
                'error' => 'Username or email is already registered.'
            ];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert staff
        $insertStmt = $pdo->prepare("
            INSERT INTO user_registration
            (
                username,
                email,
                password_hash,
                role,
                full_name,
                status,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $insertStmt->execute([
            $username,
            $email,
            $passwordHash,
            $role,
            $fullName,
            $status
        ]);

        return [
            'message' => "Staff account created successfully for {$fullName}!",
            'error' => ''
        ];

    } catch (Throwable $e) {

        error_log('Create staff error: ' . $e->getMessage());

        return [
            'message' => '',
            'error' => 'Database error while creating staff account.'
        ];
    }
}


/**
 * Update existing staff member details.
 *
 * Supports:
 * - Admin
 * - Customer Support Agent
 * - Gate Keeper
 * - Password reset
 * - Account activation/deactivation
 */
function handleUpdateStaff(
    PDO $pdo,
    string $currentRole,
    int $currentUserId
): array {

    if ($currentRole !== 'admin') {
        return [
            'message' => '',
            'error' => 'Unauthorized action.'
        ];
    }

    // Get submitted values
    $staffId  = (int) ($_POST['staff_id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $status   = trim($_POST['status'] ?? 'active');
    $newPass  = (string) ($_POST['new_password'] ?? '');

    // Validate staff ID
    if ($staffId <= 0) {
        return [
            'message' => '',
            'error' => 'Invalid staff account ID.'
        ];
    }

    // Validate role
    if (!rnIsValidStaffRole($role)) {
        return [
            'message' => '',
            'error' => 'Invalid role selected: ' . htmlspecialchars($role)
        ];
    }

    // Validate status
    if (!in_array($status, ['active', 'inactive'], true)) {
        return [
            'message' => '',
            'error' => 'Invalid account status.'
        ];
    }

    // Prevent changing own admin privileges
    if (
        $staffId === $currentUserId &&
        ($role !== 'admin' || $status !== 'active')
    ) {
        return [
            'message' => '',
            'error' => 'You cannot downgrade or deactivate your own admin account.'
        ];
    }

    // Required fields
    if ($fullName === '' || $username === '' || $email === '') {
        return [
            'message' => '',
            'error' => 'Full name, username, and email are required.'
        ];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'message' => '',
            'error' => 'Please enter a valid email address.'
        ];
    }

    if ($newPass !== '' && strlen($newPass) < 6) {
        return [
            'message' => '',
            'error' => 'New password must be at least 6 characters long.'
        ];
    }

    try {

        // Check whether staff account exists
        $staffCheck = $pdo->prepare("
            SELECT id
            FROM user_registration
            WHERE id = ?
            LIMIT 1
        ");

        $staffCheck->execute([$staffId]);

        if (!$staffCheck->fetchColumn()) {
            return [
                'message' => '',
                'error' => 'Staff account not found.'
            ];
        }

        // Check duplicate username/email
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM user_registration
            WHERE (username = ? OR email = ?)
            AND id != ?
        ");

        $checkStmt->execute([
            $username,
            $email,
            $staffId
        ]);

        if ((int) $checkStmt->fetchColumn() > 0) {
            return [
                'message' => '',
                'error' => 'Another staff account already uses that username or email.'
            ];
        }

        // Update with new password
        if ($newPass !== '') {

            $passwordHash = password_hash(
                $newPass,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                UPDATE user_registration
                SET
                    username = ?,
                    email = ?,
                    password_hash = ?,
                    role = ?,
                    full_name = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $username,
                $email,
                $passwordHash,
                $role,
                $fullName,
                $status,
                $staffId
            ]);

        } else {

            // Update without changing password
            $stmt = $pdo->prepare("
                UPDATE user_registration
                SET
                    username = ?,
                    email = ?,
                    role = ?,
                    full_name = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $username,
                $email,
                $role,
                $fullName,
                $status,
                $staffId
            ]);
        }

        // VERIFY DATABASE UPDATE
        $verifyStmt = $pdo->prepare("
            SELECT role, status
            FROM user_registration
            WHERE id = ?
            LIMIT 1
        ");

        $verifyStmt->execute([$staffId]);

        $updatedStaff = $verifyStmt->fetch(PDO::FETCH_ASSOC);

        if (!$updatedStaff) {
            return [
                'message' => '',
                'error' => 'Could not verify the updated staff account.'
            ];
        }

        // Confirm role was saved
        if ($updatedStaff['role'] !== $role) {
            error_log(
                "Staff role update failed. Staff ID: {$staffId}. Expected: {$role}. Actual: {$updatedStaff['role']}"
            );

            return [
                'message' => '',
                'error' => 'Role was not saved correctly. Please check your database role column.'
            ];
        }

        // Confirm status was saved
        if ($updatedStaff['status'] !== $status) {
            return [
                'message' => '',
                'error' => 'Account status was not saved correctly.'
            ];
        }

        return [
            'message' => "Staff member (#{$staffId}) updated successfully! Role: " .
                rnGetAllowedStaffRoles()[$role],
            'error' => ''
        ];

    } catch (Throwable $e) {

        error_log('Update staff error: ' . $e->getMessage());

        return [
            'message' => '',
            'error' => 'Database error while updating staff. Please check your database configuration.'
        ];
    }
}


/**
 * Fetch all staff members.
 */
function fetchAllStaff(PDO $pdo): array
{
    try {

        $stmt = $pdo->query("
            SELECT
                id,
                username,
                email,
                role,
                full_name,
                status,
                created_at
            FROM user_registration
            ORDER BY id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {

        error_log('Fetch staff error: ' . $e->getMessage());

        return [];
    }
}


/**
 * Calculate staff statistics.
 */
function fetchStaffCounts(array $staffList): array
{
    $counts = [
        'total'         => count($staffList),
        'admin'         => 0,
        'support_agent' => 0,
        'gate_keeper'   => 0,
    ];

    foreach ($staffList as $member) {

        $role = $member['role'] ?? '';

        if (isset($counts[$role])) {
            $counts[$role]++;
        }
    }

    return $counts;
}
