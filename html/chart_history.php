<?php
require_once __DIR__ . '/security.php';
$decoded = require_jwt_user();
require_once "header.php";
$smarty = create_smarty_instance();

$AccountId = (int) $decoded->data->id;

// Range filter: start/end month (YYYY-MM), default current year
$startYm = isset($_GET['start']) && preg_match('/^\d{4}-\d{2}$/', $_GET['start']) ? $_GET['start'] : date('Y-01');
$endYm = isset($_GET['end']) && preg_match('/^\d{4}-\d{2}$/', $_GET['end']) ? $_GET['end'] : date('Y-12');

// Build $spend and $item for range
$items = array();
$spend = array();
try {
    $startSlash = str_replace('-', '/', $startYm);
    $endSlash = str_replace('-', '/', $endYm);
    $data_date = ""; $item = array(); $sub_total = 0; $dim = -1; $_data = array();
    foreach (db_rows("SELECT Item, sum(Amount) AS amount, strftime('%Y/%m', UseDate) UseDate FROM Detail WHERE AccountId = :id AND strftime('%Y/%m', UseDate) >= :start AND strftime('%Y/%m', UseDate) <= :end GROUP BY Item, AccountId, strftime('%Y/%m', UseDate) ORDER BY UseDate DESC", array(':id' => $AccountId, ':start' => $startSlash, ':end' => $endSlash)) as $value) { $item[$value["Item"]] = "1"; $_data[] = $value; }
    $array = array();
    foreach ($_data as $row) {
        if($data_date != $row["UseDate"]) {
            $data_date = $row["UseDate"]; $dim++; $array[$dim] = array(); $array[$dim]["UseDate"] = $data_date; if($dim != 0) { $array[$dim - 1]["subtotal"] = $sub_total; foreach($item as $key => $value) { if(empty($array[$dim - 1][$key])) $array[$dim - 1][$key] = 0; } } $sub_total = 0; }
        $colum_name = $row["Item"]; $array[$dim][$colum_name] = $row["amount"] * -1; $sub_total += $row["amount"] * -1;
    }
    if ($dim >= 0) {
        $array[$dim]["subtotal"] = $sub_total; foreach($item as $key => $value) { if(empty($array[$dim][$key])) $array[$dim][$key] = 0; }
        $items = array_keys($item);
        $spend = $array;
    }
} catch ( PDOException $e ) {}

$smarty->assign('item', array_merge(array('UseDate'), $items, array('subtotal')));
$smarty->assign('spend', $spend);
$totalAmount = 0;
if (!empty($spend)) {
    foreach ($spend as $row) {
        if (isset($row['subtotal'])) { $totalAmount += (float)$row['subtotal']; }
    }
}
$smarty->assign('totalAmount', $totalAmount);
$smarty->assign('start', $startYm);
$smarty->assign('end', $endYm);
$smarty->display('chart_history.tpl');

