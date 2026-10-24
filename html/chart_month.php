<?php
require_once __DIR__ . '/security.php';
$decoded = require_jwt_user();
require_once "header.php";

$AccountId = (int) $decoded->data->id;
$ym = isset($_GET['ym']) && preg_match('/^\d{4}-\d{2}$/', $_GET['ym']) ? $_GET['ym'] : date('Y-m');
$center = DateTime::createFromFormat('!Y-m-d', $ym . '-01');
if (!$center) {
    $center = new DateTime('first day of this month');
    $ym = $center->format('Y-m');
}

$start = (clone $center)->modify('-3 months');
$end = (clone $center)->modify('+3 months');
$byMonth = array();
try {
    $rows = db_rows(
        "SELECT strftime('%Y-%m', UseDate) AS ym, Item AS name, COALESCE(SUM(Amount) * -1, 0) AS spend
         FROM Detail
         WHERE AccountId = :id AND strftime('%Y-%m', UseDate) >= :start AND strftime('%Y-%m', UseDate) <= :end
         GROUP BY strftime('%Y-%m', UseDate), Item",
        array(':id' => $AccountId, ':start' => $start->format('Y-m'), ':end' => $end->format('Y-m'))
    );
    foreach ($rows as $row) {
        $spend = (float) $row['spend'];
        if ($spend == 0.0) {
            continue;
        }
        $name = trim((string) $row['name']);
        if ($name === '') {
            $name = '未分類';
        }
        $byMonth[$row['ym']][] = array('name' => $name, 'amount' => round($spend, 2));
    }
} catch (PDOException $e) {
}

$today = new DateTime('today');
$todayYm = $today->format('Y-m');
$months = array();
$cursor = clone $start;
for ($i = 0; $i < 7; $i++) {
    $key = $cursor->format('Y-m');
    $slices = isset($byMonth[$key]) ? $byMonth[$key] : array();
    usort($slices, function ($a, $b) {
        if ($a['amount'] == $b['amount']) {
            return strcmp($a['name'], $b['name']);
        }
        return ($a['amount'] < $b['amount']) ? 1 : -1;
    });
    $total = 0;
    foreach ($slices as $slice) {
        $total += $slice['amount'];
    }
    $months[] = array(
        'ym' => $key,
        'label' => $cursor->format('Y/m'),
        'total' => round($total, 2),
        'current' => $key === $ym,
        'partial' => $key === $todayYm && $today->format('j') !== $today->format('t'),
        'slices' => $slices,
    );
    $cursor->modify('+1 month');
}

$smarty->assign('ym', $ym);
$smarty->assign('monthChartsJson', json_encode($months, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
$smarty->display('chart_month.tpl');
