<?php
require_once __DIR__ . '/security.php';
$decoded = require_jwt_user();
require_once "header.php";

$AccountId = (int) $decoded->data->id;

// month filter (YYYY-MM), default current month
$ym = isset($_GET['ym']) && preg_match('/^\d{4}-\d{2}$/', $_GET['ym']) ? $_GET['ym'] : date('Y-m');

$_items = array();
try {
    foreach (db_rows('SELECT DISTINCT item, class FROM detail WHERE AccountId = :id ORDER BY item COLLATE NOCASE DESC, class COLLATE NOCASE DESC', array(':id' => $AccountId)) as $value) {
        $class = $value[1];
        $item = $value[0];
        if(!is_null(@$_items[$item]))
            $_items[$item] = array_merge($_items[$item], array($class => $class));
        else 
            $_items[$item] = array($class => $class);
    };
} catch ( PDOException $e ) {}

// get selected month totals per item
$sdate = date('Y/m/01', strtotime($ym.'-01'));
$edate = date('Y/m/t', strtotime($ym.'-01'));
$_month = array();
try {
    foreach (db_rows("SELECT Item, sum(Amount) AS amount FROM Detail WHERE AccountId = :id AND strftime('%Y/%m/%d', UseDate) >= :sdate AND strftime('%Y/%m/%d', UseDate) <= :edate GROUP BY Item", array(':id' => $AccountId, ':sdate' => $sdate, ':edate' => $edate)) as $value) { $_month[$value[0]] = (float) $value[1] * -1; };
} catch ( PDOException $e ) {}

$smarty->assign('items', $_items);
$smarty->assign('monthTotals', $_month);
$smarty->assign('ym', $ym);
$smarty->display('chart_month.tpl');

