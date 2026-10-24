<?php

require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$ym = isset($_GET['ym']) ? (string) $_GET['ym'] : '';
$by = isset($_GET['by']) && $_GET['by'] === 'class' ? 'class' : 'item';
$name = '';
if (isset($_GET['name'])) {
    $name = trim((string) $_GET['name']);
} elseif (isset($_GET['item'])) {
    $name = trim((string) $_GET['item']);
}

if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'msg' => '月份格式不正確', 'rows' => array()), JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT Class, Item, Amount * -1 AS Amount, Descri, strftime('%Y/%m/%d', UseDate) AS UseDate, SId
        FROM Detail
        WHERE AccountId = :id AND strftime('%Y-%m', UseDate) = :ym";
$params = array(':id' => $AccountId, ':ym' => $ym);
if ($by === 'class') {
    if ($name === '' || $name === '未分類') {
        $sql .= " AND (Class IS NULL OR TRIM(Class) = '')";
    } else {
        $sql .= ' AND Class = :name';
        $params[':name'] = $name;
    }
} else {
    if ($name === '' || $name === '未分類') {
        $sql .= " AND (Item IS NULL OR TRIM(Item) = '')";
    } else {
        $sql .= ' AND Item = :name';
        $params[':name'] = $name;
    }
}
$sql .= ' ORDER BY UseDate DESC, SId DESC';

$rows = array();
try {
    foreach (db_rows($sql, $params) as $row) {
        $rows[] = array(
            'UseDate' => (string) $row['UseDate'],
            'Class' => trim((string) $row['Class']) === '' ? '未分類' : (string) $row['Class'],
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
    'by' => $by,
    'name' => ($name === '' ? '未分類' : $name),
    'item' => ($name === '' ? '未分類' : $name),
    'rows' => $rows,
), JSON_UNESCAPED_UNICODE);
