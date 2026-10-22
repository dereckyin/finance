<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$merged_results = array();

try {
    $stmt = app_db()->prepare('SELECT count(*) AS total FROM Detail WHERE AccountId = :id');
    $stmt->execute(array(':id' => $AccountId));
    foreach ($stmt as $value) {
        $merged_results[] = $value[0];
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
