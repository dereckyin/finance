<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$item = isset($_GET['item']) ? (string) $_GET['item'] : '';
$merged_results = array();

try {
    $stmt = app_db()->prepare('SELECT DISTINCT class FROM detail WHERE AccountId = :id AND item = :item ORDER BY class COLLATE NOCASE DESC');
    $stmt->execute(array(':id' => $AccountId, ':item' => $item));
    foreach ($stmt as $value) {
        $merged_results[] = $value[0];
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
