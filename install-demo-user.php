<?php
declare(strict_types=1);

/**
 * สร้างบัญชีสมาชิกทดสอบ (รันครั้งเดียว)
 * php install-demo-user.php
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$email = $argv[1] ?? 'member@kohlibong.com';
$pass = $argv[2] ?? 'member123';

seed_demo_member($email, $pass);

echo "Demo member ready: {$email}\n";
echo "Password: {$pass}\n";
echo "Login: " . (defined('MEMBER_BASE') ? MEMBER_BASE : '/member') . "/login.php\n";
