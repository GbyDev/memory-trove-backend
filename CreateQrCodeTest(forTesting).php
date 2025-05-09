<?php
require_once './phpqrcode/qrlib.php';

// Set path
$filename = __DIR__ . '/test_qr.png';
$url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

// Clean output buffer just in case
if (ob_get_length()) ob_end_clean();
ob_start();

// Generate QR
QRcode::png($url, $filename, QR_ECLEVEL_L, 10);
ob_end_clean();

// Check if file exists and is not empty
if (file_exists($filename) && filesize($filename) > 0) {
    echo "✅ QR code generated: $filename (" . filesize($filename) . " bytes)";
} else {
    echo "❌ Failed to generate QR code, or file is empty.";
}