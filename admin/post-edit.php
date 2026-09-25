<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$post = admin_get_post($id);
if (!$post) {
    flash('error', 'ไม่พบโพสต์');
    redirect('posts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        admin_update_post($id, $_POST);
        flash('ok', 'บันทึกโพสต์แล้ว');
        redirect('post-view.php?id=' . $id);
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
        redirect('post-edit.php?id=' . $id);
    }
}

admin_header('แก้ไขโพสต์', 'posts', e($post['title']));
?>
<div class="admin-back">
  <a href="post-view.php?id=<?= $id ?>" class="admin-table__link">← กลับหน้ารายละเอียด</a>
</div>

<section class="admin-panel">
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
    <label class="admin-field">
      <span>หัวข้อ</span>
      <input type="text" name="title" required value="<?= e($post['title']) ?>" />
    </label>
    <label class="admin-field">
      <span>เนื้อหา</span>
      <textarea name="content" rows="8" required><?= e($post['content']) ?></textarea>
    </label>

    <?php if ($post['post_type'] === 'review'): ?>
      <div class="admin-form__grid">
        <label class="admin-field">
          <span>จองกับ</span>
          <input type="text" name="booking_place" value="<?= e((string) ($post['booking_place'] ?? '')) ?>" />
        </label>
        <label class="admin-field">
          <span>วันที่ใช้บริการ</span>
          <input type="date" name="booking_date" value="<?= e((string) ($post['booking_date'] ?? '')) ?>" />
        </label>
        <label class="admin-field">
          <span>ชื่อลูกค้า</span>
          <input type="text" name="guest_name" value="<?= e((string) ($post['guest_name'] ?? '')) ?>" />
        </label>
        <label class="admin-field">
          <span>คะแนน (1-5)</span>
          <input type="number" name="rating" min="1" max="5" value="<?= (int) ($post['rating'] ?? 5) ?>" />
        </label>
      </div>
    <?php else: ?>
      <div class="admin-form__grid">
        <label class="admin-field">
          <span>สถานที่</span>
          <input type="text" name="location" value="<?= e((string) ($post['location'] ?? '')) ?>" />
        </label>
        <label class="admin-field">
          <span>ราคา</span>
          <input type="text" name="price" value="<?= e((string) ($post['price'] ?? '')) ?>" />
        </label>
      </div>
    <?php endif; ?>

    <div class="admin-action-bar">
      <button type="submit" class="btn btn--green">บันทึก</button>
      <a href="post-view.php?id=<?= $id ?>" class="btn btn--ghost-dark">ยกเลิก</a>
    </div>
  </form>
</section>
<?php admin_footer(); ?>
