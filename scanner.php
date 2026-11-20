<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate QR Scanner | Rave Nations</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <!-- Include html5-qrcode library for mobile camera scanning -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background: #0c0c10; color: #ffffff; font-family: "Titillium Web", sans-serif; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; min-height: 100vh; box-sizing: border-box; }
        .scanner-container { width: 100%; max-width: 480px; background: #14141c; border: 1px solid #282838; border-radius: 18px; padding: 25px; box-shadow: 0 0 40px rgba(138, 43, 226, 0.2); text-align: center; box-sizing: border-box; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid #383848; background: #000; margin-top: 15px; }
        #reader video { width: 100% !important; border-radius: 12px; }
        
        .manual-section { margin-top: 25px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; text-align: left; }
        .manual-section label { display: block; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        .manual-input-group { display: flex; gap: 10px; }
        .manual-input { flex: 1; padding: 12px; background: #0c0c10; border: 1px solid #282838; border-radius: 10px; color: #fff; font-family: inherit; font-size: 14px; outline: none; }
        .manual-input:focus { border-color: #8a2be2; }
        .manual-btn { background: #8a2be2; color: #fff; border: none; padding: 0 16px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .manual-btn:hover { background: #9d4edd; }
    </style>
</head>
<body>

    <div class="scanner-container">
        <span class="rn-section-label" style="color: #8a2be2; display: block; margin-bottom: 5px;">GATE CHECK-IN</span>
        <h2 style="font-size: 22px; font-weight: 900; margin: 0;">SCAN TICKET QR</h2>
        <p style="color: #9ca3af; font-size: 13px; margin-top: 5px;">Point your mobile camera at the attendee's ticket pass.</p>

        <!-- Live Camera Viewport -->
        <div id="reader"></div>

        <!-- Manual Fallback Entry Form -->
        <div class="manual-section">
            <label>Or Enter Order ID Manually</label>
            <form action="verify.php" method="get" class="manual-input-group">
                <input type="text" name="order_id" class="manual-input" placeholder="e.g. RN-TKT-..." required>
                <button type="submit" class="manual-btn">Verify</button>
            </form>
        </div>

        <div style="margin-top: 20px;">
            <a href="index.php" style="color: #9ca3af; font-size: 13px; text-decoration: none;">← Return to Home</a>
        </div>
    </div>

    <script>
        let html5QrCode = null;

        function onScanSuccess(decodedText, decodedResult) {
            // If the QR contains a full URL, extract just the order_id or pass the URL directly
            let targetUrl = decodedText;
            if (!decodedText.includes('verify.php')) {
                targetUrl = `verify.php?order_id=${encodeURIComponent(decodedText)}`;
            }

            // Stop scanner and redirect
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    window.location.href = targetUrl;
                }).catch(() => {
                    window.location.href = targetUrl;
                });
            } else {
                window.location.href = targetUrl;
            }
        }

        function onScanFailure(error) {
            // Scanning in progress, ignore routine frame misses
        }

        document.addEventListener('DOMContentLoaded', () => {
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" }, // Automatically selects the phone's back camera
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanFailure
            ).catch((err) => {
                console.warn("Camera access denied or unavailable. Manual entry remains active.");
            });
        });
    </script>
</body>
</html>