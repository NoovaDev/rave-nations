<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

$currentRole   = (string) $_SESSION['role'];
$currentUserId = (int) $_SESSION['user_id'];

// Only Admins can access analytics
if ($currentRole !== 'admin') {
    header('Location: ' . ($currentRole === 'gate_keeper' ? 'scanner.php' : 'index.php'));
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Generate CSRF token if not set
if (empty($_SESSION['rn_csrf_token'])) {
    $_SESSION['rn_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['rn_csrf_token'];

$allowedStatuses = [
    'completed' => 'Completed',
    'pending'   => 'Pending',
    'inquiry'   => 'Inquiry',
    'refunded'  => 'Refunded',
    'cancelled' => 'Cancelled',
    'failed'    => 'Failed',
];

$error = '';
$success = '';

// Handle secure POST status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    $targetOrderId  = trim((string) ($_POST['order_id'] ?? ''));
    $newStatus      = strtolower(trim((string) ($_POST['new_status'] ?? '')));

    if (!hash_equals($csrfToken, $submittedToken)) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } elseif ($targetOrderId === '' || !array_key_exists($newStatus, $allowedStatuses)) {
        $error = 'Invalid order reference or status selection.';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT order_id, status FROM orders WHERE order_id = :order_id LIMIT 1");
            $checkStmt->execute([':order_id' => $targetOrderId]);
            $existingOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingOrder) {
                $error = 'Order "' . htmlspecialchars($targetOrderId, ENT_QUOTES, 'UTF-8') . '" was not found.';
            } else {
                $oldStatus = strtolower((string) $existingOrder['status']);
                if ($oldStatus === $newStatus) {
                    $success = 'Order ' . htmlspecialchars($targetOrderId, ENT_QUOTES, 'UTF-8') . ' is already set to ' . strtoupper($newStatus) . '.';
                } else {
                    $updateStmt = $pdo->prepare("
                        UPDATE orders
                        SET status = :status
                        WHERE order_id = :order_id
                        LIMIT 1
                    ");
                    $updateStmt->execute([
                        ':status'   => $newStatus,
                        ':order_id' => $targetOrderId,
                    ]);

                    $success = 'Order ' . htmlspecialchars($targetOrderId, ENT_QUOTES, 'UTF-8')
                        . ' updated from ' . strtoupper($oldStatus)
                        . ' to ' . strtoupper($newStatus) . '.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Failed to update order status: ' . $e->getMessage();
        }
    }
}

$orders = [];
$eventsList = [];
$packagesList = [];
$nationalitiesList = [];

// Fetch all ticket orders combined with events, packages, and optional partner add-ons
try {
    $stmt = $pdo->query("
        SELECT 
            o.id AS order_row_id,
            o.order_id,
            o.event_id,
            COALESCE(e.event_name, CONCAT('Event #', o.event_id)) AS event_name,
            o.package_id,
            COALESCE(p.package_name, CONCAT('Package #', o.package_id)) AS package_name,
            o.quantity,
            o.amount,
            COALESCE(NULLIF(UPPER(TRIM(o.currency)), ''), 'LKR') AS currency,
            o.first_name,
            o.last_name,
            o.email,
            o.phone,
            o.nic_passport,
            o.nationality,
            o.status,
            o.created_at,
            GROUP_CONCAT(DISTINCT pp.package_name ORDER BY pp.package_name SEPARATOR ', ') AS addons_list
        FROM orders o
        LEFT JOIN events e ON o.event_id = e.id
        LEFT JOIN event_packages p ON o.package_id = p.id
        LEFT JOIN order_addons oa ON oa.order_id = o.id
        LEFT JOIN partner_packages pp ON oa.addon_id = pp.id
        GROUP BY o.id
        ORDER BY o.created_at DESC, o.id DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $row) {
        $ev = trim((string) ($row['event_name'] ?? ''));
        if ($ev !== '' && !in_array($ev, $eventsList, true)) {
            $eventsList[] = $ev;
        }

        $pkg = trim((string) ($row['package_name'] ?? ''));
        if ($pkg !== '' && !in_array($pkg, $packagesList, true)) {
            $packagesList[] = $pkg;
        }

        $nat = trim((string) ($row['nationality'] ?? ''));
        if ($nat !== '' && !in_array($nat, $nationalitiesList, true)) {
            $nationalitiesList[] = $nat;
        }
    }
    sort($eventsList);
    sort($packagesList);
    sort($nationalitiesList);
} catch (PDOException $e) {
    // Fallback query in case order_addons table structure differs
    try {
        $stmt = $pdo->query("
            SELECT 
                o.id AS order_row_id,
                o.order_id,
                o.event_id,
                COALESCE(e.event_name, CONCAT('Event #', o.event_id)) AS event_name,
                o.package_id,
                COALESCE(p.package_name, CONCAT('Package #', o.package_id)) AS package_name,
                o.quantity,
                o.amount,
                COALESCE(NULLIF(UPPER(TRIM(o.currency)), ''), 'LKR') AS currency,
                o.first_name,
                o.last_name,
                o.email,
                o.phone,
                o.nic_passport,
                o.nationality,
                o.status,
                o.created_at,
                '' AS addons_list
            FROM orders o
            LEFT JOIN events e ON o.event_id = e.id
            LEFT JOIN event_packages p ON o.package_id = p.id
            ORDER BY o.created_at DESC, o.id DESC
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $row) {
            $ev = trim((string) ($row['event_name'] ?? ''));
            if ($ev !== '' && !in_array($ev, $eventsList, true)) {
                $eventsList[] = $ev;
            }
            $pkg = trim((string) ($row['package_name'] ?? ''));
            if ($pkg !== '' && !in_array($pkg, $packagesList, true)) {
                $packagesList[] = $pkg;
            }
            $nat = trim((string) ($row['nationality'] ?? ''));
            if ($nat !== '' && !in_array($nat, $nationalitiesList, true)) {
                $nationalitiesList[] = $nat;
            }
        }
        sort($eventsList);
        sort($packagesList);
        sort($nationalitiesList);
    } catch (PDOException $innerEx) {
        $error = 'Database error: ' . $innerEx->getMessage();
    }
}

// Calculate summary metrics (LKR)
$totalOrders = count($orders);
$totalRevenueLKR = 0.0;
$completedTicketsQty = 0;
$refundedCount = 0;
$inquiryCount = 0;

foreach ($orders as $ord) {
    $st = strtolower(trim((string) ($ord['status'] ?? '')));
    $qty = max(0, (int) ($ord['quantity'] ?? 0));
    $amt = (float) ($ord['amount'] ?? 0);

    if ($st === 'completed') {
        $totalRevenueLKR += $amt;
        $completedTicketsQty += $qty;
    }
    if ($st === 'refunded') {
        $refundedCount++;
    }
    if ($st === 'inquiry') {
        $inquiryCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Orders Analytics | Rave Nations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adminstyle/admin.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/navbar.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/header.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/analytics.css?v=1.2">
</head>
<body>

    <div class="rn-admin-layout">
        <?php include __DIR__ . '/include/navbar.php'; ?>

        <main class="rn-admin-main">
            <?php include __DIR__ . '/include/header.php'; ?>

            <div class="header-title-row">
                <div>
                    <h1>Ticket Orders Analytics</h1>
                    <p class="subtitle">Track LKR ticket orders, filter by event/package/status, export CSV, and update order statuses.</p>
                </div>
                <button type="button" id="exportCsvBtn" class="rn-btn-export">Export Filtered CSV</button>
            </div>

            <?php if ($error): ?>
                <div class="rn-alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="rn-alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <!-- SUMMARY STATS CARDS -->
            <section class="rn-stats-grid">
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">TOTAL TICKET ORDERS</span>
                        <div class="stat-icon-badge">&#127915;</div>
                    </div>
                    <h3 class="stat-value"><?= $totalOrders ?></h3>
                    <div class="stat-sub"><?= $completedTicketsQty ?> completed tickets</div>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">COMPLETED REVENUE (LKR)</span>
                        <div class="stat-icon-badge" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">&#8345;</div>
                    </div>
                    <h3 class="stat-value" style="color: #10b981;">LKR <?= number_format($totalRevenueLKR, 2) ?></h3>
                    <div class="stat-sub">Confirmed paid orders</div>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">INQUIRIES &amp; REFUNDS</span>
                        <div class="stat-icon-badge" style="color: #7c3aed; background: rgba(124, 58, 237, 0.1);">&#128269;</div>
                    </div>
                    <h3 class="stat-value" style="font-size: 20px;">
                        <?= $inquiryCount ?> Inquiry &middot; <?= $refundedCount ?> Refunded
                    </h3>
                    <div class="stat-sub">Needs follow-up or refund review</div>
                </div>
            </section>

            <!-- FILTER & SEARCH BAR -->
            <section class="rn-filter-card">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label for="searchInput">Search Orders</label>
                        <input
                            type="text"
                            id="searchInput"
                            class="rn-filter-control"
                            placeholder="Order ID, name, email, phone, NIC/passport..."
                        >
                    </div>

                    <div class="filter-field">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter" class="rn-filter-control">
                            <option value="all">All Statuses</option>
                            <?php foreach ($allowedStatuses as $stKey => $stLabel): ?>
                                <option value="<?= htmlspecialchars($stKey, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="eventFilter">Event</label>
                        <select id="eventFilter" class="rn-filter-control">
                            <option value="all">All Events</option>
                            <?php foreach ($eventsList as $evName): ?>
                                <option value="<?= htmlspecialchars(mb_strtolower($evName), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($evName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="packageFilter">Package</label>
                        <select id="packageFilter" class="rn-filter-control">
                            <option value="all">All Packages</option>
                            <?php foreach ($packagesList as $pkgName): ?>
                                <option value="<?= htmlspecialchars(mb_strtolower($pkgName), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($pkgName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="nationalityFilter">Nationality</label>
                        <select id="nationalityFilter" class="rn-filter-control">
                            <option value="all">All Nationalities</option>
                            <?php foreach ($nationalitiesList as $natName): ?>
                                <option value="<?= htmlspecialchars(mb_strtolower($natName), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($natName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-footer">
                    <span id="visibleCountLabel" class="visible-count-text">
                        Showing <?= $totalOrders ?> of <?= $totalOrders ?> orders (LKR)
                    </span>
                    <button type="button" id="resetFiltersBtn" class="rn-btn-reset">Reset Filters</button>
                </div>
            </section>

            <!-- BORDERED TABLE CARD -->
            <section class="rn-admin-card">
                <div class="card-header">
                    <h2>All Ticket Orders &amp; Packages (LKR)</h2>
                </div>

                <div class="table-responsive">
                    <table class="rn-bordered-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order Ref</th>
                                <th>Event &amp; Package</th>
                                <th>Guest Name</th>
                                <th>Contact &amp; Email</th>
                                <th>Qty</th>
                                <th>Amount (LKR)</th>
                                <th>NIC / Passport</th>
                                <th>Nationality</th>
                                <th>Status</th>
                                <th>Update Status</th>
                                <th>Date / Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $ord):
                                    $rawStatus = strtolower(trim((string) ($ord['status'] ?? 'pending')));
                                    $statusClass = 'status-' . preg_replace('/[^a-z0-9_-]/', '', $rawStatus);
                                    $eventName = trim((string) ($ord['event_name'] ?? ''));
                                    $packageName = trim((string) ($ord['package_name'] ?? ''));
                                    $nationalityVal = trim((string) ($ord['nationality'] ?? ''));
                                    $addonsStr = trim((string) ($ord['addons_list'] ?? ''));
                                    $fullName = trim((string) ($ord['first_name'] ?? '') . ' ' . (string) ($ord['last_name'] ?? ''));
                                    $amountNum = (float) ($ord['amount'] ?? 0);
                                    $qtyNum = (int) ($ord['quantity'] ?? 1);

                                    $searchBlob = mb_strtolower(
                                        (string) ($ord['order_id'] ?? '') . ' ' .
                                        $eventName . ' ' .
                                        $packageName . ' ' .
                                        $addonsStr . ' ' .
                                        $fullName . ' ' .
                                        (string) ($ord['email'] ?? '') . ' ' .
                                        (string) ($ord['phone'] ?? '') . ' ' .
                                        (string) ($ord['nic_passport'] ?? '') . ' ' .
                                        $nationalityVal . ' ' .
                                        $rawStatus
                                    );
                                ?>
                                    <tr
                                        class="order-row"
                                        data-status="<?= htmlspecialchars($rawStatus, ENT_QUOTES, 'UTF-8') ?>"
                                        data-event="<?= htmlspecialchars(mb_strtolower($eventName), ENT_QUOTES, 'UTF-8') ?>"
                                        data-package="<?= htmlspecialchars(mb_strtolower($packageName), ENT_QUOTES, 'UTF-8') ?>"
                                        data-nationality="<?= htmlspecialchars(mb_strtolower($nationalityVal), ENT_QUOTES, 'UTF-8') ?>"
                                        data-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-order="<?= htmlspecialchars((string) ($ord['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-event="<?= htmlspecialchars($eventName, ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-package="<?= htmlspecialchars($packageName, ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-addons="<?= htmlspecialchars($addonsStr, ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-guest="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-email="<?= htmlspecialchars((string) ($ord['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-phone="<?= htmlspecialchars((string) ($ord['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-qty="<?= $qtyNum ?>"
                                        data-csv-amount="<?= htmlspecialchars(number_format($amountNum, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-id="<?= htmlspecialchars((string) ($ord['nic_passport'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-nationality="<?= htmlspecialchars($nationalityVal, ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-status="<?= htmlspecialchars(strtoupper($rawStatus), ENT_QUOTES, 'UTF-8') ?>"
                                        data-csv-date="<?= htmlspecialchars((string) ($ord['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <td><strong><?= htmlspecialchars((string) ($ord['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                        <td>
                                            <div class="event-sub-name"><?= htmlspecialchars($eventName, ENT_QUOTES, 'UTF-8') ?></div>
                                            <span class="package-badge"><?= htmlspecialchars($packageName, ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if ($addonsStr !== ''): ?>
                                                <div class="addon-note">Add-ons: <?= htmlspecialchars($addonsStr, ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <div><?= htmlspecialchars((string) ($ord['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div style="font-size:11px; color:#64748b;"><?= htmlspecialchars((string) ($ord['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td style="text-align: center; font-weight: 700;"><?= $qtyNum ?></td>
                                        <td><strong>LKR <?= number_format($amountNum, 2) ?></strong></td>
                                        <td><?= htmlspecialchars((string) (($ord['nic_passport'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($nationalityVal !== '' ? $nationalityVal : '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                                <?= strtoupper(htmlspecialchars($rawStatus, ENT_QUOTES, 'UTF-8')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="status-update-form" data-order-id="<?= htmlspecialchars((string) ($ord['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) ($ord['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <select name="new_status" class="rn-status-select" aria-label="Change status for <?= htmlspecialchars((string) ($ord['order_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <?php foreach ($allowedStatuses as $stKey => $stLabel): ?>
                                                        <option value="<?= htmlspecialchars($stKey, ENT_QUOTES, 'UTF-8') ?>" <?= $rawStatus === $stKey ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="rn-btn-save-status">Save</button>
                                            </form>
                                        </td>
                                        <td style="font-size: 12px; color: #475569;"><?= htmlspecialchars((string) ($ord['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr id="noFilterMatchRow" style="display: none;">
                                    <td colspan="11" style="text-align: center; padding: 24px; color: #64748b;">
                                        No orders match your current filter criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 24px; color: #64748b;">No ticket orders found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        (function () {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const eventFilter = document.getElementById('eventFilter');
            const packageFilter = document.getElementById('packageFilter');
            const nationalityFilter = document.getElementById('nationalityFilter');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const exportCsvBtn = document.getElementById('exportCsvBtn');
            const visibleCountLabel = document.getElementById('visibleCountLabel');
            const noFilterMatchRow = document.getElementById('noFilterMatchRow');
            const rows = Array.from(document.querySelectorAll('#ordersTable tbody tr.order-row'));
            const totalCount = rows.length;

            function isRowVisible(row) {
                const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
                const st = (statusFilter ? statusFilter.value : 'all').toLowerCase();
                const ev = (eventFilter ? eventFilter.value : 'all').toLowerCase();
                const pkg = (packageFilter ? packageFilter.value : 'all').toLowerCase();
                const nat = (nationalityFilter ? nationalityFilter.value : 'all').toLowerCase();

                const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                const rowEvent = (row.getAttribute('data-event') || '').toLowerCase();
                const rowPackage = (row.getAttribute('data-package') || '').toLowerCase();
                const rowNationality = (row.getAttribute('data-nationality') || '').toLowerCase();
                const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

                const matchSearch = q === '' || rowSearch.includes(q);
                const matchStatus = st === 'all' || rowStatus === st;
                const matchEvent = ev === 'all' || rowEvent === ev;
                const matchPackage = pkg === 'all' || rowPackage === pkg;
                const matchNationality = nat === 'all' || rowNationality === nat;

                return matchSearch && matchStatus && matchEvent && matchPackage && matchNationality;
            }

            function applyFilters() {
                let shown = 0;

                rows.forEach((row) => {
                    const visible = isRowVisible(row);
                    row.style.display = visible ? '' : 'none';
                    if (visible) {
                        shown++;
                    }
                });

                if (visibleCountLabel) {
                    visibleCountLabel.textContent = `Showing ${shown} of ${totalCount} orders (LKR)`;
                }
                if (noFilterMatchRow) {
                    noFilterMatchRow.style.display = (totalCount > 0 && shown === 0) ? '' : 'none';
                }
            }

            function csvCell(val) {
                const str = String(val ?? '');
                return '"' + str.replace(/"/g, '""') + '"';
            }

            if (exportCsvBtn) {
                exportCsvBtn.addEventListener('click', function () {
                    const visibleRows = rows.filter((r) => r.style.display !== 'none');
                    if (visibleRows.length === 0) {
                        window.alert('No visible orders to export.');
                        return;
                    }

                    const headers = [
                        'Order ID', 'Event', 'Package', 'Add-ons', 'Guest Name',
                        'Email', 'Phone', 'Quantity', 'Currency', 'Amount',
                        'NIC / Passport', 'Nationality', 'Status', 'Created At'
                    ];

                    const lines = [headers.map(csvCell).join(',')];

                    visibleRows.forEach((row) => {
                        const line = [
                            row.getAttribute('data-csv-order') || '',
                            row.getAttribute('data-csv-event') || '',
                            row.getAttribute('data-csv-package') || '',
                            row.getAttribute('data-csv-addons') || '',
                            row.getAttribute('data-csv-guest') || '',
                            row.getAttribute('data-csv-email') || '',
                            row.getAttribute('data-csv-phone') || '',
                            row.getAttribute('data-csv-qty') || '0',
                            'LKR',
                            row.getAttribute('data-csv-amount') || '0.00',
                            row.getAttribute('data-csv-id') || '',
                            row.getAttribute('data-csv-nationality') || '',
                            row.getAttribute('data-csv-status') || '',
                            row.getAttribute('data-csv-date') || ''
                        ].map(csvCell).join(',');
                        lines.push(line);
                    });

                    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const stamp = new Date().toISOString().slice(0, 10);
                    link.href = url;
                    link.setAttribute('download', `rave-nations-orders-lkr-${stamp}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (eventFilter) eventFilter.addEventListener('change', applyFilters);
            if (packageFilter) packageFilter.addEventListener('change', applyFilters);
            if (nationalityFilter) nationalityFilter.addEventListener('change', applyFilters);

            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (statusFilter) statusFilter.value = 'all';
                    if (eventFilter) eventFilter.value = 'all';
                    if (packageFilter) packageFilter.value = 'all';
                    if (nationalityFilter) nationalityFilter.value = 'all';
                    applyFilters();
                });
            }

            document.querySelectorAll('.status-update-form').forEach((form) => {
                form.addEventListener('submit', function (e) {
                    const orderId = form.getAttribute('data-order-id') || '';
                    const selectEl = form.querySelector('select[name="new_status"]');
                    const chosenStatus = selectEl ? selectEl.value.toUpperCase() : '';
                    const ok = window.confirm(`Update order ${orderId} status to ${chosenStatus}?`);
                    if (!ok) {
                        e.preventDefault();
                    }
                });
            });
        })();
    </script>

</body>
</html>