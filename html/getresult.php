<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$pagesize = isset($_GET['pagesize']) ? (int) $_GET['pagesize'] : 25;
$keyword = isset($_GET['keyword']) ? (string) $_GET['keyword'] : '';
if ($page < 1) {
    $page = 1;
}
if ($pagesize < 1 || $pagesize > 10000) {
    $pagesize = 25;
}

$merged_results = array();
$sql = "SELECT 0 AS is_checked, AccountId, SId, Class, Item, Amount * -1 AS Amount, Descri, strftime('%Y/%m/%d', UseDate) AS UseDate, Need, 1 AS is_edited FROM Detail WHERE AccountId = :id";
$params = array(':id' => $AccountId);
if ($keyword !== '') {
    $sql .= ' AND (Class LIKE :kw1 OR Item LIKE :kw2 OR Descri LIKE :kw3)';
    $params[':kw1'] = '%' . $keyword . '%';
    $params[':kw2'] = '%' . $keyword . '%';
    $params[':kw3'] = '%' . $keyword . '%';
    $page = 1;
}
$offset = ($page - 1) * $pagesize;
$sql .= ' ORDER BY UseDate DESC, SId DESC LIMIT :limit OFFSET :offset';

try {
    $stmt = app_db()->prepare($sql);
    foreach ($params as $name => $value) {
        $stmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $pagesize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt as $value) {
        $merged_results[] = $value;
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
