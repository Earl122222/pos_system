<?php
session_start();

// Generate CAPTCHA code
$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
$captcha_string = '';

// Generate a new code that's different from the current one
do {
    $captcha_string = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha_string .= $chars[rand(0, strlen($chars) - 1)];
    }
} while (isset($_SESSION['captcha_code']) && $_SESSION['captcha_code'] === $captcha_string);

// Save the new code in session
$_SESSION['captcha_code'] = $captcha_string;

// Define colors for CAPTCHA characters
$colors = ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6', '#34495e'];

// Generate HTML with colored characters
$output = '';
for ($i = 0; $i < strlen($captcha_string); $i++) {
    $color = $colors[rand(0, count($colors)-1)];
    $rotation = rand(-5, 5);
    $translateY = rand(-2, 2);
    
    $output .= sprintf(
        '<span style="color: %s; transform: rotate(%ddeg) translateY(%dpx); display: inline-block; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); animation: float 2s ease-in-out infinite;">%s</span>',
        $color,
        $rotation,
        $translateY,
        htmlspecialchars($captcha_string[$i])
    );
}

// Clear any output buffer
if (ob_get_length()) ob_clean();

// Set headers to prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

echo $output;
?> 