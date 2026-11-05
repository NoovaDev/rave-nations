<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

// Load PayHere configuration
$payhereConfig = require __DIR__ . '/config/payhere.php';

// Capture POST parameters sent from PayHere Sandbox server
$merchant_id      = $_POST['merchant_id'] ?? '';
$order_id         = $_POST['order_id'] ?? '';
$payhere_amount   = $_POST['payhere_amount'] ?? '';
$payhere_currency = $_POST['payhere_currency'] ?? '';
$status_code      = $_POST['status_code'] ?? '';
$md5sig           = $_POST['md5sig'] ?? '';

// Generate local MD5 hash for verification using config secret
$local_md5sig = strtoupper(
    md5(
        $merchant_id . 
        $order_id . 
        $payhere_amount . 
        $payhere_currency . 
        $status_code . 
        strtoupper(md5($payhereConfig['merchant_secret']))
    )
);

// Verify signature authenticity and check for success status (status_code 2 = Success)
if (($local_md5sig === $md5sig) && ($status_code == "2")) {
    try {
        // Update order status in database
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Paid' WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        http_response_code(200);
        exit('SUCCESS');
    } catch (Exception $e) {
        error_log("Payment DB Update Error: " . $e->getMessage());
        http_response_code(500);
        exit('DB_ERROR');
    }
} else {
    http_response_code(400);
    exit('VERIFICATION_FAILED');
}