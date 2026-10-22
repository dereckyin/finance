<?php
require_once __DIR__ . '/security.php';
$decoded = require_jwt_user();
require_once "header.php";
$smarty = create_smarty_instance();

$AccountId = (int) $decoded->data->id;

// month filter (YYYY-MM), default current month
$ym = isset($_GET['ym']) && preg_match('/^\d{4}-\d{2}$/', $_GET['ym']) ? $_GET['ym'] : date('Y-m');

// load $item and $spend for selected month only
$items = array();
$spend = array();
try {
    $ymSlash = str_replace('-', '/', $ym);
    $rowAmounts = array();
    foreach (db_rows("SELECT Item, sum(Amount) AS amount FROM Detail WHERE AccountId = :id AND strftime('%Y/%m', UseDate) = :ym GROUP BY Item ORDER BY Item COLLATE NOCASE", array(':id' => $AccountId, ':ym' => $ymSlash)) as $value) { $rowAmounts[$value[0]] = $value[1] * -1; }
    // Build items list
    $items = array_keys($rowAmounts);
    sort($items, SORT_NATURAL | SORT_FLAG_CASE);
    // Build spend structure expected by template
    $spendRow = array('UseDate' => $ymSlash);
    $subtotal = 0;
    foreach ($items as $it) { $amt = isset($rowAmounts[$it]) ? $rowAmounts[$it] : 0; $spendRow[$it] = $amt; $subtotal += $amt; }
    $spendRow['subtotal'] = $subtotal;
    $spend = array($spendRow);
} catch ( PDOException $e ) {}

$smarty->assign('item', array_merge(array('UseDate'), $items, array('subtotal')));
$smarty->assign('spend', $spend);
$smarty->assign('ym', $ym);
$smarty->display('chart_bar.tpl');

