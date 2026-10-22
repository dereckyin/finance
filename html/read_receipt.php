<?php

require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');

require_jwt_user();

$key = getenv('GEMINI_API_KEY');
if (!is_string($key) || trim($key) === '') {
    http_response_code(503);
    echo json_encode(array('ok' => false, 'msg' => '尚未設定辨識金鑰'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '請選擇圖片'), JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['image'];
if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '圖片上傳失敗'), JSON_UNESCAPED_UNICODE);
    exit;
}
if (!isset($file['size']) || (int) $file['size'] <= 0 || (int) $file['size'] > 8 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '圖片請小於 8MB'), JSON_UNESCAPED_UNICODE);
    exit;
}

$tmp = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
if ($tmp === '' || !is_uploaded_file($tmp)) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '圖片上傳失敗'), JSON_UNESCAPED_UNICODE);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmp);
$allowed = array(
    'image/jpeg' => 'image/jpeg',
    'image/png' => 'image/png',
    'image/webp' => 'image/webp',
    'image/gif' => 'image/gif',
);
if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '僅支援 JPG、PNG、WebP 或 GIF'), JSON_UNESCAPED_UNICODE);
    exit;
}

$bytes = file_get_contents($tmp);
@unlink($tmp);
if ($bytes === false || $bytes === '') {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '無法讀取圖片'), JSON_UNESCAPED_UNICODE);
    exit;
}

$prompt = "這是超市或全聯的消費明細截圖。請只抽出商品細項，回傳 JSON 物件，格式為 {\"lines\":[{\"name\":\"品名\",\"amount\":129}]}。"
    . "規則：name 是品名文字，可保留品名開頭的編號；amount 是該品項金額數字；忽略交易條碼、機台、序號、日期、店名、總計與標題列；不要加入說明文字。";

$body = array(
    'contents' => array(
        array(
            'parts' => array(
                array('text' => $prompt),
                array(
                    'inline_data' => array(
                        'mime_type' => $allowed[$mime],
                        'data' => base64_encode($bytes),
                    ),
                ),
            ),
        ),
    ),
    'generationConfig' => array(
        'temperature' => 0,
        'responseMimeType' => 'application/json',
    ),
);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . rawurlencode(trim($key));
$payload = json_encode($body, JSON_UNESCAPED_UNICODE);
if ($payload === false) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'msg' => '辨識準備失敗'), JSON_UNESCAPED_UNICODE);
    exit;
}

$ch = curl_init($url);
curl_setopt_array($ch, array(
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 45,
));
$response = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $status < 200 || $status >= 300) {
    error_log('Gemini receipt read failed: status=' . $status . ' error=' . $curlError);
    http_response_code(502);
    echo json_encode(array('ok' => false, 'msg' => '辨識服務暫時無法使用'), JSON_UNESCAPED_UNICODE);
    exit;
}

$decoded = json_decode($response, true);
$text = '';
if (isset($decoded['candidates'][0]['content']['parts']) && is_array($decoded['candidates'][0]['content']['parts'])) {
    foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
        if (isset($part['text'])) {
            $text .= (string) $part['text'];
        }
    }
}
$text = trim($text);
if ($text === '') {
    http_response_code(502);
    echo json_encode(array('ok' => false, 'msg' => '沒有辨識到細項'), JSON_UNESCAPED_UNICODE);
    exit;
}

$json = json_decode($text, true);
if (!is_array($json)) {
    if (preg_match('/\{.*\}/s', $text, $match)) {
        $json = json_decode($match[0], true);
    }
}
if (!is_array($json)) {
    http_response_code(502);
    echo json_encode(array('ok' => false, 'msg' => '辨識結果無法解析'), JSON_UNESCAPED_UNICODE);
    exit;
}

$rawLines = array();
if (isset($json['lines']) && is_array($json['lines'])) {
    $rawLines = $json['lines'];
} elseif (array_keys($json) === range(0, count($json) - 1)) {
    $rawLines = $json;
}

$lines = array();
foreach ($rawLines as $row) {
    if (!is_array($row)) {
        continue;
    }
    $name = isset($row['name']) ? trim((string) $row['name']) : '';
    $amount = isset($row['amount']) ? $row['amount'] : '';
    if ($name === '' || !is_numeric($amount)) {
        continue;
    }
    $number = (float) $amount;
    if (!is_finite($number) || $number <= 0 || $number > 99999999.99) {
        continue;
    }
    $lines[] = array(
        'name' => $name,
        'amount' => round($number, 2),
    );
}

if (!$lines) {
    echo json_encode(array('ok' => false, 'msg' => '沒有辨識到細項', 'lines' => array()), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('ok' => true, 'lines' => $lines), JSON_UNESCAPED_UNICODE);
