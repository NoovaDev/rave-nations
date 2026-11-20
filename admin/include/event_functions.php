<?php
declare(strict_types=1);

require_once __DIR__ . '/upload_helper.php';
require_once __DIR__ . '/package_functions.php';

/**
 * Route AJAX requests for events and main ticket packages.
 */
function handleEventAjaxRequests(PDO $pdo, string $currentRole): void
{
    header('Content-Type: application/json');
    $action = $_GET['ajax_action'] ?? '';

    if ($action === 'get_event' && isset($_GET['event_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([(int) $_GET['event_id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
        exit;
    }

    if ($action === 'delete_event' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        if ($eventId > 0 && $currentRole === 'admin') {
            try {
                $stmtPoster = $pdo->prepare("SELECT poster_path FROM events WHERE id = ?");
                $stmtPoster->execute([$eventId]);
                rnAdminDeleteFile($stmtPoster->fetchColumn() ?: null);

                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                echo json_encode(['success' => $stmt->execute([$eventId])]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if (handlePackageAjax($pdo, $currentRole, $action)) {
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

/**
 * Handle Update Event POST form submission.
 */
function handleUpdateEvent(PDO $pdo, string $currentRole): array
{
    $response = ['message' => '', 'error' => ''];
    if ($currentRole !== 'admin') {
        return $response;
    }

    $eventId     = (int) ($_POST['event_id'] ?? 0);
    $eventName   = trim($_POST['event_name'] ?? '');
    $eventDate   = $_POST['event_date'] ?? '';
    $eventTime   = $_POST['event_time'] ?? '20:00';
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'published';

    $upload = rnAdminUploadPoster('event_poster', 'poster_');
    if ($upload['error']) {
        $response['error'] = $upload['error'];
        return $response;
    }

    if ($eventId > 0 && $eventName !== '') {
        try {
            if ($upload['path']) {
                $stmtOld = $pdo->prepare("SELECT poster_path FROM events WHERE id = ?");
                $stmtOld->execute([$eventId]);
                rnAdminDeleteFile($stmtOld->fetchColumn() ?: null);

                $stmt = $pdo->prepare("UPDATE events SET event_name = ?, location = ?, event_date = ?, event_time = ?, description = ?, poster_path = ?, status = ? WHERE id = ?");
                $stmt->execute([$eventName, $location, $eventDate, $eventTime, $description, $upload['path'], $status, $eventId]);
            } else {
                $stmt = $pdo->prepare("UPDATE events SET event_name = ?, location = ?, event_date = ?, event_time = ?, description = ?, status = ? WHERE id = ?");
                $stmt->execute([$eventName, $location, $eventDate, $eventTime, $description, $status, $eventId]);
            }
            $response['message'] = "Event details updated successfully!";
        } catch (Exception $e) {
            $response['error'] = "Failed to update event: " . $e->getMessage();
        }
    }

    return $response;
}

/**
 * Handle Creating New Event Wizard Form Submission.
 */
function handleCreateEvent(PDO $pdo, string $currentRole): array
{
    $response = ['message' => '', 'error' => ''];
    if ($currentRole !== 'admin') {
        return $response;
    }

    $eventName   = trim($_POST['event_name'] ?? '');
    $eventDate   = $_POST['event_date'] ?? '';
    $eventTime   = $_POST['event_time'] ?? '20:00';
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $packageName = trim($_POST['package_name'] ?? 'Standard Pass');
    $ticketPrice = (float) ($_POST['ticket_price'] ?? 0.00);
    $benefits    = trim($_POST['benefits'] ?? '');

    $upload = rnAdminUploadPoster('event_poster', 'poster_');
    if ($upload['error']) {
        $response['error'] = $upload['error'];
        return $response;
    }
    if (!$upload['path']) {
        $response['error'] = "Please upload an event poster image.";
        return $response;
    }

    if ($eventName !== '') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO events (event_name, location, event_date, event_time, description, poster_path, status) VALUES (?, ?, ?, ?, ?, ?, 'published')");
            $stmt->execute([$eventName, $location, $eventDate, $eventTime, $description, $upload['path']]);
            $eventId = (int) $pdo->lastInsertId();

            $stmtPkg = $pdo->prepare("INSERT INTO event_packages (event_id, package_name, ticket_price, benefits) VALUES (?, ?, ?, ?)");
            $stmtPkg->execute([$eventId, $packageName, $ticketPrice, $benefits]);

            $pdo->commit();
            $response['message'] = "Event & main ticket package created successfully! (ID: #{$eventId})";
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['error'] = "Database error: " . $e->getMessage();
        }
    }

    return $response;
}

/**
 * Fetch dashboard summary counts.
 */
function fetchDashboardStats(PDO $pdo): array
{
    $stats = [
        'total_events'     => 0,
        'published_events' => 0,
        'total_users'      => 0,
    ];

    try {
        $stats['total_users'] = (int) $pdo->query("SELECT COUNT(*) FROM user_registration")->fetchColumn();

        if ($pdo->query("SHOW TABLES LIKE 'events'")->rowCount() > 0) {
            $stats['total_events']     = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
            $stats['published_events'] = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
        }
    } catch (PDOException $e) {
        error_log('Admin stats query error: ' . $e->getMessage());
    }

    return $stats;
}

/**
 * Fetch all events with main ticket pass count.
 */
function fetchAllEventsWithCounts(PDO $pdo): array
{
    try {
        $stmt = $pdo->query("
            SELECT e.id, e.event_name, e.location, e.event_date, e.event_time, e.description, e.poster_path, e.status,
                   COUNT(p.id) AS package_count
            FROM events e
            LEFT JOIN event_packages p ON e.id = p.event_id
            GROUP BY e.id, e.event_name, e.location, e.event_date, e.event_time, e.description, e.poster_path, e.status
            ORDER BY e.event_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch events error: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch next auto increment ID for events table.
 */
function fetchNextAutoIncrementId(PDO $pdo): int
{
    try {
        $rowId = $pdo->query("SHOW TABLE STATUS LIKE 'events'")->fetch(PDO::FETCH_ASSOC);
        if ($rowId && isset($rowId['Auto_increment'])) {
            return (int) $rowId['Auto_increment'];
        }
    } catch (PDOException $e) {
    }
    return 1;
}