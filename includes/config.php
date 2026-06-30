<?php
declare(strict_types=1);

if (is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'kohlibong');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

if (!function_exists('app_detect_site_base')) {
    function app_detect_site_base(): string
    {
        if (PHP_SAPI === 'cli') {
            $folder = basename(dirname(__DIR__));
            if ($folder === 'เกาะลิบง.com' || $folder === 'KOHLIBONG') {
                return '/' . $folder;
            }
            return '';
        }

        $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
            ? rtrim(str_replace('\\', '/', (string) $_SERVER['DOCUMENT_ROOT']), '/')
            : '';
        $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

        if ($docRoot !== '' && str_starts_with($projectRoot, $docRoot)) {
            $rel = substr($projectRoot, strlen($docRoot));
            return $rel === '' ? '' : $rel;
        }

        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        if (str_contains($script, '/เกาะลิบง.com/')) {
            return '/เกาะลิบง.com';
        }
        if (str_contains($script, '/KOHLIBONG/')) {
            return '/KOHLIBONG';
        }

        return '';
    }
}

if (!defined('SITE_BASE')) {
    define('SITE_BASE', app_detect_site_base());
}

define('SITE_NAME', 'เกาะลิบง.com');
define('MEMBER_BASE', SITE_BASE . '/member');
define('ADMIN_BASE', SITE_BASE . '/admin');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('SLIP_DIR', UPLOAD_DIR . '/slips');
define('COVER_DIR', UPLOAD_DIR . '/covers');
define('SITE_CONTENT_DIR', UPLOAD_DIR . '/site');

define('MEMBERSHIP_FEE', 299);
define('SUBSCRIPTION_DAYS', 30);
define('BANK_INFO', 'ธนาคารกสิกรไทย · ชื่อบัญชี เกาะลิบง.com · เลขที่ 123-4-56789-0');
define('ADMIN_CONTACT_LINE', '@kohlibong');
define('ADMIN_CONTACT_EMAIL', 'admin@kohlibong.com');
define('EXPIRY_WARN_DAYS', 7);

define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

if (session_status() === PHP_SESSION_NONE) {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
