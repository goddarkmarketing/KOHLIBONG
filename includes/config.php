<?php
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'kohlibong');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'เกาะลิบง.com');
define('SITE_BASE', '/เกาะลิบง.com');
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
    session_start();
}
