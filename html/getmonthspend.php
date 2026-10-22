<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
    $year = (int) date('Y');
    $month = (int) date('n');
}
$start = sprintf('%04d/%02d/01', $year, $month);
$end = sprintf('%04d/%02d/%02d', $year, $month, (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month))));

try {
    $rows = db_rows(
        "SELECT COALESCE(SUM(Amount) * -1, 0) AS spend FROM Detail WHERE AccountId = :id AND strftime('%Y/%m/%d', UseDate) >= :start AND strftime('%Y/%m/%d', UseDate) <= :end",
        array(':id' => $AccountId, ':start' => $start, ':end' => $end)
    );
    echo isset($rows[0]['spend']) ? $rows[0]['spend'] : 0;
} catch (PDOException $e) {
    db_fail($e);
}
