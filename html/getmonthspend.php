<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$merged_results = 0;
$sdate = date('Y/m/') . '01';
$edate = date('Y/m/t', strtotime($sdate));

try {
    $stmt = app_db()->prepare("SELECT sum(Amount) * -1 AS spend FROM Detail WHERE AccountId = :id AND strftime('%Y/%m/%d', UseDate) >= :sdate AND strftime('%Y/%m/%d', UseDate) <= :edate");
    $stmt->execute(array(':id' => $AccountId, ':sdate' => $sdate, ':edate' => $edate));
    foreach ($stmt as $value) {
        $merged_results = $value[0];
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
