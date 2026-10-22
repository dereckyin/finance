<?php

require_once '/app/vendor/firebase/php-jwt/src/JWT.php';
require_once '/app/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
require_once '/app/vendor/firebase/php-jwt/src/ExpiredException.php';
require_once '/app/vendor/firebase/php-jwt/src/BeforeValidException.php';

use Firebase\JWT\JWT;

ini_set('display_errors', '0');
ini_set('log_errors', '1');

function app_db()
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO('sqlite:' . __DIR__ . '/myfinance.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function jwt_secret()
{
    $key = getenv('JWT_SECRET');
    if (!is_string($key) || strlen($key) < 32) {
        error_log('JWT_SECRET is missing or too short');
        http_response_code(500);
        exit;
    }
    return $key;
}

function jwt_user()
{
    $jwt = isset($_COOKIE['jwt']) ? (string) $_COOKIE['jwt'] : '';
    if ($jwt === '') {
        return null;
    }
    try {
        $decoded = JWT::decode($jwt, jwt_secret(), array('HS256'));
    } catch (Throwable $e) {
        return null;
    }
    if (!isset($decoded->data->id)) {
        return null;
    }
    return $decoded;
}

function require_jwt_user()
{
    $user = jwt_user();
    if ($user === null) {
        header('Location: index.php');
        exit;
    }
    return $user;
}

function issue_login_cookie($accountId, $username)
{
    $now = time();
    $token = array(
        'iss' => 'https://feliix.myvnc.com',
        'aud' => 'https://feliix.myvnc.org',
        'iat' => $now,
        'nbf' => $now,
        'exp' => $now + 7 * 86400,
        'data' => array(
            'id' => (int) $accountId,
            'username' => $username,
        ),
    );
    $jwt = JWT::encode($token, jwt_secret(), 'HS256');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    setcookie('jwt', $jwt, array(
        'expires' => $now + 7 * 86400,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ));
}

function verify_account_password($name, $password)
{
    $stmt = app_db()->prepare('SELECT AccountId, AccountName, AccountPwd FROM Account WHERE AccountName = :name AND Active = 1');
    $stmt->execute(array(':name' => $name));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }

    $stored = (string) $row['AccountPwd'];
    $valid = false;
    if (preg_match('/^\$2[aby]\$/', $stored) === 1) {
        $valid = password_verify((string) $password, $stored);
    } else {
        $valid = hash_equals($stored, (string) $password);
    }
    if (!$valid) {
        return false;
    }

    if (preg_match('/^\$2[aby]\$/', $stored) !== 1 || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
        $update = app_db()->prepare('UPDATE Account SET AccountPwd = :hash WHERE AccountId = :id');
        $update->execute(array(
            ':hash' => password_hash((string) $password, PASSWORD_DEFAULT),
            ':id' => $row['AccountId'],
        ));
    }
    return $row;
}

function db_fail(Throwable $e)
{
    error_log($e->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
    }
    exit('暫時無法完成這個動作。');
}

function db_rows($sql, array $params = array())
{
    $stmt = app_db()->prepare($sql);
    foreach ($params as $name => $value) {
        $stmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function ledger_amount($value)
{
    if (!is_numeric($value)) {
        return 0.0;
    }
    return ((float) $value) * -1;
}

function ledger_datetime($value)
{
    $value = str_replace('/', '-', trim((string) $value));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
        return null;
    }
    return $value . ' 00:00:00';
}
