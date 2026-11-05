<?php
declare(strict_types=1);
$pageTitle = "Terms & Conditions | Rave Nations";
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
        <h1>Terms & Conditions</h1>
        <div class="policy-date">Effective Date: October 29, 2026 | Website: https://ravenations.lk</div>
        
        <p>Welcome to ravenations.lk. By purchasing tickets or using our website, you agree to comply with and be bound by the following Terms and Conditions set forth by RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD.</p>

        <h2>1. General Provisions</h2>
        <p>This website is operated by RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD. Throughout the site, the terms "we", "us", and "our" refer to RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD.</p>

        <h2>2. Ticket Issuance & QR Code Verification</h2>
        <p>All tickets sold on this website are issued digitally as e-tickets containing a unique QR code.</p>
        <ul>
            <li>To gain entry to an event, attendees must present a valid QR code ticket (either on a mobile device or as a clear physical printout) at the event entrance.</li>
            <li>Each QR code allows one single scan and entry per ticket. Once scanned and checked in, the QR code becomes invalid for subsequent entry attempts.</li>
            <li>Unlawful duplication, altering, or copying of tickets is strictly prohibited. We are not responsible for tickets obtained through unauthorized third-party sellers.</li>
        </ul>

        <h2>3. Entry Restrictions & Venue Rules</h2>
        <ul>
            <li><strong>Age Limits:</strong> Age restrictions vary depending on the specific event (e.g., 18+, 21+, or all-ages). Any age limit will be clearly specified on the individual event page prior to purchase.</li>
            <li><strong>Identification:</strong> Event management reserves the right to request valid government-issued photo ID (e.g., National Identity Card, Driver's License, or Passport) at the gate to verify age or ticket ownership.</li>
            <li><strong>Right of Admission:</strong> Event organizers and security staff reserve the right to refuse admission or remove any individual whose behavior is deemed disruptive, unsafe, or in violation of venue rules without offering a refund.</li>
        </ul>

        <h2>4. Pricing & Payments</h2>
        <p>All ticket prices are displayed in Sri Lankan Rupees (LKR) and include applicable fees unless stated otherwise.</p>
        <ul>
            <li>Payments must be completed online through our authorized payment portal (PayHere). Orders are only confirmed once payment authorization is complete.</li>
        </ul>

        <h2>5. Governing Law</h2>
        <p>These Terms and Conditions are governed by and construed in accordance with the laws of the Democratic Socialist Republic of Sri Lanka.</p>

        <h2>6. Contact Information</h2>
        <p>For questions regarding our Terms and Conditions, please reach out to us at:</p>
        <ul>
            <li><strong>Address:</strong> No. 6, Sumanasara Road, Welegoda, Matara, Sri Lanka</li>
            <li><strong>Email:</strong> support@ravenations.lk</li>
            <li><strong>Phone:</strong> 0412238497</li>
        </ul>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>