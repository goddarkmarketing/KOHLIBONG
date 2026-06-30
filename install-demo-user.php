<?php
declare(strict_types=1);

/**
 * สร้างบัญชีสมาชิกทดสอบ (รันครั้งเดียว)
 * php install-demo-user.php
 */

require_once __DIR__ . '/includes/db.php';

$email = $argv[1] ?? 'member@kohlibong.com';
$pass = $argv[2] ?? 'member123';
$type = $argv[3] ?? 'reviewer';

if (!in_array($type, ['reviewer', 'business'], true)) {
    $type = 'reviewer';
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$db = db();

$check = $db->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    $upd = $db->prepare("UPDATE users SET password_hash=?, full_name=?, phone=?, member_type=?, role='member', status='active', subscription_start=?, subscription_end=? WHERE email=?");
    $upd->execute([
        $hash, 'สมาชิกทดสอบ', '0812345678', $type,
        date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), $email,
    ]);
    echo "Updated demo member: {$email}\n";
} else {
    $ins = $db->prepare("INSERT INTO users (email, password_hash, full_name, phone, member_type, role, status, subscription_start, subscription_end) VALUES (?,?,?,?,?,?,?,?,?)");
    $ins->execute([
        $email, $hash, 'สมาชิกทดสอบ', '0812345678', $type, 'member', 'active',
        date('Y-m-d'), date('Y-m-d', strtotime('+30 days')),
    ]);
    echo "Created demo member: {$email}\n";
}

echo "Password: {$pass}\n";
echo "Type: {$type} · active 30 days\n";
echo "Login: " . (defined('MEMBER_BASE') ? MEMBER_BASE : '/member') . "/login.php\n";
