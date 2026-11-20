<?php
declare(strict_types=1);

/**
 * Handle adding a new main ticket package via POST form.
 */
function handleAddPackage(PDO $pdo, string $currentRole): array
{
    $response = ['message' => '', 'error' => ''];
    if ($currentRole !== 'admin') {
        return $response;
    }

    $eventId     = (int) ($_POST['event_id'] ?? 0);
    $packageName = trim($_POST['package_name'] ?? '');
    $ticketPrice = (float) ($_POST['ticket_price'] ?? 0.00);
    $benefits    = trim($_POST['benefits'] ?? '');

    if ($eventId > 0 && $packageName !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO event_packages (event_id, package_name, ticket_price, benefits) VALUES (?, ?, ?, ?)");
            $stmt->execute([$eventId, $packageName, $ticketPrice, $benefits]);
            $response['message'] = "Main ticket package tier added successfully!";
        } catch (Exception $e) {
            $response['error'] = "Failed to add package: " . $e->getMessage();
        }
    }

    return $response;
}

/**
 * Handle AJAX requests for Main Event Ticket Packages.
 */
function handlePackageAjax(PDO $pdo, string $currentRole, string $action): bool
{
    if ($action === 'get_packages' && isset($_GET['event_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM event_packages WHERE event_id = ? ORDER BY id ASC");
        $stmt->execute([(int) $_GET['event_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        return true;
    }

    if ($action === 'update_package' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pkgId    = (int) ($_POST['package_id'] ?? 0);
        $name     = trim($_POST['package_name'] ?? '');
        $price    = (float) ($_POST['ticket_price'] ?? 0);
        $benefits = trim($_POST['benefits'] ?? '');

        if ($pkgId > 0 && $name !== '' && $currentRole === 'admin') {
            try {
                $stmt = $pdo->prepare("UPDATE event_packages SET package_name = ?, ticket_price = ?, benefits = ? WHERE id = ?");
                echo json_encode(['success' => $stmt->execute([$name, $price, $benefits, $pkgId])]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid data provided']);
        }
        return true;
    }

    if ($action === 'delete_package' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pkgId = (int) ($_POST['package_id'] ?? 0);
        if ($pkgId > 0 && $currentRole === 'admin') {
            $stmt = $pdo->prepare("DELETE FROM event_packages WHERE id = ?");
            echo json_encode(['success' => $stmt->execute([$pkgId])]);
        } else {
            echo json_encode(['success' => false]);
        }
        return true;
    }

    return false;
}