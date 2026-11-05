<?php
declare(strict_types=1);
$pageTitle = "Privacy Policy | Rave Nations";
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
        <h1>Privacy Policy</h1>
        <div class="policy-date">Effective Date: October 29, 2026 | Website: https://ravenations.lk</div>
        
        <p>At RAVE NATIONS MIDNIGHT ENTERTAINMENT (PVT) LTD, we are committed to protecting the privacy and security of your personal data. This Privacy Policy outlines how we collect, use, and protect your information when you visit our website or purchase event tickets through our platform.</p>

        <h2>1. Information We Collect</h2>
        <p>When you purchase a ticket or register on our platform, we may collect the following personal details:</p>
        <ul>
            <li>Full Name</li>
            <li>Email Address</li>
            <li>Contact Phone Number</li>
            <li>Billing Address & Payment Transaction Status (Note: We do not store full credit card details on our servers; payments are processed securely through our payment gateway provider, PayHere).</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use your information to:</p>
        <ul>
            <li>Generate, issue, and deliver your digital QR code event tickets.</li>
            <li>Verify attendee identity and ticket validity at the event entrance.</li>
            <li>Send transaction confirmations, ticket updates, or event cancellation notices.</li>
            <li>Respond to customer support inquiries and prevent fraudulent activity.</li>
        </ul>

        <h2>3. Data Protection and Security</h2>
        <p>We implement strict technical and security measures to protect your personal information from unauthorized access, loss, or misuse. Ticket validation QR codes are dynamically generated and encrypted to prevent duplication or unauthorized ticket reselling.</p>

        <h2>4. Third-Party Sharing</h2>
        <p>We do not sell, trade, or rent your personal data to third parties. We only share necessary details with authorized service providers, such as payment gateway operators (PayHere) and event management staff, strictly for processing payments and verifying entrance at the event.</p>

        <h2>5. Contact Us</h2>
        <p>If you have any questions regarding this Privacy Policy, please contact us:</p>
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