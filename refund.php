<?php
declare(strict_types=1);
$pageTitle = "Return & Refund Policy | Rave Nations";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/footer.css">
    <style>
        body { background: #ffffff; color: #111114; font-family: "Titillium Web", sans-serif; margin: 0; }
        .policy-container { max-width: 900px; margin: 60px auto; padding: 0 30px 80px; }
        .policy-container h1 { font-size: 36px; font-weight: 900; color: #111114; margin-bottom: 10px; }
        .policy-date { font-size: 14px; color: var(--home-text); margin-bottom: 40px; font-weight: 600; }
        .policy-container h2 { font-size: 20px; font-weight: 800; color: #7237b6; margin-top: 35px; margin-bottom: 15px; }
        .policy-container p, .policy-container li { font-size: 16px; line-height: 1.8; color: #4b4b56; margin-bottom: 12px; }
        .policy-container ul { padding-left: 20px; margin-bottom: 20px; }
        .back-home { display: inline-block; margin-bottom: 30px; font-weight: 700; color: #7237b6; text-decoration: none; }
        .back-home:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="policy-container">
        <a href="index.php" class="back-home">← Back to Home</a>
        <h1>Return & Refund Policy</h1>
        <div class="policy-date">Effective Date: October 29, 2026 | Website: https://ravenations.lk</div>
        
        <p>Thank you for purchasing event tickets through RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD. Please review our refund and cancellation policy regarding ticket purchases below.</p>

        <h2>1. No Refund / Non-Transferable Policy</h2>
        <p>All ticket sales purchased through ravenations.lk are final, non-refundable, and non-exchangeable.</p>
        <p>Once a ticket order is completed and a digital QR code ticket is issued, no refunds will be granted for non-attendance, change of personal plans, or incorrect selection during the purchase process.</p>

        <h2>2. Event Cancellation or Postponement</h2>
        <ul>
            <li><strong>Official Event Cancellation:</strong> If an event is officially canceled by RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD or the event host, ticket holders will be notified via email or phone, and a full refund or equivalent ticket credit will be processed.</li>
            <li><strong>Postponement / Rescheduling:</strong> If an event is postponed or rescheduled, purchased tickets will automatically remain valid for the rescheduled date.</li>
        </ul>

        <h2>3. Refund Processing Time</h2>
        <p>In the rare case that a refund is authorized due to an event cancellation:</p>
        <ul>
            <li>Refunds will be processed back to the original payment method (bank account/card) used during the purchase via PayHere.</li>
            <li>Please allow 5 to 10 working days for the refunded amount to reflect in your account, depending on your card issuer or banking institution.</li>
        </ul>

        <h2>4. Entry Refusal</h2>
        <p>No refunds will be granted if an attendee is refused entry or evicted from an event due to failure to present a valid QR code ticket, failure to meet event age requirements, or violation of venue safety rules.</p>

        <h2>5. Contact Us</h2>
        <p>For any inquiries regarding ticket deliveries or event cancellations, please contact our support team:</p>
        <ul>
            <li><strong>Company Name:</strong> RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD</li>
            <li><strong>Address:</strong> No. 6, Sumanasara Road, Welegoda, Matara, Sri Lanka</li>
            <li><strong>Email:</strong> support@ravenations.lk</li>
            <li><strong>Phone:</strong> 0412238497</li>
        </ul>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>