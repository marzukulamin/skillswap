<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbConfig = [
    'host' => getenv('SKILLSWAP_DB_HOST') ?: 'localhost',
    'username' => getenv('SKILLSWAP_DB_USER') ?: 'root',
    'password' => getenv('SKILLSWAP_DB_PASSWORD') ?: '',
    'database' => getenv('SKILLSWAP_DB_NAME') ?: 'skillswap',
    'port' => (int) (getenv('SKILLSWAP_DB_PORT') ?: 3306),
];

$localConfigPath = __DIR__ . '/config.local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $dbConfig = array_merge($dbConfig, $localConfig);
    }
}

try {
    $conn = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database'],
        $dbConfig['port']
    );
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(503);
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    $detail = $isLocal ? ' ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') : '';
    exit('SkillSwap cannot connect to MySQL.' . $detail);
}

// Re-check account state on every authenticated database request so a suspended
// user cannot keep using an older session indefinitely.
if (function_exists('current_user_id') && current_user_id()) {
    $sessionUserId = current_user_id();
    $sessionUserStmt = $conn->prepare('SELECT * FROM `USER` WHERE user_id = ? LIMIT 1');
    $sessionUserStmt->bind_param('i', $sessionUserId);
    $sessionUserStmt->execute();
    $sessionUser = $sessionUserStmt->get_result()->fetch_assoc();

    if (!$sessionUser || in_array(strtolower((string) $sessionUser['status']), ['inactive', 'suspended'], true)) {
        unset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['user']);
        flash('danger', 'Your account is not currently active.');
        redirect('login.php');
    }

    $_SESSION['user'] = $sessionUser;
    $_SESSION['role'] = $sessionUser['role'];
}
