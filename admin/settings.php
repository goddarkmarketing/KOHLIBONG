<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
$admin = require_admin();

$settings = site_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $action = $_POST['action'] ?? 'save';
        if ($action === 'lock_install') {
            site_write_install_lock();
            flash('ok', 'ล็อกการติดตั้งแล้ว — setup/install scripts จะถูกปิด');
        } elseif ($action === 'unlock_install') {
            $lock = site_install_lock_path();
            if (is_file($lock)) {
                unlink($lock);
            }
            flash('ok', 'ปลดล็อกการติดตั้งแล้ว (ใช้เฉพาะตอนพัฒนา)');
        } else {
            site_settings_save($_POST);
            flash('ok', 'บันทึกการตั้งค่าแล้ว');
        }
        redirect('settings.php');
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
        redirect('settings.php');
    }
}

$locked = site_install_locked();
$lineUrl = site_settings_line_url((string) $settings['contact_line']);

admin_header('ตั้งค่าระบบ', 'settings', 'ค่าสมาชิก ช่องทางติดต่อ และข้อความบนหน้าเว็บ');
?>
<section class="admin-panel">
  <div class="admin-panel__head">
    <h2 class="admin-panel__title">สมาชิกและการชำระเงิน</h2>
    <p class="admin-panel__desc">ค่าเหล่านี้ใช้ในหน้าสมัคร / ต่ออายุ / ช่วยเหลือ และตอนอนุมัติสลิป</p>
  </div>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
    <input type="hidden" name="action" value="save" />

    <div class="admin-form__grid">
      <label class="admin-field">
        <span>ค่าสมาชิก (บาท)</span>
        <input type="number" name="membership_fee" min="1" max="100000" required value="<?= (int) $settings['membership_fee'] ?>" />
      </label>
      <label class="admin-field">
        <span>จำนวนวันต่อรอบ</span>
        <input type="number" name="subscription_days" min="1" max="365" required value="<?= (int) $settings['subscription_days'] ?>" />
      </label>
    </div>

    <label class="admin-field">
      <span>ข้อมูลโอนเงิน</span>
      <textarea name="bank_info" rows="2" required><?= e((string) $settings['bank_info']) ?></textarea>
    </label>

    <div class="admin-form__grid">
      <label class="admin-field">
        <span>LINE Official</span>
        <input type="text" name="contact_line" value="<?= e((string) $settings['contact_line']) ?>" placeholder="@talaytrang" />
        <small class="admin-field__hint">ลิงก์ที่ได้: <a href="<?= e($lineUrl) ?>" target="_blank" rel="noopener"><?= e($lineUrl) ?></a></small>
      </label>
      <label class="admin-field">
        <span>อีเมลติดต่อ</span>
        <input type="email" name="contact_email" value="<?= e((string) $settings['contact_email']) ?>" />
      </label>
    </div>

    <hr class="admin-divider" />

    <h3 class="admin-panel__subtitle">ออฟฟิศ / หน้าติดต่อ / Hero</h3>
    <p class="admin-panel__desc">ข้อความเหล่านี้จะถูกดึงไปแสดงบนหน้าติดต่อและส่วนติดต่อหน้าแรก</p>

    <label class="admin-field">
      <span>ชื่อท่าเรือ</span>
      <input type="text" name="pier_name" value="<?= e((string) $settings['pier_name']) ?>" />
    </label>
    <label class="admin-field">
      <span>ที่อยู่ออฟฟิศ</span>
      <textarea name="office_address" rows="2"><?= e((string) $settings['office_address']) ?></textarea>
    </label>
    <label class="admin-field">
      <span>เวลาทำการ</span>
      <input type="text" name="office_hours" value="<?= e((string) $settings['office_hours']) ?>" />
    </label>

    <div class="admin-form__grid">
      <label class="admin-field">
        <span>แผนที่ Latitude</span>
        <input type="text" name="map_lat" value="<?= e((string) $settings['map_lat']) ?>" />
      </label>
      <label class="admin-field">
        <span>แผนที่ Longitude</span>
        <input type="text" name="map_lng" value="<?= e((string) $settings['map_lng']) ?>" />
      </label>
    </div>

    <div class="admin-form__grid">
      <label class="admin-field">
        <span>ติดต่อจองทัวร์ 1</span>
        <input type="text" name="tour_contact_1" value="<?= e((string) $settings['tour_contact_1']) ?>" />
      </label>
      <label class="admin-field">
        <span>ติดต่อจองทัวร์ 2</span>
        <input type="text" name="tour_contact_2" value="<?= e((string) $settings['tour_contact_2']) ?>" />
      </label>
    </div>
    <label class="admin-field">
      <span>ติดต่อจองเรือ</span>
      <input type="text" name="boat_contact_1" value="<?= e((string) $settings['boat_contact_1']) ?>" />
    </label>

    <label class="admin-field">
      <span>ข้อความ Hero</span>
      <input type="text" name="hero_caption" value="<?= e((string) $settings['hero_caption']) ?>" />
    </label>
    <label class="admin-field">
      <span>แท็กไลน์ฟุตเตอร์</span>
      <input type="text" name="footer_tagline" value="<?= e((string) $settings['footer_tagline']) ?>" />
    </label>

    <div class="admin-action-bar">
      <button type="submit" class="btn btn--green">บันทึกการตั้งค่า</button>
      <a class="btn btn--ghost-dark" href="../contact.html" target="_blank" rel="noopener">ดูหน้าติดต่อ</a>
    </div>
  </form>
</section>

<section class="admin-panel">
  <div class="admin-panel__head">
    <h2 class="admin-panel__title">ความปลอดภัยการติดตั้ง</h2>
    <p class="admin-panel__desc">
      สถานะปัจจุบัน:
      <?php if ($locked): ?>
        <strong class="admin-text-ok">ล็อกแล้ว</strong> — setup.php / install scripts ถูกปิด
      <?php else: ?>
        <strong class="admin-text-warn">ยังไม่ล็อก</strong> — แนะนำล็อกก่อนขึ้น production
      <?php endif; ?>
    </p>
  </div>
  <form method="post" class="admin-action-bar">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
    <?php if ($locked): ?>
      <button name="action" value="unlock_install" class="btn btn--login" onclick="return confirm('ปลดล็อกสำหรับพัฒนาเท่านั้น?');">ปลดล็อกการติดตั้ง</button>
    <?php else: ?>
      <button name="action" value="lock_install" class="btn btn--green">ล็อกการติดตั้งทันที</button>
    <?php endif; ?>
  </form>
  <p class="admin-panel__desc">
    บน production แนะนำตั้งใน <code>config.local.php</code>:
    <code>define('SHOW_DEMO_ACCOUNTS', false);</code>
    และ
    <code>define('ALLOW_SETUP', false);</code>
  </p>
</section>
<?php admin_footer(); ?>
