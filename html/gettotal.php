<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$keyword = isset($_GET['keyword']) ? (string) $_GET['keyword'] : '';
$merged_results = array();
$sql = 'SELECT count(*) AS total FROM Detail WHERE AccountId = :id';
$params = array(':id' => $AccountId);
if ($keyword !== '') {
    $sql .= ' AND (Class LIKE :kw1 OR Item LIKE :kw2 OR Descri LIKE :kw3)';
    $params[':kw1'] = '%' . $keyword . '%';
    $params[':kw2'] = '%' . $keyword . '%';
    $params[':kw3'] = '%' . $keyword . '%';
}

try {
    $stmt = app_db()->prepare($sql);
    $stmt->execute($params);
    foreach ($stmt as $value) {
        $merged_results[] = $value[0];
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
