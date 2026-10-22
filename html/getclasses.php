<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$merged_results = array();

try {
    $stmt = app_db()->prepare('SELECT DISTINCT item FROM detail WHERE AccountId = :id ORDER BY item COLLATE NOCASE DESC');
    $stmt->execute(array(':id' => $AccountId));
    foreach ($stmt as $value) {
        $merged_results[] = $value[0];
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
