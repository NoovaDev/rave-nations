<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
$payhereConfig = require __DIR__ . '/config/payhere.php';

function rnEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rnIcon(string $name, string $class = 'rn-icon'): string
{
    $icons = [
        'arrow-left' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
        'globe'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'ticket'     => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
        'user'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'id-card'    => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2"/><line x1="14" y1="10" x2="18" y2="10"/><line x1="14" y1="14" x2="18" y2="14"/></svg>',
        'mail'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
        'phone'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'calendar'   => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'pin'        => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'lock'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
        'arrow-up-r' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>',
    ];

    return $icons[$name] ?? '';
}

$packageId = isset($_REQUEST['package_id']) ? (int) $_REQUEST['package_id'] : 0;
$quantity  = isset($_REQUEST['quantity']) ? max(1, (int) $_REQUEST['quantity']) : 1;

// Fetch Main Ticket Details
$stmt = $pdo->prepare("
    SELECT ep.*, e.event_name, e.event_date, e.location, e.poster_path
    FROM event_packages ep
    JOIN events e ON ep.event_id = e.id
    WHERE ep.id = ?
");
$stmt->execute([$packageId]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Invalid ticket package selected.");
}

$unitPrice   = (float) $package['ticket_price'];
$totalAmount = $unitPrice * $quantity;

// --- UPDATED: SHORT ORDER ID GENERATION ---
$orderId  = "RN-TKT-" . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 4));
$amount   = number_format($totalAmount, 2, '.', '');
$currency = $payhereConfig['currency'];
$itemName = $package['event_name'] . " - " . $package['package_name'] . " (Qty: {$quantity})";

// --- PROCESS FORM SUBMISSION & SAVE ORDER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName   = trim($_POST['first_name'] ?? '');
    $lastName    = trim($_POST['last_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $nicPassport = trim($_POST['nic_passport'] ?? '');
    $nationality = trim($_POST['nationality'] ?? 'Sri Lanka');
    $quantity    = max(1, (int) ($_POST['quantity'] ?? 1));
    
    // Recalculate total amount in case quantity changed via POST
    $totalAmount = $unitPrice * $quantity;
    $amount = number_format($totalAmount, 2, '.', '');

    try {
        $insertStmt = $pdo->prepare("
            INSERT INTO orders (
                order_id, event_id, package_id, quantity, first_name, last_name, 
                email, phone, amount, currency, nic_passport, status, created_at, nationality
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), ?)
        ");
        
        $insertStmt->execute([
            $orderId,
            $package['event_id'],
            $packageId,
            $quantity,
            $firstName,
            $lastName,
            $email,
            $phone,
            $amount,
            $currency,
            $nicPassport,
            $nationality
        ]);

    } catch (Exception $e) {
        die("Order processing failed: " . $e->getMessage());
    }

    $hash = strtoupper(
        md5(
            $payhereConfig['merchant_id'] .
            $orderId .
            $amount .
            $currency .
            strtoupper(md5($payhereConfig['merchant_secret']))
        )
    );
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Redirecting to PayHere...</title></head>
    <body onload="document.forms['payhere_form'].submit();">
        <p style="text-align:center; font-family:sans-serif; margin-top:50px;">
            Saving order and redirecting to PayHere...
        </p>
        <form name="payhere_form" method="post" action="<?= rnEscape($payhereConfig['checkout_url']) ?>">
            <input type="hidden" name="merchant_id" value="<?= rnEscape($payhereConfig['merchant_id']) ?>">
            <input type="hidden" name="return_url" value="<?= rnEscape($payhereConfig['return_url']) ?>">
            <input type="hidden" name="cancel_url" value="<?= rnEscape($payhereConfig['cancel_url']) ?>">
            <input type="hidden" name="notify_url" value="<?= rnEscape($payhereConfig['notify_url']) ?>">
            <input type="hidden" name="order_id" value="<?= rnEscape($orderId) ?>">
            <input type="hidden" name="items" value="<?= rnEscape($itemName) ?>">
            <input type="hidden" name="currency" value="<?= rnEscape($currency) ?>">
            <input type="hidden" name="amount" value="<?= rnEscape($amount) ?>">
            <input type="hidden" name="first_name" value="<?= rnEscape($firstName) ?>">
            <input type="hidden" name="last_name" value="<?= rnEscape($lastName) ?>">
            <input type="hidden" name="email" value="<?= rnEscape($email) ?>">
            <input type="hidden" name="phone" value="<?= rnEscape($phone) ?>">
            <input type="hidden" name="address" value="Sri Lanka">
            <input type="hidden" name="city" value="Colombo">
            <input type="hidden" name="country" value="<?= rnEscape($nationality) ?>">
            <input type="hidden" name="hash" value="<?= rnEscape($hash) ?>">
        </form>
    </body>
    </html>
    <?php
    exit();
}

$eventTimestamp = strtotime((string) ($package['event_date'] ?? ''));
$formattedEventDate = $eventTimestamp ? date('d M Y', $eventTimestamp) : 'TBA';
$heroBg = !empty($package['poster_path']) ? (string) $package['poster_path'] : 'assets/design/hero.jpg';
$backToEventUrl = "event.php?id=" . (int) $package['event_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0e0b18">
    <title>Checkout | Rave Nations</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style/checkout.css?v=171">
    <link rel="stylesheet" href="style/footer.css?v=76">
</head>
<body>

    <!-- HERO SECTION -->
    <header class="checkout-hero">
        <div class="rn-hero-background" style="background-image: url('<?= rnEscape($heroBg) ?>');"></div>
        <div class="rn-hero-overlay"></div>

        <div class="rn-hero-container">
            <div class="checkout-topline">
                <a href="<?= rnEscape($backToEventUrl) ?>" class="checkout-back-link">
                    <?= rnIcon('arrow-left', 'rn-icon rn-icon-sm') ?>
                    <span>Back to Event</span>
                </a>
            </div>

            <div class="checkout-hero-content">
                <span class="checkout-step-label">SECURE CHECKOUT</span>

                <h1 class="checkout-title"><?= rnEscape($package['event_name']) ?></h1>

                <div class="rn-hero-meta-row">
                    <span class="rn-hero-chip">
                        <?= rnIcon('ticket', 'rn-icon rn-icon-sm') ?>
                        <span><?= rnEscape($package['package_name']) ?></span>
                    </span>

                    <span class="rn-hero-chip">
                        <span>Unit: LKR <?= number_format($unitPrice, 2) ?></span>
                    </span>
                </div>

                <p class="checkout-subtitle">Select your quantity and enter your details to complete your booking.</p>
            </div>
        </div>
    </header>

    <!-- CHECKOUT MAIN WRAPPER -->
    <main class="checkout-main-wrapper">
        <div class="rn-container">
            <div class="checkout-grid">

                <!-- Left: Attendee Form -->
                <section class="checkout-card">
                    <span class="checkout-section-label">ATTENDEE DETAILS</span>
                    <h2 class="checkout-card-heading">Complete Your <span>Order</span></h2>

                    <form method="post" action="">
                        <input type="hidden" name="package_id" value="<?= $packageId ?>">

                        <div class="form-group">
                            <label for="quantity">
                                <?= rnIcon('ticket', 'rn-icon rn-icon-sm') ?>
                                <span>Number of Tickets *</span>
                            </label>
                            <select name="quantity" id="quantity" class="form-group input" style="width:100%; min-height:48px; padding:12px 15px; background:var(--rn-surface); border:1.5px solid var(--rn-border); color:var(--rn-heading); border-radius:12px; font-family:inherit; font-size:15px; font-weight:600;" required>
                                <option value="1" <?= $quantity === 1 ? 'selected' : '' ?>>1 Ticket</option>
                                <option value="2" <?= $quantity === 2 ? 'selected' : '' ?>>2 Tickets</option>
                                <option value="3" <?= $quantity === 3 ? 'selected' : '' ?>>3 Tickets</option>
                                <option value="4" <?= $quantity === 4 ? 'selected' : '' ?>>4 Tickets</option>
                                <option value="5" <?= $quantity === 5 ? 'selected' : '' ?>>5 Tickets</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nationality">
                                <?= rnIcon('globe', 'rn-icon rn-icon-sm') ?>
                                <span>Nationality / Country of Residence *</span>
                            </label>
                            <select name="nationality" id="nationality" class="form-group input" style="width:100%; min-height:48px; padding:12px 15px; background:var(--rn-surface); border:1.5px solid var(--rn-border); color:var(--rn-heading); border-radius:12px; font-family:inherit; font-size:15px; font-weight:600;" required>
                                <option value="Sri Lanka" selected>🇱🇰 Sri Lanka</option>
                                <option value="Australia">🇦🇺 Australia</option>
                                <option value="Canada">🇨🇦 Canada</option>
                                <option value="France">🇫🇷 France</option>
                                <option value="Germany">🇩🇪 Germany</option>
                                <option value="India">🇮🇳 India</option>
                                <option value="United Kingdom">🇬🇧 United Kingdom</option>
                                <option value="United States">🇺🇸 United States</option>
                                <option value="Other">🌍 Other International Country</option>
                            </select>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">
                                    <?= rnIcon('user', 'rn-icon rn-icon-sm') ?>
                                    <span>First Name *</span>
                                </label>
                                <input type="text" id="first_name" name="first_name" autocomplete="given-name" required placeholder="e.g., Sadev">
                            </div>

                            <div class="form-group">
                                <label for="last_name">
                                    <?= rnIcon('user', 'rn-icon rn-icon-sm') ?>
                                    <span>Last Name *</span>
                                </label>
                                <input type="text" id="last_name" name="last_name" autocomplete="family-name" required placeholder="e.g., Induwara">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nic_passport" id="id_label">
                                <?= rnIcon('id-card', 'rn-icon rn-icon-sm') ?>
                                <span>Sri Lankan NIC Number *</span>
                            </label>
                            <input
                                type="text"
                                id="nic_passport"
                                name="nic_passport"
                                required
                                placeholder="e.g., 199512345678 or 951234567V"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <?= rnIcon('mail', 'rn-icon rn-icon-sm') ?>
                                <span>Email Address *</span>
                            </label>
                            <input type="email" id="email" name="email" autocomplete="email" required placeholder="e.g., devnoova@gmail.com">
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                <?= rnIcon('phone', 'rn-icon rn-icon-sm') ?>
                                <span>Phone / WhatsApp Number *</span>
                            </label>
                            <input type="tel" id="phone" name="phone" autocomplete="tel" inputmode="tel" required placeholder="e.g., 0783847145">
                        </div>

                        <div class="terms-group">
                            <input type="checkbox" id="terms_agree" required>
                            <label for="terms_agree">
                                I agree to the Rave Nations
                                <a href="terms.php" target="_blank" rel="noopener noreferrer">Terms &amp; Conditions</a>,
                                <a href="privacy.php" target="_blank" rel="noopener noreferrer">Privacy Policy</a>, and
                                <a href="refund.php" target="_blank" rel="noopener noreferrer">Refund Policy</a>.
                            </label>
                        </div>

                        <button type="submit" class="checkout-submit-btn">
                            <?= rnIcon('lock', 'rn-icon rn-icon-sm') ?>
                            <span>Pay Now · LKR <span id="btn_total_display"><?= number_format($totalAmount, 2) ?></span></span>
                            <?= rnIcon('arrow-up-r', 'rn-icon rn-icon-sm') ?>
                        </button>

                        <div class="checkout-secure-note">
                            <?= rnIcon('lock', 'rn-icon rn-icon-sm') ?>
                            <span>Encrypted checkout powered by PayHere</span>
                        </div>
                    </form>
                </section>

                <!-- Right: Order Summary -->
                <aside class="checkout-card order-summary-card">
                    <span class="checkout-section-label">ORDER SUMMARY</span>
                    <h3 class="summary-event-title"><?= rnEscape($package['event_name']) ?></h3>

                    <div class="summary-event-meta">
                        <span class="summary-meta-chip">
                            <?= rnIcon('calendar', 'rn-icon rn-icon-sm') ?>
                            <span><?= rnEscape($formattedEventDate) ?></span>
                        </span>
                        <?php if (!empty($package['location'])): ?>
                            <span class="summary-meta-chip">
                                <?= rnIcon('pin', 'rn-icon rn-icon-sm') ?>
                                <span><?= rnEscape($package['location']) ?></span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="summary-items-box">
                        <div class="summary-row">
                            <div>
                                <div class="summary-item-name"><?= rnEscape($package['package_name']) ?></div>
                                <span class="summary-item-tag">Main Event Pass (×<span id="summary_qty_display"><?= $quantity ?></span>)</span>
                            </div>
                            <div class="summary-price-col">
                                <span class="summary-item-price">LKR <span id="summary_total_display"><?= number_format($totalAmount, 2) ?></span></span>
                            </div>
                        </div>

                        <hr class="summary-divider">

                        <div class="summary-total">
                            <span class="summary-total-label">Total Due</span>
                            <span class="summary-total-amount">LKR <span id="summary_final_display"><?= number_format($totalAmount, 2) ?></span></span>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <!-- Footer Component -->
    <?php
    if (file_exists(__DIR__ . '/includes/footer.php')) {
        require_once __DIR__ . '/includes/footer.php';
    }
    ?>

    <script>
        const unitPrice = <?= $unitPrice ?>;

        // Update totals dynamically when quantity changes
        document.getElementById('quantity').addEventListener('change', function() {
            const qty = parseInt(this.value) || 1;
            const total = unitPrice * qty;
            const formatted = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            document.getElementById('btn_total_display').textContent = formatted;
            document.getElementById('summary_qty_display').textContent = qty;
            document.getElementById('summary_total_display').textContent = formatted;
            document.getElementById('summary_final_display').textContent = formatted;
        });

        // Toggle ID label based on nationality selection
        document.getElementById('nationality').addEventListener('change', function() {
            const country = this.value;
            const idLabel = document.getElementById('id_label');
            const idInput = document.getElementById('nic_passport');

            if (country === 'Sri Lanka') {
                idLabel.innerHTML = '<?= rnIcon('id-card', 'rn-icon rn-icon-sm') ?> <span>Sri Lankan NIC Number *</span>';
                idInput.placeholder = 'e.g., 199512345678 or 951234567V';
            } else {
                idLabel.innerHTML = '<?= rnIcon('id-card', 'rn-icon rn-icon-sm') ?> <span>Passport / Govt ID Number *</span>';
                idInput.placeholder = 'e.g., P123456789';
            }
        });
    </script>

</body>
</html>