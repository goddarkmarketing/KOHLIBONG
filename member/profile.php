<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_member();
$error = null;
$biz = $user['member_type'] === 'business' ? member_get_business_profile((int) $user['id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'profile';
    try {
        if ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (!password_verify($current, $user['password_hash'])) {
                throw new RuntimeException('รหัสผ่านปัจจุบันไม่ถูกต้อง');
            }
            if (strlen($newPass) < 6 || $newPass !== $confirm) {
                throw new RuntimeException('รหัสผ่านใหม่ไม่ตรงกันหรือสั้นเกินไป (อย่างน้อย 6 ตัว)');
            }
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($newPass, PASSWORD_DEFAULT), $user['id']]);
            flash('ok', 'เปลี่ยนรหัสผ่านแล้ว');
        } else {
            $fullName = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if (!$fullName) {
                throw new RuntimeException('กรอกชื่อ-นามสกุล');
            }
            db()->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?')
                ->execute([$fullName, $phone ?: null, $user['id']]);

            if ($user['member_type'] === 'business') {
                $bizName = trim($_POST['business_name'] ?? '');
                $bizType = $_POST['business_type'] ?? 'hotel';
                $address = trim($_POST['address'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                $lineId = trim($_POST['line_id'] ?? '');
                if (!$bizName) {
                    throw new RuntimeException('กรอกชื่อธุรกิจ');
                }
                if ($biz) {
                    db()->prepare('UPDATE business_profiles SET business_name=?, business_type=?, address=?, description=?, line_id=? WHERE user_id=?')
                        ->execute([$bizName, $bizType, $address, $desc, $lineId, $user['id']]);
                } else {
                    db()->prepare('INSERT INTO business_profiles (user_id, business_name, business_type, address, description, line_id) VALUES (?,?,?,?,?,?)')
                        ->execute([$user['id'], $bizName, $bizType, $address, $desc, $lineId]);
                }
            }
            flash('ok', 'บันทึกโปรไฟล์แล้ว');
        }
        redirect('profile.php');
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
    $user = require_member();
    $biz = $user['member_type'] === 'business' ? member_get_business_profile((int) $user['id']) : null;
}

$daysLeft = days_until_subscription_end($user['subscription_end'] ?? null);

member_header('โปรไฟล์', 'profile', 'จัดการข้อมูลบัญชีและธุรกิจ');
?>
<?php if ($error): ?><div class="mapp-alert mapp-alert--error"><?= e($error) ?></div><?php endif; ?>

<div class="mapp-grid mapp-grid--profile">
  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">ข้อมูลบัญชี</h2>
      <p class="mapp-panel__desc">อีเมลใช้ล็อกอิน ไม่สามารถเปลี่ยนได้</p>
    </div>
    <dl class="mapp-dl">
      <div><dt>อีเมล</dt><dd><?= e($user['email']) ?></dd></div>
      <div><dt>ประเภท</dt><dd><?= e(member_type_label($user['member_type'])) ?></dd></div>
      <div><dt>สถานะ</dt><dd><?= status_badge($user['status']) ?></dd></div>
      <div><dt>ใช้ได้ถึง</dt><dd><?= $user['subscription_end'] ? e($user['subscription_end']) : '—' ?><?= $daysLeft !== null && $daysLeft >= 0 ? ' (เหลือ ' . $daysLeft . ' วัน)' : '' ?></dd></div>
    </dl>
    <form method="post" class="review-fields review-fields--stack">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <input type="hidden" name="action" value="profile" />
      <label class="field">
        <span class="field__label">ชื่อ-นามสกุล <em>*</em></span>
        <input type="text" name="full_name" required value="<?= e($user['full_name']) ?>" />
      </label>
      <label class="field">
        <span class="field__label">เบอร์โทร</span>
        <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" />
      </label>
      <?php if ($user['member_type'] === 'business'): ?>
        <h3 class="form-section-title">ข้อมูลธุรกิจ</h3>
        <label class="field">
          <span class="field__label">ชื่อธุรกิจ <em>*</em></span>
          <input type="text" name="business_name" value="<?= e($biz['business_name'] ?? '') ?>" required />
        </label>
        <label class="field">
          <span class="field__label">ประเภทธุรกิจ</span>
          <select name="business_type">
            <?php foreach (['hotel', 'restaurant', 'tour', 'other'] as $t): ?>
              <option value="<?= $t ?>" <?= ($biz['business_type'] ?? '') === $t ? 'selected' : '' ?>><?= e(business_type_label($t)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">
          <span class="field__label">ที่อยู่ / สถานที่</span>
          <input type="text" name="address" value="<?= e($biz['address'] ?? '') ?>" />
        </label>
        <label class="field">
          <span class="field__label">LINE ID</span>
          <input type="text" name="line_id" value="<?= e($biz['line_id'] ?? '') ?>" />
        </label>
        <label class="field">
          <span class="field__label">รายละเอียดธุรกิจ</span>
          <textarea name="description" rows="4"><?= e($biz['description'] ?? '') ?></textarea>
        </label>
      <?php endif; ?>
      <button type="submit" class="btn btn--primary">บันทึกโปรไฟล์</button>
    </form>
  </section>

  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">เปลี่ยนรหัสผ่าน</h2>
      <p class="mapp-panel__desc">ใช้รหัสผ่านที่จำง่ายและไม่ซ้ำกับที่อื่น</p>
    </div>
    <form method="post" class="review-fields review-fields--stack">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <input type="hidden" name="action" value="password" />
      <label class="field">
        <span class="field__label">รหัสผ่านปัจจุบัน</span>
        <input type="password" name="current_password" required autocomplete="current-password" />
      </label>
      <label class="field">
        <span class="field__label">รหัสผ่านใหม่</span>
        <input type="password" name="new_password" required minlength="6" autocomplete="new-password" />
      </label>
      <label class="field">
        <span class="field__label">ยืนยันรหัสผ่านใหม่</span>
        <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password" />
      </label>
      <button type="submit" class="btn btn--ghost-dark">เปลี่ยนรหัสผ่าน</button>
    </form>
  </section>
</div>

<?php member_footer(); ?>
