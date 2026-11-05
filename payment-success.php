<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

function rnEscape(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$orderId = isset($_GET['order_id']) ? rnEscape($_GET['order_id']) : '';

// Optional: Fetch order & ticket status from database
$orderStatus = 'Processing';
if (!empty($orderId)) {
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if ($order) {
        $orderStatus = $order['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful | Rave Nations</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/footer.css">
    <style>
        body { background: #0c0c10; color: #ffffff; font-family: "Titillium Web", sans-serif; text-align: center; }
        .success-card { max-width: 580px; margin: 80px auto; padding: 40px; background: #14141c; border: 1px solid #282838; border-radius: 18px; box-shadow: 0 0 40px rgba(138, 43, 226, 0.2); }
        .success-badge { font-size: 48px; color: #22c55e; margin-bottom: 10px; }
        .order-tag { font-weight: 700; color: #8a2be2; background: rgba(138, 43, 226, 0.15); border: 1px solid rgba(138, 43, 226, 0.4); padding: 8px 16px; border-radius: 8px; display: inline-block; margin: 15px 0; }
        
        /* 3D Ticket Container Placeholder */
        .ticket-3d-preview { width: 100%; height: 200px; margin: 25px 0; background: linear-gradient(135deg, #1f1c2c, #928dab); border-radius: 14px; border: 1px dashed #8a2be2; display: flex; flex-direction: column; align-items: center; justify-content: center; transform: perspective(800px) rotateY(-5deg) rotateX(5deg); transition: transform 0.4s ease; }
        .ticket-3d-preview:hover { transform: perspective(800px) rotateY(0deg) rotateX(0deg); }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="success-badge">✓</div>
        <span class="rn-section-label" style="color: #8a2be2;">PAYMENT VERIFIED</span>
        <h1 style="font-size: 32px; font-weight: 900; margin: 10px 0;">YOU ARE <span>IN!</span></h1>
        
        <p style="color: #a0a0b0; font-size: 16px;">
            Your payment via PayHere Sandbox was successful.
        </p>

        <?php if (!empty($orderId)): ?>
            <div class="order-tag">Order Reference: <?= $orderId ?></div>
        <?php endif; ?>

        <!-- 3D Design Preview Container -->
        <div class="ticket-3d-preview">
            <span style="font-weight: 900; font-size: 20px; letter-spacing: 2px;">RAVE NATIONS PASS</span>
            <span style="font-size: 12px; color: #d0d0e0; margin-top: 6px;">[ 3D TICKET PREVIEW MODE ]</span>
        </div>

        <div>
            <a href="index.php" class="rn-featured-explore-btn" style="text-decoration: none; display: inline-block; padding: 12px 28px; background: #8a2be2; color: #fff; border-radius: 8px; font-weight: 700;">Return to Home</a>
        </div>
    </div>

    <?php 
    if (file_exists(__DIR__ . '/includes/footer.php')) {
        require_once __DIR__ . '/includes/footer.php';
    }
    ?>
</body>
</html>