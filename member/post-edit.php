<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_active_member();
$id = (int) ($_GET['id'] ?? 0);
$post = member_get_post($id, (int) $user['id']);
if (!$post) {
    flash('error', 'ไม่พบรายการ');
    redirect('posts.php');
}
if ($post['status'] === 'pending') {
    flash('info', 'รายการรอตรวจ — ยังแก้ไขไม่ได้');
    redirect('post-view.php?id=' . $id);
}

if ($post['post_type'] === 'review' && $user['member_type'] !== 'reviewer') {
    flash('error', 'ไม่มีสิทธิ์แก้ไขรีวิว');
    redirect('posts.php');
}
if ($post['post_type'] !== 'review' && $user['member_type'] !== 'business') {
    flash('error', 'ไม่มีสิทธิ์แก้ไขโพสต์ธุรกิจ');
    redirect('posts.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (!$title || !$content) {
            throw new RuntimeException('กรอกหัวข้อและรายละเอียด');
        }

        if ($post['post_type'] === 'review') {
            $rating = (int) ($_POST['rating'] ?? 0);
            $bookingPlace = trim($_POST['booking_place'] ?? '');
            $bookingDate = $_POST['booking_date'] ?? '';
            $guestName = trim($_POST['guest_name'] ?? '');
            if ($rating < 1 || $rating > 5 || !$bookingPlace || !$bookingDate || !$guestName) {
                throw new RuntimeException('กรอกข้อมูลรีวิวให้ครบ');
            }
            $cover = save_upload_optional('cover', COVER_DIR, 'review', $post['cover_image']);
            $stmt = db()->prepare('UPDATE posts SET title=?, content=?, rating=?, booking_place=?, booking_date=?, guest_name=?, cover_image=?, status=?, admin_note=NULL, reviewed_by=NULL, reviewed_at=NULL WHERE id=? AND user_id=?');
            $stmt->execute([$title, $content, $rating, $bookingPlace, $bookingDate, $guestName, $cover, 'pending', $id, $user['id']]);
        } else {
            $postType = $_POST['post_type'] ?? $post['post_type'];
            $price = trim($_POST['price'] ?? '');
            $location = trim($_POST['location'] ?? '');
            if (!in_array($postType, ['hotel', 'restaurant', 'tour'], true)) {
                throw new RuntimeException('ประเภทโพสต์ไม่ถูกต้อง');
            }
            $cover = save_upload_optional('cover', COVER_DIR, 'listing', $post['cover_image']);
            $stmt = db()->prepare('UPDATE posts SET post_type=?, title=?, content=?, cover_image=?, price=?, location=?, status=?, admin_note=NULL, reviewed_by=NULL, reviewed_at=NULL WHERE id=? AND user_id=?');
            $stmt->execute([$postType, $title, $content, $cover, $price, $location, 'pending', $id, $user['id']]);
        }

        flash('ok', 'ส่งแก้ไขแล้ว — รอแอดมินอนุมัติอีกครั้ง');
        redirect('post-view.php?id=' . $id);
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
        $post = member_get_post($id, (int) $user['id']) ?? $post;
    }
}

member_header('แก้ไขโพสต์', 'posts', e($post['title']));
?>
<?php if ($error): ?><div class="mapp-alert mapp-alert--error"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="review-form">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />

  <?php if ($post['post_type'] === 'review'): ?>
    <section class="mapp-panel review-section">
      <div class="mapp-panel__head"><h2 class="mapp-panel__title">ข้อมูลการจอง</h2></div>
      <div class="review-fields">
        <label class="field"><span class="field__label">จองกับที่ไหน</span><input type="text" name="booking_place" required value="<?= e($post['booking_place'] ?? '') ?>" /></label>
        <label class="field"><span class="field__label">วันที่ใช้บริการ</span><input type="date" name="booking_date" required value="<?= e($post['booking_date'] ?? '') ?>" /></label>
        <label class="field"><span class="field__label">ชื่อลูกค้า</span><input type="text" name="guest_name" required value="<?= e($post['guest_name'] ?? '') ?>" /></label>
      </div>
    </section>
    <section class="mapp-panel review-section">
      <div class="mapp-panel__head"><h2 class="mapp-panel__title">เนื้อหารีวิว</h2></div>
      <div class="review-fields review-fields--stack">
        <label class="field"><span class="field__label">หัวข้อ</span><input type="text" name="title" required value="<?= e($post['title']) ?>" /></label>
        <div class="field">
          <span class="field__label">คะแนน</span>
          <div class="star-picker">
            <?php $selected = (int) $post['rating']; for ($i = 5; $i >= 1; $i--): ?>
              <label class="star-picker__item"><input type="radio" name="rating" value="<?= $i ?>" <?= $selected === $i ? 'checked' : '' ?> required /><i data-lucide="star" class="star-picker__icon"></i></label>
            <?php endfor; ?>
          </div>
        </div>
        <label class="field"><span class="field__label">ข้อความ</span><textarea name="content" rows="6" required><?= e($post['content']) ?></textarea></label>
      </div>
    </section>
  <?php else: ?>
    <section class="mapp-panel review-section">
      <div class="review-fields">
        <label class="field"><span class="field__label">ประเภท</span>
          <select name="post_type">
            <?php foreach (['hotel', 'restaurant', 'tour'] as $t): ?>
              <option value="<?= $t ?>" <?= $post['post_type'] === $t ? 'selected' : '' ?>><?= e(post_type_label($t)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field"><span class="field__label">หัวข้อ</span><input type="text" name="title" required value="<?= e($post['title']) ?>" /></label>
        <label class="field"><span class="field__label">ราคา</span><input type="text" name="price" value="<?= e($post['price'] ?? '') ?>" /></label>
        <label class="field"><span class="field__label">สถานที่</span><input type="text" name="location" value="<?= e($post['location'] ?? '') ?>" /></label>
        <label class="field span-2"><span class="field__label">รายละเอียด</span><textarea name="content" rows="6" required><?= e($post['content']) ?></textarea></label>
      </div>
    </section>
  <?php endif; ?>

  <section class="mapp-panel review-section">
    <div class="mapp-panel__head"><h2 class="mapp-panel__title">รูปปก</h2><p class="mapp-panel__desc">เว้นว่างถ้าไม่เปลี่ยนรูป</p></div>
    <?php if ($post['cover_image']): ?>
      <img class="post-view__thumb" src="../<?= e($post['cover_image']) ?>" alt="" />
    <?php endif; ?>
    <label class="upload-zone" style="margin-top:12px">
      <input type="file" name="cover" id="coverInput" accept="image/jpeg,image/png,image/webp" hidden />
      <span class="upload-zone__icon"><i data-lucide="image-plus"></i></span>
      <span class="upload-zone__title">เลือกรูปใหม่ (ถ้าต้องการ)</span>
      <span class="upload-zone__file" id="coverFileName">ไม่เปลี่ยนรูป</span>
    </label>
  </section>

  <div class="review-submit">
    <a href="post-view.php?id=<?= (int) $post['id'] ?>" class="btn btn--ghost-dark">ยกเลิก</a>
    <button type="submit" class="btn btn--purple"><i data-lucide="send" class="icon"></i> ส่งแก้ไข (รออนุมัติ)</button>
  </div>
</form>
<?php member_footer(); ?>
