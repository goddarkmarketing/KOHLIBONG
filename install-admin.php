<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$email = $argv[1] ?? 'admin@kohlibong.com';
$pass = $argv[2] ?? 'admin123';

$hash = password_hash($pass, PASSWORD_DEFAULT);
$db = db();
$db->exec("DELETE FROM users WHERE role = 'admin'");
$stmt = $db->prepare('INSERT INTO users (email, password_hash, full_name, phone, member_type, role, status, subscription_start, subscription_end) VALUES (?,?,?,?,?,?,?,?,?)');
$stmt->execute([
    $email, $hash, 'ผู้ดูแลระบบ', null, 'reviewer', 'admin', 'active',
    date('Y-m-d'), date('Y-m-d', strtotime('+10 years')),
]);

echo "Admin created: {$email}\n";
