<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/database.php';

function rnEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rnIcon(string $name, string $class = 'rn-icon'): string
{
    $icons = [
        'download'   => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
        'printer'    => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>',
        'arrow-left' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
        'calendar'   => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'pin'        => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'user'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'id-card'    => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2"/><line x1="14" y1="10" x2="18" y2="10"/><line x1="14" y1="14" x2="18" y2="14"/></svg>',
        'mail'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
        'phone'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'shield'     => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
    ];

    return $icons[$name] ?? '';
}

$orderId = $_GET['order_id'] ?? $_SESSION['last_order_id'] ?? '';

if (empty($orderId)) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, e.event_name, e.event_date, e.event_time, e.location, e.poster_path, ep.package_name, ep.benefits
    FROM orders o
    JOIN events e ON o.event_id = e.id
    JOIN event_packages ep ON o.package_id = ep.id
    WHERE o.order_id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order voucher not found.");
}

$addonsList = [];
if (!empty($order['addons'])) {
    $addonIds = array_values(array_filter(array_map('intval', explode(',', (string) $order['addons']))));
    if (!empty($addonIds)) {
        $inClause = implode(',', array_fill(0, count($addonIds), '?'));
        $addonStmt = $pdo->prepare("SELECT * FROM partner_packages WHERE id IN ($inClause)");
        $addonStmt->execute($addonIds);
        $addonsList = $addonStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$rawTime = (string) ($order['event_time'] ?? '20:00:00');
$eventTimestamp = strtotime(trim((string) $order['event_date'] . ' ' . $rawTime)) ?: time();
$formattedDate = date('d M Y', $eventTimestamp);
$formattedTime = date('h:i A', $eventTimestamp);
$statusText = strtoupper((string) ($order['status'] ?? 'PENDING'));
$isPaid = in_array(strtolower($statusText), ['paid', 'completed', 'confirmed', 'success'], true);
$addonNames = array_values(array_map(static fn($a) => (string) $a['package_name'], $addonsList));
$quantity = (int)($order['quantity'] ?? 1);
$nationality = !empty($order['nationality']) ? $order['nationality'] : 'Sri Lanka';

$pdfPayload = [
    'orderId'    => (string) $order['order_id'],
    'status'     => $statusText,
    'isPaid'     => $isPaid,
    'guestType'  => ($nationality === 'Sri Lanka' ? 'LOCAL' : 'INTERNATIONAL') . ' GUEST PASS',
    'idLabel'    => ($nationality === 'Sri Lanka' ? 'NIC No' : 'Passport / ID No'),
    'eventName'  => (string) $order['event_name'],
    'tierName'   => (string) $order['package_name'] . ' (x' . $quantity . ')',
    'dateTime'   => $formattedDate . ' · ' . $formattedTime,
    'location'   => (string) $order['location'],
    'holderName' => trim((string) $order['first_name'] . ' ' . (string) $order['last_name']),
    'nicPassport'=> (string) $order['nic_passport'],
    'email'      => (string) $order['email'],
    'phone'      => (string) $order['phone'],
    'addons'     => $addonNames,
    'amount'     => 'LKR ' . number_format((float) $order['amount'], 2),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0e0b18">
    <title>Event Ticket Voucher | <?= rnEscape($order['order_id']) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Self-contained local QR Code generator library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

    <style>
        :root {
            --rn-dark: #0e0b18;
            --rn-pearl: #f6f5fa;
            --rn-card: #ffffff;
            --rn-surface: #faf9fd;
            --rn-border: #e7e4f2;
            --rn-purple: #7c2ae8;
            --rn-purple-light: #b455ff;
            --rn-purple-dark: #5b21b6;
            --rn-heading: #141122;
            --rn-text: #5d5b70;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background:
                radial-gradient(circle at 15% 10%, rgba(124, 42, 232, 0.08), transparent 40%),
                radial-gradient(circle at 85% 90%, rgba(180, 85, 255, 0.08), transparent 40%),
                var(--rn-pearl);
            color: var(--rn-heading);
            font-family: "Titillium Web", sans-serif;
            margin: 0;
            padding: 36px 18px 56px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            -webkit-font-smoothing: antialiased;
        }

        .rn-icon { width: 16px; height: 16px; flex-shrink: 0; display: inline-block; vertical-align: middle; }
        .rn-icon-sm { width: 14px; height: 14px; }

        .action-bar {
            width: 100%;
            max-width: 820px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .action-group { display: flex; flex-wrap: wrap; gap: 10px; }

        .rn-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 11px 22px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--rn-purple), var(--rn-purple-light));
            color: #ffffff;
            font-family: inherit;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(124, 42, 232, 0.34);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .rn-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(124, 42, 232, 0.48); }
        .rn-btn:disabled { opacity: 0.7; cursor: wait; transform: none; }

        .rn-btn-secondary {
            background: #ffffff;
            color: var(--rn-heading);
            border: 1.5px solid var(--rn-border);
            box-shadow: 0 6px 16px rgba(20, 17, 34, 0.05);
        }

        .rn-btn-secondary:hover {
            background: #faf9fd;
            border-color: #c4a3ff;
            color: var(--rn-purple-dark);
            box-shadow: 0 10px 22px rgba(109, 40, 217, 0.12);
        }

        .voucher-export-wrap { width: 100%; max-width: 820px; }

        .ticket-voucher {
            position: relative;
            width: 100%;
            background: var(--rn-card);
            border: 1px solid var(--rn-border);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(20, 17, 34, 0.12);
        }

        .voucher-header {
            position: relative;
            padding: 26px 30px;
            background:
                radial-gradient(circle at 85% 20%, rgba(139, 61, 255, 0.32), transparent 52%),
                linear-gradient(155deg, #0e0b18 0%, #19122e 100%);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            border-bottom: 2px dashed rgba(216, 180, 254, 0.35);
        }

        .brand-row { display: flex; align-items: center; gap: 12px; }

        .brand-logo-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .brand-logo-pill img { height: 24px; width: auto; display: block; }

        .brand-title { font-size: 17px; font-weight: 900; letter-spacing: 0.6px; color: #ffffff; }
        .brand-title span { color: #d8b4fe; font-weight: 700; }

        .header-badges { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }

        .order-id-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f3e8ff;
            padding: 6px 13px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .status-badge {
            padding: 6px 13px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .status-paid { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(74, 222, 128, 0.45); }
        .status-pending { background: rgba(250, 204, 21, 0.18); color: #fde047; border: 1px solid rgba(250, 204, 21, 0.4); }

        .voucher-body {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 245px;
        }

        .voucher-main-col {
            padding: 30px;
            border-right: 2px dashed var(--rn-border);
            position: relative;
        }

        .voucher-main-col::before,
        .voucher-main-col::after {
            content: "";
            position: absolute;
            right: -12px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--rn-pearl);
            border: 1px solid var(--rn-border);
            z-index: 3;
        }

        .voucher-main-col::before { top: -11px; }
        .voucher-main-col::after { bottom: -11px; }

        .guest-pass-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            background: #f3ebff;
            border: 1px solid #dfd0ff;
            color: var(--rn-purple-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .event-main-title {
            font-size: clamp(24px, 3.2vw, 32px);
            font-weight: 900;
            margin: 0 0 4px;
            line-height: 1.12;
            color: var(--rn-heading);
            letter-spacing: -0.5px;
        }

        .event-tier-line { margin: 0 0 20px; color: var(--rn-purple); font-weight: 800; font-size: 15.5px; }

        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }

        .meta-box { background: var(--rn-surface); border: 1px solid var(--rn-border); padding: 12px 14px; border-radius: 14px; }
        .meta-label {
            display: flex; align-items: center; gap: 6px; font-size: 10.5px; text-transform: uppercase;
            color: var(--rn-text); font-weight: 800; letter-spacing: 0.7px; margin-bottom: 4px;
        }
        .meta-label .rn-icon { color: var(--rn-purple); }
        .meta-value { font-size: 14.5px; font-weight: 800; color: var(--rn-heading); overflow-wrap: break-word; }

        .guest-details-box {
            background: var(--rn-surface);
            border: 1px solid var(--rn-border);
            border-left: 4px solid var(--rn-purple);
            padding: 14px 16px;
            border-radius: 14px;
        }

        .guest-details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px; font-size: 13.5px; }
        .guest-field-label { display: flex; align-items: center; gap: 5px; color: var(--rn-text); font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .guest-field-val { color: var(--rn-heading); font-weight: 800; overflow-wrap: break-word; }

        .addons-block { margin-top: 16px; padding-top: 14px; border-top: 1px dashed var(--rn-border); }
        .addons-title { font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--rn-text); margin-bottom: 8px; }
        .addons-pills { display: flex; flex-wrap: wrap; gap: 7px; }
        .addon-pill { padding: 4px 11px; border-radius: 999px; background: #f3ebff; border: 1px solid #dfd0ff; color: var(--rn-purple-dark); font-size: 12px; font-weight: 700; }

        .qr-stub-col {
            padding: 28px 22px;
            background: var(--rn-surface);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .qr-box {
            width: 174px;
            height: 174px;
            padding: 10px;
            background: #ffffff;
            border: 1.5px solid var(--rn-border);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 26px rgba(20, 17, 34, 0.08);
        }

        .qr-box svg { width: 150px !important; height: 150px !important; display: block; }

        .qr-caption { margin-top: 14px; font-size: 11px; font-weight: 900; color: var(--rn-purple-dark); letter-spacing: 1.4px; text-transform: uppercase; }
        .qr-code-text { margin-top: 4px; font-size: 12px; font-weight: 800; color: var(--rn-text); letter-spacing: 0.6px; word-break: break-all; }

        .voucher-footer {
            background: #faf9fd;
            padding: 18px 30px;
            border-top: 1px solid var(--rn-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-verify-note { display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; color: var(--rn-text); }
        .footer-verify-note .rn-icon { color: var(--rn-purple); }

        .total-price-wrap { display: flex; align-items: baseline; gap: 8px; }
        .total-label { font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--rn-text); }
        .total-price { font-size: 22px; font-weight: 900; color: var(--rn-purple-dark); }

        @media (max-width: 760px) {
            body { padding: 20px 14px 40px; }
            .action-bar { flex-direction: column; align-items: stretch; }
            .action-group { display: grid; grid-template-columns: 1fr 1fr; }
            .rn-btn { width: 100%; padding: 11px 14px; font-size: 13.5px; }
            .voucher-header { padding: 22px 20px; }
            .voucher-body { grid-template-columns: 1fr; }
            .voucher-main-col { padding: 22px 20px; border-right: none; border-bottom: 2px dashed var(--rn-border); }
            .voucher-main-col::before { top: auto; right: auto; left: -11px; bottom: -12px; }
            .voucher-main-col::after { right: -11px; bottom: -12px; }
            .meta-grid, .guest-details-grid { grid-template-columns: 1fr; }
            .qr-stub-col { padding: 24px 20px; }
            .voucher-footer { padding: 16px 20px; flex-direction: column; align-items: flex-start; }
        }

        @media print {
            body { background: #ffffff !important; padding: 0 !important; }
            .action-bar { display: none !important; }
            .ticket-voucher { box-shadow: none !important; border: 1.5px solid #d5c4fa !important; }
            .voucher-main-col::before, .voucher-main-col::after { background: #ffffff !important; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="index.php" class="rn-btn rn-btn-secondary">
            <?= rnIcon('arrow-left', 'rn-icon rn-icon-sm') ?>
            <span>Return to Home</span>
        </a>

        <div class="action-group">
            <button type="button" id="downloadPdfBtn" class="rn-btn">
                <?= rnIcon('download', 'rn-icon rn-icon-sm') ?>
                <span id="downloadBtnText">Download PDF Pass</span>
            </button>

            <button type="button" onclick="window.print();" class="rn-btn rn-btn-secondary">
                <?= rnIcon('printer', 'rn-icon rn-icon-sm') ?>
                <span>Print</span>
            </button>
        </div>
    </div>

    <div class="voucher-export-wrap">
        <div class="ticket-voucher" id="ticketVoucherCard">

            <div class="voucher-header">
                <div class="brand-row">
                    <span class="brand-logo-pill">
                        <img id="voucherLogoImg" src="assets/design/logo.png" alt="Rave Nations">
                    </span>
                    <div class="brand-title">RAVE NATIONS <span>// E-TICKET</span></div>
                </div>

                <div class="header-badges">
                    <span class="order-id-badge"><?= rnEscape($order['order_id']) ?></span>
                    <span class="status-badge <?= $isPaid ? 'status-paid' : 'status-pending' ?>">
                        <?= rnEscape($statusText) ?>
                    </span>
                </div>
            </div>

            <div class="voucher-body">
                <div class="voucher-main-col">
                    <span class="guest-pass-pill">
                        <?= strtoupper(rnEscape($pdfPayload['guestType'])) ?>
                    </span>

                    <h1 class="event-main-title"><?= rnEscape($order['event_name']) ?></h1>
                    <p class="event-tier-line">Tier: <?= rnEscape($order['package_name']) ?> (Qty: <?= $quantity ?>)</p>

                    <div class="meta-grid">
                        <div class="meta-box">
                            <div class="meta-label">
                                <?= rnIcon('calendar', 'rn-icon rn-icon-sm') ?>
                                <span>Event Date &amp; Time</span>
                            </div>
                            <div class="meta-value"><?= rnEscape($formattedDate) ?> · <?= rnEscape($formattedTime) ?></div>
                        </div>

                        <div class="meta-box">
                            <div class="meta-label">
                                <?= rnIcon('pin', 'rn-icon rn-icon-sm') ?>
                                <span>Venue / Location</span>
                            </div>
                            <div class="meta-value"><?= rnEscape($order['location']) ?></div>
                        </div>
                    </div>

                    <div class="guest-details-box">
                        <div class="guest-details-grid">
                            <div>
                                <span class="guest-field-label"><?= rnIcon('user', 'rn-icon rn-icon-sm') ?> Holder Name</span>
                                <span class="guest-field-val"><?= rnEscape($order['first_name'] . ' ' . $order['last_name']) ?></span>
                            </div>
                            <div>
                                <span class="guest-field-label"><?= rnIcon('id-card', 'rn-icon rn-icon-sm') ?> <?= $pdfPayload['idLabel'] ?></span>
                                <span class="guest-field-val"><?= rnEscape($order['nic_passport']) ?></span>
                            </div>
                            <div>
                                <span class="guest-field-label"><?= rnIcon('mail', 'rn-icon rn-icon-sm') ?> Email</span>
                                <span class="guest-field-val"><?= rnEscape($order['email']) ?></span>
                            </div>
                            <div>
                                <span class="guest-field-label"><?= rnIcon('phone', 'rn-icon rn-icon-sm') ?> Phone</span>
                                <span class="guest-field-val"><?= rnEscape($order['phone']) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($addonsList)): ?>
                        <div class="addons-block">
                            <div class="addons-title">Included Partner Experiences</div>
                            <div class="addons-pills">
                                <?php foreach ($addonsList as $addon): ?>
                                    <span class="addon-pill"><?= rnEscape($addon['package_name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="qr-stub-col">
                    <div class="qr-box" id="voucherQrCode" aria-label="Ticket Verification QR Code"></div>
                    <div class="qr-caption">Scan At Gate Checkpoint</div>
                    <div class="qr-code-text"><?= rnEscape($order['order_id']) ?></div>
                </div>
            </div>

            <div class="voucher-footer">
                <div class="footer-verify-note">
                    <?= rnIcon('shield', 'rn-icon rn-icon-sm') ?>
                    <span>Present this QR code and your matching ID at the venue gate.</span>
                </div>

                <div class="total-price-wrap">
                    <span class="total-label">Total Paid</span>
                    <span class="total-price">LKR <?= number_format((float) $order['amount'], 2) ?></span>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ticketData = <?php echo json_encode($pdfPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            const qrContainer = document.getElementById('voucherQrCode');
            const downloadBtn = document.getElementById('downloadPdfBtn');
            const downloadBtnText = document.getElementById('downloadBtnText');

            // 1. Generate Local Standalone SVG QR Code
            function renderQRCode() {
                if (!qrContainer) return;
                qrContainer.innerHTML = '';

                try {
                    const qr = qrcode(0, 'H');
                    qr.addData(ticketData.orderId);
                    qr.make();
                    const svgString = qr.createSvgTag({ scalable: true, margin: 0 });
                    qrContainer.innerHTML = svgString;
                } catch (err) {
                    qrContainer.innerHTML = '<span style="font-size:11px; color:#7c2ae8; font-weight:bold;"><?= rnEscape($order['order_id']) ?></span>';
                }
            }

            renderQRCode();

            function loadLogoDataUrl() {
                return new Promise((resolve) => {
                    const imgEl = document.getElementById('voucherLogoImg');
                    if (!imgEl || !imgEl.complete || imgEl.naturalWidth === 0) {
                        const tmp = new Image();
                        tmp.onload = () => convertImg(tmp, resolve);
                        tmp.onerror = () => resolve(null);
                        tmp.src = 'assets/design/logo.png';
                    } else {
                        convertImg(imgEl, resolve);
                    }
                });
            }

            function convertImg(img, resolve) {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve({
                        dataUrl: canvas.toDataURL('image/png'),
                        ratio: img.naturalWidth / img.naturalHeight
                    });
                } catch (e) {
                    resolve(null);
                }
            }

            function getQrDataUrl() {
                return new Promise((resolve) => {
                    const svgEl = qrContainer ? qrContainer.querySelector('svg') : null;
                    if (!svgEl) {
                        resolve(null);
                        return;
                    }
                    try {
                        const svgData = new XMLSerializer().serializeToString(svgEl);
                        const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
                        const DOMURL = window.URL || window.webkitURL || window;
                        const url = DOMURL.createObjectURL(svgBlob);
                        const img = new Image();
                        img.onload = function () {
                            const canvas = document.createElement('canvas');
                            canvas.width = 300;
                            canvas.height = 300;
                            const ctx = canvas.getContext('2d');
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                            DOMURL.revokeObjectURL(url);
                            resolve(canvas.toDataURL('image/png'));
                        };
                        img.onerror = function () {
                            resolve(null);
                        };
                        img.src = url;
                    } catch (e) {
                        resolve(null);
                    }
                });
            }

            // 2. Crisp Vector PDF Generator via jsPDF
            if (downloadBtn) {
                downloadBtn.addEventListener('click', async function () {
                    if (!window.jspdf || !window.jspdf.jsPDF) {
                        window.print();
                        return;
                    }

                    downloadBtn.disabled = true;
                    downloadBtnText.textContent = 'Generating PDF...';

                    try {
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

                        const pageW = 210;
                        const pageH = 297;
                        const cardX = 14;
                        const cardY = 18;
                        const cardW = 182;

                        doc.setFillColor(246, 245, 250);
                        doc.rect(0, 0, pageW, pageH, 'F');

                        const hasAddons = Array.isArray(ticketData.addons) && ticketData.addons.length > 0;
                        const addonsText = hasAddons ? ticketData.addons.join('  •  ') : '';
                        const addonLines = hasAddons ? doc.splitTextToSize(addonsText, 108) : [];
                        const extraH = hasAddons ? (10 + addonLines.length * 4.5) : 0;

                        const headerH = 28;
                        const bodyH = Math.max(98, 88 + extraH);
                        const footerH = 16;
                        const cardH = headerH + bodyH + footerH;

                        doc.setFillColor(255, 255, 255);
                        doc.setDrawColor(225, 220, 240);
                        doc.setLineWidth(0.5);
                        doc.roundedRect(cardX, cardY, cardW, cardH, 6, 6, 'FD');

                        doc.setFillColor(14, 11, 24);
                        doc.roundedRect(cardX, cardY, cardW, headerH, 6, 6, 'F');
                        doc.rect(cardX, cardY + headerH - 6, cardW, 6, 'F');

                        doc.setFillColor(124, 42, 232);
                        doc.rect(cardX, cardY + headerH - 1.2, cardW, 1.2, 'F');

                        const logoInfo = await loadLogoDataUrl();
                        let textStartX = cardX + 8;
                        if (logoInfo) {
                            const pillH = 10;
                            const imgH = 6.5;
                            const imgW = imgH * logoInfo.ratio;
                            const pillW = imgW + 6;
                            doc.setFillColor(255, 255, 255);
                            doc.roundedRect(cardX + 8, cardY + 9, pillW, pillH, 2.5, 2.5, 'F');
                            doc.addImage(logoInfo.dataUrl, 'PNG', cardX + 11, cardY + 10.75, imgW, imgH);
                            textStartX = cardX + 8 + pillW + 4;
                        }

                        doc.setFont('helvetica', 'bold');
                        doc.setFontSize(12);
                        doc.setTextColor(255, 255, 255);
                        doc.text('RAVE NATIONS', textStartX, cardY + 13.5);
                        doc.setFontSize(8);
                        doc.setTextColor(216, 180, 254);
                        doc.text('OFFICIAL EVENT E-TICKET', textStartX, cardY + 18);

                        doc.setFontSize(8);
                        const orderText = 'ORDER: ' + ticketData.orderId;
                        const orderW = doc.getTextWidth(orderText) + 8;
                        const statusW = doc.getTextWidth(ticketData.status) + 8;

                        const statusX = cardX + cardW - 8 - statusW;
                        const orderX = statusX - 3 - orderW;

                        doc.setFillColor(40, 32, 68);
                        doc.roundedRect(orderX, cardY + 10, orderW, 7.5, 3.5, 3.5, 'F');
                        doc.setTextColor(243, 232, 255);
                        doc.text(orderText, orderX + 4, cardY + 14.9);

                        if (ticketData.isPaid) {
                            doc.setFillColor(22, 163, 74);
                            doc.setTextColor(255, 255, 255);
                        } else {
                            doc.setFillColor(234, 179, 8);
                            doc.setTextColor(20, 17, 34);
                        }
                        doc.roundedRect(statusX, cardY + 10, statusW, 7.5, 3.5, 3.5, 'F');
                        doc.text(ticketData.status, statusX + 4, cardY + 14.9);

                        const bodyY = cardY + headerH;
                        const splitX = cardX + 122;

                        doc.setFillColor(250, 249, 253);
                        doc.rect(splitX, bodyY, cardW - 122, bodyH, 'F');

                        doc.setDrawColor(215, 210, 232);
                        doc.setLineWidth(0.4);
                        doc.setLineDashPattern([2, 2], 0);
                        doc.line(splitX, bodyY + 3, splitX, bodyY + bodyH - 3);
                        doc.setLineDashPattern([], 0);

                        const leftX = cardX + 9;
                        let curY = bodyY + 10;

                        doc.setFillColor(243, 235, 255);
                        const passW = doc.getTextWidth(ticketData.guestType) + 8;
                        doc.roundedRect(leftX, curY - 4.2, passW, 6, 3, 3, 'F');
                        doc.setFontSize(7.5);
                        doc.setFont('helvetica', 'bold');
                        doc.setTextColor(91, 33, 182);
                        doc.text(ticketData.guestType, leftX + 4, curY);

                        curY += 8;

                        doc.setFontSize(18);
                        doc.setTextColor(20, 17, 34);
                        const titleLines = doc.splitTextToSize(ticketData.eventName, 108);
                        doc.text(titleLines, leftX, curY);
                        curY += titleLines.length * 6.5;

                        doc.setFontSize(10.5);
                        doc.setTextColor(124, 42, 232);
                        doc.text('Tier: ' + ticketData.tierName, leftX, curY);
                        curY += 7;

                        const boxW = 52;
                        const boxH = 14.5;

                        doc.setFillColor(250, 249, 253);
                        doc.setDrawColor(231, 228, 242);
                        doc.roundedRect(leftX, curY, boxW, boxH, 2.5, 2.5, 'FD');
                        doc.roundedRect(leftX + boxW + 4, curY, boxW, boxH, 2.5, 2.5, 'FD');

                        doc.setFontSize(7);
                        doc.setTextColor(93, 91, 112);
                        doc.text('EVENT DATE & TIME', leftX + 3.5, curY + 5);
                        doc.text('VENUE / LOCATION', leftX + boxW + 7.5, curY + 5);

                        doc.setFontSize(9);
                        doc.setTextColor(20, 17, 34);
                        doc.text(doc.splitTextToSize(ticketData.dateTime, boxW - 6)[0] || '', leftX + 3.5, curY + 10.5);
                        doc.text(doc.splitTextToSize(ticketData.location, boxW - 6)[0] || '', leftX + boxW + 7.5, curY + 10.5);

                        curY += boxH + 5;

                        const guestBoxW = 108;
                        const guestBoxH = 27;
                        doc.setFillColor(250, 249, 253);
                        doc.setDrawColor(231, 228, 242);
                        doc.roundedRect(leftX, curY, guestBoxW, guestBoxH, 2.5, 2.5, 'FD');
                        doc.setFillColor(124, 42, 232);
                        doc.rect(leftX, curY + 1.5, 1.5, guestBoxH - 3, 'F');

                        const col1X = leftX + 4.5;
                        const col2X = leftX + 56;

                        doc.setFontSize(6.8);
                        doc.setTextColor(93, 91, 112);
                        doc.text('HOLDER NAME', col1X, curY + 5.5);
                        doc.text(ticketData.idLabel.toUpperCase(), col2X, curY + 5.5);
                        doc.text('EMAIL', col1X, curY + 16.5);
                        doc.text('PHONE', col2X, curY + 16.5);

                        doc.setFontSize(8.8);
                        doc.setTextColor(20, 17, 34);
                        doc.text(doc.splitTextToSize(ticketData.holderName, 48)[0] || '', col1X, curY + 10.5);
                        doc.text(doc.splitTextToSize(ticketData.nicPassport, 48)[0] || '', col2X, curY + 10.5);
                        doc.text(doc.splitTextToSize(ticketData.email, 48)[0] || '', col1X, curY + 21.5);
                        doc.text(doc.splitTextToSize(ticketData.phone, 48)[0] || '', col2X, curY + 21.5);

                        curY += guestBoxH + 6;

                        if (hasAddons) {
                            doc.setFontSize(7.2);
                            doc.setTextColor(91, 33, 182);
                            doc.text('INCLUDED PARTNER EXPERIENCES', leftX, curY);
                            curY += 4.5;
                            doc.setFontSize(8.5);
                            doc.setTextColor(20, 17, 34);
                            doc.text(addonLines, leftX, curY);
                        }

                        const stubCenterX = splitX + (cardW - 122) / 2;
                        const qrBoxSize = 42;
                        const qrBoxX = stubCenterX - qrBoxSize / 2;
                        const qrBoxY = bodyY + (bodyH - qrBoxSize - 14) / 2;

                        doc.setFillColor(255, 255, 255);
                        doc.setDrawColor(225, 220, 240);
                        doc.roundedRect(qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 3.5, 3.5, 'FD');

                        const qrDataUrl = await getQrDataUrl();
                        if (qrDataUrl) {
                            doc.addImage(qrDataUrl, 'PNG', qrBoxX + 3, qrBoxY + 3, qrBoxSize - 6, qrBoxSize - 6);
                        }

                        doc.setFontSize(7.5);
                        doc.setFont('helvetica', 'bold');
                        doc.setTextColor(91, 33, 182);
                        doc.text('SCAN AT GATE CHECKPOINT', stubCenterX, qrBoxY + qrBoxSize + 6.5, { align: 'center' });

                        doc.setFontSize(8);
                        doc.setTextColor(93, 91, 112);
                        doc.text(ticketData.orderId, stubCenterX, qrBoxY + qrBoxSize + 11.5, { align: 'center' });

                        const footY = bodyY + bodyH;
                        doc.setFillColor(250, 249, 253);
                        doc.rect(cardX + 0.5, footY, cardW - 1, footerH - 3, 'F');
                        doc.roundedRect(cardX, footY, cardW, footerH, 6, 6, 'S');
                        doc.setDrawColor(231, 228, 242);
                        doc.line(cardX, footY, cardX + cardW, footY);

                        doc.setFontSize(8);
                        doc.setFont('helvetica', 'normal');
                        doc.setTextColor(93, 91, 112);
                        doc.text('Present this QR code and your matching ID at the venue gate.', cardX + 9, footY + 9.5);

                        doc.setFont('helvetica', 'bold');
                        const totalTxt = ticketData.amount;
                        doc.setFontSize(12);
                        const totalW = doc.getTextWidth(totalTxt);
                        doc.setTextColor(91, 33, 182);
                        doc.text(totalTxt, cardX + cardW - 9, footY + 10, { align: 'right' });

                        doc.setFontSize(7.5);
                        doc.setTextColor(93, 91, 112);
                        doc.text('TOTAL PAID:', cardX + cardW - 9 - totalW - 2.5, footY + 9.7, { align: 'right' });

                        doc.setFont('helvetica', 'normal');
                        doc.setFontSize(8);
                        doc.setTextColor(135, 132, 152);
                        doc.text('Rave Nations Sri Lanka  •  Official Digital Ticket Voucher  •  Valid for Single Entry', pageW / 2, cardY + cardH + 10, { align: 'center' });

                        const safeFileName = 'RaveNations-Pass-' + ticketData.orderId.replace(/[^A-Za-z0-9_-]/g, '') + '.pdf';
                        doc.save(safeFileName);
                    } catch (err) {
                        window.print();
                    } finally {
                        downloadBtn.disabled = false;
                        downloadBtnText.textContent = 'Download PDF Pass';
                    }
                });
            }
        });
    </script>
</body>
</html>