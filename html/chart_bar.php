<?php
require_once __DIR__ . '/security.php';
$decoded = require_jwt_user();
require_once "header.php";

$AccountId = (int) $decoded->data->id;
$ym = isset($_GET['ym']) && preg_match('/^\d{4}-\d{2}$/', $_GET['ym']) ? $_GET['ym'] : date('Y-m');
$group = isset($_GET['group']) && $_GET['group'] === 'class' ? 'class' : 'item';
$column = $group === 'class' ? 'Class' : 'Item';
$groupLabel = $group === 'class' ? '明細' : '分類';

$bars = array();
$total = 0;
try {
    $rows = db_rows(
        "SELECT {$column} AS name, COALESCE(SUM(Amount) * -1, 0) AS amount
         FROM Detail
         WHERE AccountId = :id AND strftime('%Y-%m', UseDate) = :ym
         GROUP BY {$column}
         ORDER BY amount DESC, name COLLATE NOCASE",
        array(':id' => $AccountId, ':ym' => $ym)
    );
    foreach ($rows as $row) {
        $amount = (float) $row['amount'];
        if ($amount == 0.0) {
            continue;
        }
        $name = trim((string) $row['name']);
        if ($name === '') {
            $name = '未分類';
        }
        $bars[] = array(
            'name' => $name,
            'amount' => round($amount, 2),
        );
        $total += $amount;
    }
} catch (PDOException $e) {
}

$smarty->assign('ym', $ym);
$smarty->assign('group', $group);
$smarty->assign('groupLabel', $groupLabel);
$smarty->assign('barTotalLabel', number_format(round($total, 0), 0, '.', ','));
$smarty->assign('barChartsJson', json_encode($bars, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
$smarty->display('chart_bar.tpl');
