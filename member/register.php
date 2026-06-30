<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

if (current_user()) {
    $u = current_user();
    login_redirect($u);
}

$error = null;
$type = $_GET['type'] ?? ($_POST['member_type'] ?? 'reviewer');
if (!in_array($type, ['reviewer', 'business'], true)) {
    $type = 'reviewer';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $memberType = $_POST['member_type'] ?? 'reviewer';
        $amount = (float) ($_POST['amount'] ?? MEMBERSHIP_FEE);
        $transferDate = parse_date_picker('transfer', required: false);

        if (!$email || !$fullName || strlen($password) < 6) {
            throw new RuntimeException('กรอกข้อมูลให้ครบ (รหัสผ่านอย่างน้อย 6 ตัว)');
        }
        if ($password !== $confirm) {
            throw new RuntimeException('รหัสผ่านยืนยันไม่ตรงกัน');
        }

        $check = db()->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            throw new RuntimeException('อีเมลนี้ถูกใช้แล้ว');
        }

        $slipPath = save_upload('slip', SLIP_DIR, 'slip');

        $pdo = db();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, full_name, phone, member_type, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $fullName, $phone, $memberType, 'pending_approval']);
        $userId = (int) $pdo->lastInsertId();

        if ($memberType === 'business') {
            $bizName = trim($_POST['business_name'] ?? '');
            $bizType = $_POST['business_type'] ?? 'hotel';
            $address = trim($_POST['address'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $lineId = trim($_POST['line_id'] ?? '');
            if (!$bizName) {
                throw new RuntimeException('กรอกชื่อธุรกิจ');
            }
            $bp = $pdo->prepare('INSERT INTO business_profiles (user_id, business_name, business_type, address, description, line_id) VALUES (?,?,?,?,?,?)');
            $bp->execute([$userId, $bizName, $bizType, $address, $desc, $lineId]);
        }

        $pay = $pdo->prepare('INSERT INTO payments (user_id, slip_path, amount, transfer_date, status) VALUES (?,?,?,?,?)');
        $pay->execute([$userId, $slipPath, $amount, $transferDate ?: null, 'pending']);

        $pdo->commit();
        flash('ok', 'สมัครสำเร็จ — รอแอดมินตรวจสอบสลิป (ภายใน 24 ชม.)');
        redirect('login.php');
    } catch (Throwable $ex) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        $error = $ex->getMessage();
        $type = $_POST['member_type'] ?? $type;
    }
}

member_header('สมัครสมาชิก');
?>
<section class="member-card member-card--wide">
  <h1>สมัครสมาชิก</h1>
  <p class="member-lead">ค่าสมาชิก <?= number_format(MEMBERSHIP_FEE) ?> บาท / 30 วัน — อัปโหลดสลิปโอนเงิน แอดมินจะอนุมัติก่อนใช้งาน</p>

  <div class="type-tabs">
    <a href="?type=reviewer" class="type-tab <?= $type === 'reviewer' ? 'is-active' : '' ?>">ประเภท 1: รีวิวเท่านั้น</a>
    <a href="?type=business" class="type-tab <?= $type === 'business' ? 'is-active' : '' ?>">ประเภท 2: ผู้ประกอบการ</a>
  </div>

  <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="member-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
    <input type="hidden" name="member_type" value="<?= e($type) ?>" />

    <div class="form-grid">
      <label>ชื่อ-นามสกุล<input type="text" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>" /></label>
      <label>เบอร์โทร<input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" /></label>
      <label>อีเมล (ใช้ล็อกอิน)<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" /></label>
      <label>รหัสผ่าน<input type="password" name="password" required minlength="6" /></label>
      <label>ยืนยันรหัสผ่าน<input type="password" name="password_confirm" required minlength="6" /></label>
    </div>

    <?php if ($type === 'business'): ?>
      <h3 class="form-section-title">ข้อมูลธุรกิจ</h3>
      <div class="form-grid">
        <label>ชื่อธุรกิจ<input type="text" name="business_name" value="<?= e($_POST['business_name'] ?? '') ?>" /></label>
        <label>ประเภท
          <select name="business_type">
            <option value="hotel">ที่พัก</option>
            <option value="restaurant">ร้านอาหาร</option>
            <option value="tour">ทัวร์/บริการ</option>
            <option value="other">อื่น ๆ</option>
          </select>
        </label>
        <label class="span-2">ที่อยู่ / สถานที่<input type="text" name="address" value="<?= e($_POST['address'] ?? '') ?>" /></label>
        <label>LINE ID<input type="text" name="line_id" value="<?= e($_POST['line_id'] ?? '') ?>" /></label>
        <label class="span-2">รายละเอียดธุรกิจ<textarea name="description" rows="3"><?= e($_POST['description'] ?? '') ?></textarea></label>
      </div>
    <?php else: ?>
      <p class="member-note">ประเภทรีวิว: หลังอนุมัติแล้วสามารถเขียนรีวิวได้ (ระบุที่จอง วันที่ ชื่อลูกค้า รูปปก และให้ดาว)</p>
    <?php endif; ?>

    <h3 class="form-section-title">ชำระค่าสมาชิก</h3>
    <div class="bank-box">
      <strong>โอนเงิน <?= number_format(MEMBERSHIP_FEE) ?> บาท / 30 วัน</strong><br>
      <?= e(BANK_INFO) ?><br>
      อัปโหลดสลิปด้านล่าง — แอดมินจะตรวจสอบก่อนเปิดใช้งาน
    </div>
    <div class="form-grid">
      <label>จำนวนเงิน (บาท)<input type="number" name="amount" step="0.01" value="<?= MEMBERSHIP_FEE ?>" required /></label>
      <div class="field span-2">
        <span class="field__label">วันที่โอน</span>
        <?= render_date_picker('transfer', date('Y-m-d')) ?>
      </div>
      <label class="span-2">สลิปโอนเงิน (จำเป็น)<input type="file" name="slip" accept="image/jpeg,image/png,image/webp" required /></label>
    </div>

    <button type="submit" class="btn btn--register btn--block">ส่งสมัครสมาชิก</button>
  </form>
  <p class="member-foot">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
</section>
<?php member_footer(); ?>
