<?php
// Start a session without loading app header to avoid HTML/Smarty side effects
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// generate simple captcha text
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$_SESSION['captcha_code'] = $code;

// output SVG to avoid GD dependency
header('Content-Type: image/svg+xml');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
$w = 140; $h = 48;
$bg = '#f8f9fa';
$noise = '';
for ($i = 0; $i < 8; $i++) {
    $x1 = random_int(0, $w); $y1 = random_int(0, $h);
    $x2 = random_int(0, $w); $y2 = random_int(0, $h);
    $noiseColor = sprintf('#%06X', random_int(0x666666, 0xAAAAAA));
    $noise .= '<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="'.$noiseColor.'" stroke-width="1" opacity="0.4" />';
}

$chars = '';
$x = 12;
for ($i = 0; $i < strlen($code); $i++) {
    $angle = random_int(-20, 20);
    $y = random_int(28, 40);
    $textColor = sprintf('#%06X', random_int(0x111111, 0x333333));
    $chars .= '<text x="'.$x.'" y="'.$y.'" transform="rotate('.$angle.' '.$x.' '.$y.')" font-family="Verdana, Arial" font-size="26" fill="'.$textColor.'">'.htmlspecialchars($code[$i]).'</text>';
    $x += 24;
}

echo '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'">'
    .'<rect x="0" y="0" width="100%" height="100%" fill="'.$bg.'"/>'
    .$noise
    .$chars
    .'</svg>';

