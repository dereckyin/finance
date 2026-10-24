<?php

require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$ym = isset($_GET['ym']) ? (string) $_GET['ym'] : '';
$item = isset($_GET['item']) ? trim((string) $_GET['item']) : '';

if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '月份格式不正確', 'rows' => array()), JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT Class, Item, Amount * -1 AS Amount, Descri, strftime('%Y/%m/%d', UseDate) AS UseDate, SId
        FROM Detail
        WHERE AccountId = :id AND strftime('%Y-%m', UseDate) = :ym";
$params = array(':id' => $AccountId, ':ym' => $ym);
if ($item === '' || $item === '未分類') {
    $sql .= " AND (Item IS NULL OR TRIM(Item) = '')";
} else {
    $sql .= ' AND Item = :item';
    $params[':item'] = $item;
}
$sql .= ' ORDER BY UseDate DESC, SId DESC';

$rows = array();
try {
    foreach (db_rows($sql, $params) as $row) {
        $rows[] = array(
            'UseDate' => (string) $row['UseDate'],
            'Class' => (string) $row['Class'],
            'Item' => trim((string) $row['Item']) === '' ? '未分類' : (string) $row['Item'],
            'Amount' => round((float) $row['Amount'], 2),
            'Descri' => (string) $row['Descri'],
            'SId' => (int) $row['SId'],
        );
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode(array(
    'ok' => true,
    'ym' => $ym,
    'item' => ($item === '' ? '未分類' : $item),
    'rows' => $rows,
), JSON_UNESCAPED_UNICODE);
