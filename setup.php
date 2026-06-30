<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = $pageTitle ?? 'ติดตั้งระบบ';
$done = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $adminEmail = trim(strtolower($_POST['admin_email'] ?? ''));
        $adminPass = $_POST['admin_password'] ?? '';
        if (!$adminEmail || strlen($adminPass) < 6) {
            throw new RuntimeException('กรอกอีเมลแอดมินและรหัสผ่านอย่างน้อย 6 ตัว');
        }

        $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
        }

        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $db = db();
        $db->exec("DELETE FROM users WHERE role = 'admin'");
        $ins = $db->prepare("INSERT INTO users (email, password_hash, full_name, phone, member_type, role, status, subscription_start, subscription_end) VALUES (?,?,?,?,?,?,?,?,?)");
        $ins->execute([
            $adminEmail, $hash, 'ผู้ดูแลระบบ', null, 'reviewer', 'admin', 'active',
            date('Y-m-d'), date('Y-m-d', strtotime('+10 years')),
        ]);

        foreach ([SLIP_DIR, COVER_DIR] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        file_put_contents(UPLOAD_DIR . '/.htaccess', "php_flag engine off\n");
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
  <title><?= e($pageTitle) ?> — <?= e(SITE_NAME) ?></title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/member.css" />
</head>
<body class="member-page">
  <main class="member-card">
    <h1>ติดตั้งระบบสมาชิก</h1>
    <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($done): ?>
      <div class="alert alert--ok">ติดตั้งสำเร็จแล้ว — ลบหรือปิดไฟล์ setup.php หลังใช้งาน</div>
      <p><a class="btn btn--blue" href="member/login.php">เข้าสู่ระบบ</a></p>
    <?php else: ?>
      <p class="member-lead">รันครั้งเดียวเพื่อสร้างฐานข้อมูลและบัญชีแอดมิน (ต้องเปิด XAMPP MySQL)</p>
      <form method="post" class="member-form">
        <label>อีเมลแอดมิน<input type="email" name="admin_email" required value="admin@kohlibong.com" /></label>
        <label>รหัสผ่านแอดมิน<input type="password" name="admin_password" required minlength="6" /></label>
        <button type="submit" class="btn btn--primary btn--block">ติดตั้ง</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
