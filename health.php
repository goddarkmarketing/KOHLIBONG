<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/config.php';

$result = [
    'ok' => true,
    'app' => 'kohlibong',
    'php' => PHP_VERSION,
    'site_base' => SITE_BASE,
    'config_local' => is_file(__DIR__ . '/includes/config.local.php'),
    'db' => false,
    'db_error' => null,
    'admin_login' => SITE_BASE . '/member/login.php',
];

try {
    require_once __DIR__ . '/includes/db.php';
    db()->query('SELECT 1');
    $result['db'] = true;
} catch (Throwable $ex) {
    $result['ok'] = false;
    $result['db_error'] = $ex->getMessage();
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
