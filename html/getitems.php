<?php

require_once __DIR__ . '/security.php';

$decoded = require_jwt_user();
$AccountId = (int) $decoded->data->id;
$item = isset($_GET['item']) ? (string) $_GET['item'] : '';
$merged_results = array();

try {
    $stmt = app_db()->prepare(
        'SELECT Class
         FROM Detail
         WHERE AccountId = :id AND Item = :item
         GROUP BY Class
         ORDER BY COUNT(*) DESC, MAX(UseDate) DESC, Class COLLATE NOCASE'
    );
    $stmt->execute(array(':id' => $AccountId, ':item' => $item));
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
