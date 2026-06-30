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
        seed_demo_member($email, $pass);
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
