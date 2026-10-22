<?php
/**
 * Example Application
 *
 * @package Example-application
 */
ob_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
require_once "header.php";
require_once __DIR__ . "/security.php";

$op = isset($_REQUEST['op']) ? my_filter($_REQUEST['op'], "string") : '';
$txtId = isset($_POST['txtId']) ? trim((string) $_POST['txtId']) : '';
$txtPwd = isset($_POST['txtPwd']) ? (string) $_POST['txtPwd'] : '';
$captchaInput = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

switch ($op) {
    

    case 'user_login':
        if (!verify_captcha($captchaInput)) {
            header("location:index.php?e=captcha");
            exit;
        }
        $login = user_login($txtId, $txtPwd);

        if ($login) {
            header("location:add_data.php");
        } else {
            header("location:index.php?e=account");
        }
        exit;
        break;

    case 'user_logout':
        user_logout();
        header("location:index.php");
        exit;
        break;

}

function verify_captcha($input)
{
    if (!isset($_SESSION)) {
        @session_start();
    }
    $expected = isset($_SESSION['captcha_code']) ? $_SESSION['captcha_code'] : '';
    // one-time use
    unset($_SESSION['captcha_code']);
    if ($expected === '') {
        return false;
    }
    return strtoupper($input) === strtoupper($expected);
}

//�|���n�J
function user_login($txtId, $txtPwd)
{
    if ($txtId === '' || $txtPwd === '') {
        return false;
    }

    try {
        $value = verify_account_password($txtId, $txtPwd);
        if ($value === false) {
            return false;
        }
        issue_login_cookie($value['AccountId'], $value['AccountName']);
        return true;
    } catch (Throwable $e) {
        error_log($e->getMessage());
        return false;
    }
}

//�|���n�X
function user_logout()
{
    unset($_SESSION['AccountId']);
    unset($_SESSION['AccountVer']);
    unset($_SESSION['AccountName']);
    unset($_SESSION['login']);
    setcookie('jwt', '', array(
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ));
}

$smarty = create_smarty_instance();

//$smarty->force_compile = true;
//$smarty->debugging = true;
$smarty->caching = false;

$loginError = '';
if (isset($_GET['e']) && $_GET['e'] === 'captcha') {
    $loginError = '驗證碼錯誤，請重新輸入';
} elseif (isset($_GET['e']) && $_GET['e'] === 'account') {
    $loginError = '帳號或密碼錯誤';
}
$smarty->assign('login_error', $loginError);

$smarty->display('index.html');
