<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate random string
$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
$captcha_string = '';
for ($i = 0; $i < 6; $i++) {
    $captcha_string .= $chars[rand(0, strlen($chars) - 1)];
}

// Store the string in session
$_SESSION['captcha_code'] = $captcha_string;
session_write_close(); // Ensure session is written

// Set content type to HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        .captcha-text {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            padding: 10px;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
            border-radius: 5px;
            user-select: none;
            display: inline-block;
            position: relative;
        }
        .captcha-text::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(0,0,0,0.05) 2px,
                rgba(0,0,0,0.05) 4px
            );
        }
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50px;
            background: transparent;
        }
    </style>
</head>
<body>
    <div class="captcha-text">
        <?php 
            $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6', '#34495e'];
            for ($i = 0; $i < strlen($captcha_string); $i++) {
                $color = $colors[rand(0, count($colors)-1)];
                echo '<span style="color: '.$color.'; transform: rotate('.rand(-5, 5).'deg) translateY('.rand(-2, 2).'px) display: inline-block;">'
                    .htmlspecialchars($captcha_string[$i]).'</span>';
            }
        ?>
    </div>
</body>
</html> 