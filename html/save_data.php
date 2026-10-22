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

    $entry = ledger_entry($strClass, $strItem, $txtUseDate, $nAmount, $strDesc);
    if ($entry['error'] !== null) {
        http_response_code(400);
        echo json_encode(array('ok' => false, 'msg' => $entry['error']), JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = app_db()->prepare('INSERT INTO Detail(AccountId, Class, Item, Amount, Descri, UseDate, Need) VALUES (:id, :class, :item, :amount, :descri, :usedate, :need)');
        $stmt->execute(array(
            ':id' => (int) $AccountId,
            ':class' => $entry['class'],
            ':item' => $entry['item'],
            ':amount' => $entry['amount'],
            ':descri' => $entry['note'],
            ':usedate' => $entry['date'],
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

    $entry = ledger_entry($strClass, $strItem, $txtUseDate, $nAmount, $strDesc);
    if ($entry['error'] !== null) {
        http_response_code(400);
        echo json_encode(array('ok' => false, 'msg' => $entry['error']), JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = app_db()->prepare('UPDATE Detail SET Class = :class, Item = :item, Amount = :amount, UseDate = :usedate, Descri = :descri WHERE AccountId = :id AND SId = :sid');
        $stmt->execute(array(
            ':class' => $entry['class'],
            ':item' => $entry['item'],
            ':amount' => $entry['amount'],
            ':usedate' => $entry['date'],
            ':descri' => $entry['note'],
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
