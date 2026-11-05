<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
$payhereConfig = require __DIR__ . '/config/payhere.php';

function rnEscape(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

$stmt = $pdo->prepare("
    SELECT ep.*, e.event_name, e.event_date, e.location 
    FROM event_packages ep 
    JOIN events e ON ep.event_id = e.id 
    WHERE ep.id = ?
");
$stmt->execute([$packageId]);
$package = $stmt->fetch();

if (!$package) {
    die("Invalid ticket package selected.");
}

$orderId  = "RN-TKT-" . strtoupper(uniqid());
$amount   = number_format((float)$package['ticket_price'], 2, '.', '');
$currency = $payhereConfig['currency'];
$itemName = $package['event_name'] . " - " . $package['package_name'];

// --- PROCESS FORM SUBMISSION & SAVE ORDER FIRST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    // 1. Insert order into DB as Pending (Including event_id)
    $insertStmt = $pdo->prepare("
        INSERT INTO orders (order_id, event_id, package_id, first_name, last_name, email, phone, amount, currency, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
    ");
    
    $insertStmt->execute([
        $orderId,
        $package['event_id'], // <-- Captured directly from the package query
        $packageId,
        $firstName,
        $lastName,
        $email,
        $phone,
        $amount,
        $currency
    ]);

    // 2. Generate MD5 Hash
    $hash = strtoupper(
        md5(
            $payhereConfig['merchant_id'] . 
            $orderId . 
            $amount . 
            $currency . 
            strtoupper(md5($payhereConfig['merchant_secret']))
        )
    );

    // 3. Render auto-submitting form to PayHere
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Redirecting to PayHere...</title></head>
    <body onload="document.forms['payhere_form'].submit();">
        <p style="text-align:center; font-family:sans-serif; margin-top:50px;">
            Saving order and redirecting to PayHere Sandbox...
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
            <input type="hidden" name="city" value="Matara">
            <input type="hidden" name="country" value="Sri Lanka">
            <input type="hidden" name="hash" value="<?= rnEscape($hash) ?>">
        </form>
    </body>
    </html>
    <?php
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Rave Nations</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        body { background: #f7f7f9; color: #111114; font-family: "Titillium Web", sans-serif; }
        .checkout-container { max-width: 600px; margin: 60px auto; background: #ffffff; padding: 40px; border-radius: 18px; border: 1px solid #e9e9ee; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #d1d1d6; border-radius: 8px; box-sizing: border-box; }
    </style>
</head>
<body>

    <div class="checkout-container">
        <h1>COMPLETE YOUR ORDER</h1>
        <form method="post" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required placeholder="Sadev">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required placeholder="Induwara">
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="support@ravenations.lk">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required placeholder="0712345678">
            </div>

            <button type="submit" style="width:100%; padding:14px; background:#8a2be2; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
                Proceed to PayHere Sandbox ↗
            </button>
        </form>
    </div>

</body>
</html>