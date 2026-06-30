<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_active_member();
if ($user['member_type'] !== 'reviewer') {
    flash('error', 'เฉพาะสมาชิกประเภทรีวิว');
    redirect('dashboard.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 0);
        $bookingPlace = trim($_POST['booking_place'] ?? '');
        $bookingDate = $_POST['booking_date'] ?? '';
        $guestName = trim($_POST['guest_name'] ?? '');

        if (!$title || !$content || $rating < 1 || $rating > 5) {
            throw new RuntimeException('กรอกหัวข้อ รีวิว และให้ดาว 1-5');
        }
        if (!$bookingPlace || !$bookingDate || !$guestName) {
            throw new RuntimeException('ระบุที่จอง วันที่ และชื่อลูกค้า');
        }

        $cover = save_upload('cover', COVER_DIR, 'review');

        $stmt = db()->prepare('INSERT INTO posts (user_id, post_type, title, content, rating, booking_place, booking_date, guest_name, cover_image, status) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$user['id'], 'review', $title, $content, $rating, $bookingPlace, $bookingDate, $guestName, $cover, 'pending']);

        flash('ok', 'ส่งรีวิวแล้ว — รอแอดมินอนุมัติก่อนแสดงบนเว็บ');
        redirect('posts.php');
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

member_header('เขียนรีวิว', 'review', 'แบ่งฟอร์มเป็นขั้นตอน — กรอกข้อมูลจริงจากการใช้บริการ');
?>
<?php if ($error): ?><div class="mapp-alert mapp-alert--error"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="review-form">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />

  <section class="mapp-panel review-section">
    <div class="mapp-panel__head">
      <span class="review-step">1</span>
      <div>
        <h2 class="mapp-panel__title">ข้อมูลการจอง</h2>
        <p class="mapp-panel__desc">ระบุว่าจองกับที่ไหน เมื่อไหร่ และชื่อที่ใช้จอง</p>
      </div>
    </div>
    <div class="review-fields">
      <label class="field">
        <span class="field__label">จองกับที่ไหน <em>*</em></span>
        <input type="text" name="booking_place" required placeholder="ชื่อที่พัก / ทัวร์ / ร้านอาหาร"
          value="<?= e($_POST['booking_place'] ?? '') ?>" />
      </label>
      <label class="field">
        <span class="field__label">วันที่ใช้บริการ <em>*</em></span>
        <input type="date" name="booking_date" required value="<?= e($_POST['booking_date'] ?? '') ?>" />
      </label>
      <label class="field">
        <span class="field__label">ชื่อลูกค้า <em>*</em></span>
        <input type="text" name="guest_name" required placeholder="ชื่อที่ใช้จอง"
          value="<?= e($_POST['guest_name'] ?? '') ?>" />
      </label>
    </div>
  </section>

  <section class="mapp-panel review-section">
    <div class="mapp-panel__head">
      <span class="review-step">2</span>
      <div>
        <h2 class="mapp-panel__title">เนื้อหารีวิว</h2>
        <p class="mapp-panel__desc">หัวข้อสั้น ๆ ให้คะแนน และเล่าประสบการณ์จริง</p>
      </div>
    </div>
    <div class="review-fields review-fields--stack">
      <label class="field">
        <span class="field__label">หัวข้อรีวิว <em>*</em></span>
        <input type="text" name="title" required placeholder="เช่น พักโฮมสเตย์ริมทะเล บรรยากาศดีมาก"
          value="<?= e($_POST['title'] ?? '') ?>" />
      </label>

      <div class="field">
        <span class="field__label">ให้คะแนน <em>*</em></span>
        <div class="star-picker" role="radiogroup" aria-label="ให้คะแนน">
          <?php
          $selected = (int) ($_POST['rating'] ?? 0);
          for ($i = 5; $i >= 1; $i--):
          ?>
            <label class="star-picker__item" title="<?= $i ?> ดาว">
              <input type="radio" name="rating" value="<?= $i ?>" <?= $selected === $i ? 'checked' : '' ?> required />
              <i data-lucide="star" class="star-picker__icon"></i>
            </label>
          <?php endfor; ?>
        </div>
        <span class="star-picker__hint" id="starPickerLabel"><?= $selected ? $selected . ' ดาว' : 'แตะเพื่อเลือกคะแนน' ?></span>
      </div>

      <label class="field">
        <span class="field__label">ข้อความรีวิว <em>*</em></span>
        <textarea name="content" rows="6" required placeholder="เล่าประสบการณ์จริง — บริการ บรรยากาศ สิ่งที่ประทับใจ..."><?= e($_POST['content'] ?? '') ?></textarea>
        <span class="field__hint">รีวิวจะแสดงบนเว็บหลังแอดมินอนุมัติ</span>
      </label>
    </div>
  </section>

  <section class="mapp-panel review-section">
    <div class="mapp-panel__head">
      <span class="review-step">3</span>
      <div>
        <h2 class="mapp-panel__title">รูปปก</h2>
        <p class="mapp-panel__desc">อัปโหลดรูปหนึ่งภาพเป็นหน้าปกรีวิว (JPG, PNG, WEBP)</p>
      </div>
    </div>
    <label class="upload-zone">
      <input type="file" name="cover" id="coverInput" accept="image/jpeg,image/png,image/webp" required hidden />
      <span class="upload-zone__icon"><i data-lucide="image-plus"></i></span>
      <span class="upload-zone__title">คลิกเพื่อเลือกรูป</span>
      <span class="upload-zone__file" id="coverFileName">ยังไม่ได้เลือกไฟล์</span>
    </label>
  </section>

  <div class="review-submit">
    <a href="dashboard.php" class="btn btn--ghost-dark">ยกเลิก</a>
    <button type="submit" class="btn btn--purple">
      <i data-lucide="send" class="icon"></i> ส่งรีวิว (รออนุมัติ)
    </button>
  </div>
</form>

<?php member_footer(); ?>
