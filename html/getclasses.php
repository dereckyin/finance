<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$merged_results = array();

try {
    $stmt = app_db()->prepare(
        'SELECT Item
         FROM Detail
         WHERE AccountId = :id
         GROUP BY Item
         ORDER BY COUNT(*) DESC, MAX(UseDate) DESC, Item COLLATE NOCASE'
    );
    $stmt->execute(array(':id' => $AccountId));
    foreach ($stmt as $value) {
        $name = trim((string) $value[0]);
        if ($name !== '') {
            $merged_results[] = $name;
        }
    }
} catch (Throwable $e) {
    db_fail($e);
}

echo json_encode($merged_results, JSON_UNESCAPED_UNICODE);
