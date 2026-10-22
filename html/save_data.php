<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/header.php';

$decoded = require_jwt_user();
$op = isset($_REQUEST['op']) ? (string) $_REQUEST['op'] : '';
$SId = isset($_REQUEST['SId']) ? (int) $_REQUEST['SId'] : 0;
$strClass = isset($_REQUEST['strClass']) ? (string) $_REQUEST['strClass'] : '';
$strItem = isset($_REQUEST['strItem']) ? (string) $_REQUEST['strItem'] : '';
$txtUseDate = isset($_REQUEST['txtUseDate']) ? (string) $_REQUEST['txtUseDate'] : '';
$nAmount = isset($_REQUEST['nAmount']) ? $_REQUEST['nAmount'] : '';
$strDesc = isset($_REQUEST['strDesc']) ? (string) $_REQUEST['strDesc'] : '';
$AccountId = (int) $decoded->data->id;

switch ($op) {
    case 'adddata':
        adddata($AccountId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc);
        break;
    case 'del':
        deletedata($AccountId, $SId);
        break;
    case 'mdf':
        mdfdata($SId, $AccountId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc);
        break;
}

function adddata($AccountId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc)
{
    if (empty($AccountId)) {
        return false;
    }

    $useDate = ledger_datetime($txtUseDate);
    if ($useDate === null) {
        http_response_code(400);
        echo json_encode(array('ok' => false, 'msg' => '日期格式不正確'), JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = app_db()->prepare('INSERT INTO Detail(AccountId, Class, Item, Amount, Descri, UseDate, Need) VALUES (:id, :class, :item, :amount, :descri, :usedate, :need)');
        $stmt->execute(array(
            ':id' => (int) $AccountId,
            ':class' => $strClass,
            ':item' => $strItem,
            ':amount' => ledger_amount($nAmount),
            ':descri' => $strDesc,
            ':usedate' => $useDate,
            ':need' => 'Y',
        ));
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(array('ok' => false, 'msg' => '儲存失敗'), JSON_UNESCAPED_UNICODE);
        exit;
    }

    return true;
}

function mdfdata($SId, $AccountId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc)
{
    if (empty($AccountId) || $SId < 1) {
        return false;
    }

    $useDate = ledger_datetime($txtUseDate);
    if ($useDate === null) {
        http_response_code(400);
        echo json_encode(array('ok' => false, 'msg' => '日期格式不正確'), JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = app_db()->prepare('UPDATE Detail SET Class = :class, Item = :item, Amount = :amount, UseDate = :usedate, Descri = :descri WHERE AccountId = :id AND SId = :sid');
        $stmt->execute(array(
            ':class' => $strClass,
            ':item' => $strItem,
            ':amount' => ledger_amount($nAmount),
            ':usedate' => $useDate,
            ':descri' => $strDesc,
            ':id' => (int) $AccountId,
            ':sid' => (int) $SId,
        ));
    } catch (Throwable $e) {
        db_fail($e);
    }

    return true;
}

function deletedata($AccountId, $SId)
{
    if (empty($AccountId) || $SId < 1) {
        return false;
    }

    try {
        $stmt = app_db()->prepare('DELETE FROM Detail WHERE SId = :sid AND AccountId = :id');
        $stmt->execute(array(
            ':sid' => (int) $SId,
            ':id' => (int) $AccountId,
        ));
    } catch (Throwable $e) {
        db_fail($e);
    }

    return true;
}
