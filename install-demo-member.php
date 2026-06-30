<?php
declare(strict_types=1);

/**
 * สร้างบัญชีสมาชิกทดสอบ (รันครั้งเดียวบนโฮสติ้ง แล้วลบไฟล์นี้)
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$email = 'member@kohlibong.com';
$pass = 'member123';
$done = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $db = db();
        $check = $db->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $upd = $db->prepare("UPDATE users SET password_hash=?, full_name=?, phone=?, member_type=?, role='member', status='active', subscription_start=?, subscription_end=? WHERE email=?");
            $upd->execute([
                $hash, 'สมาชิกทดสอบ', '0812345678', 'reviewer',
                date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), $email,
            ]);
        } else {
            $ins = $db->prepare("INSERT INTO users (email, password_hash, full_name, phone, member_type, role, status, subscription_start, subscription_end) VALUES (?,?,?,?,?,?,?,?,?)");
            $ins->execute([
                $email, $hash, 'สมาชิกทดสอบ', '0812345678', 'reviewer', 'member', 'active',
                date('Y-m-d'), date('Y-m-d', strtotime('+30 days')),
            ]);
        }
        $done = true;
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>สร้างสมาชิกทดสอบ — <?= e(SITE_NAME) ?></title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/member.css" />
</head>
<body class="member-page">
  <main class="member-card">
    <h1>สร้างบัญชีสมาชิกทดสอบ</h1>
    <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($done): ?>
      <div class="alert alert--ok">สร้างสำเร็จ — ลบไฟล์ install-demo-member.php หลังใช้งาน</div>
      <p>อีเมล <code><?= e($email) ?></code> · รหัสผ่าน <code><?= e($pass) ?></code></p>
      <p><a class="btn btn--blue" href="member/login.php">เข้าสู่ระบบ</a></p>
    <?php else: ?>
      <p class="member-lead">รันครั้งเดียวเพื่อสร้างบัญชีทดสอบบนฐานข้อมูลจริง</p>
      <ul class="member-lead">
        <li>อีเมล: <code><?= e($email) ?></code></li>
        <li>รหัสผ่าน: <code><?= e($pass) ?></code></li>
        <li>สถานะ: active 30 วัน</li>
      </ul>
      <form method="post" class="member-form">
        <button type="submit" class="btn btn--primary btn--block">สร้างบัญชีทดสอบ</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
