<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_active_member();
if ($user['member_type'] !== 'business') {
    flash('error', 'เฉพาะผู้ประกอบการ');
    redirect('dashboard.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $postType = $_POST['post_type'] ?? 'hotel';
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if (!$title || !$content) {
            throw new RuntimeException('กรอกหัวข้อและรายละเอียด');
        }
        if (!in_array($postType, ['hotel', 'restaurant', 'tour'], true)) {
            throw new RuntimeException('ประเภทโพสต์ไม่ถูกต้อง');
        }

        $cover = save_upload('cover', COVER_DIR, 'listing');

        $stmt = db()->prepare('INSERT INTO posts (user_id, post_type, title, content, cover_image, price, location, status) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$user['id'], $postType, $title, $content, $cover, $price, $location, 'pending']);

        flash('ok', 'ส่งโพสต์แล้ว — รอแอดมินอนุมัติก่อนแสดงบนเว็บ');
        redirect('posts.php');
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

member_header('โพสต์ข้อมูล', 'listing');
?>
<?php if ($error): ?><div class="mapp-alert mapp-alert--error"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="review-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
    <section class="mapp-panel">
      <div class="mapp-panel__head">
        <h2 class="mapp-panel__title">โพสต์ที่พัก / ร้านอาหาร / บริการ</h2>
        <p class="mapp-panel__desc">กรอกรายละเอียดและรูปปก — แอดมินตรวจก่อนเผยแพร่</p>
      </div>
      <div class="review-fields">
      <label class="field"><span class="field__label">ประเภท</span>
        <select name="post_type">
          <option value="hotel">ที่พัก</option>
          <option value="restaurant">ร้านอาหาร</option>
          <option value="tour">ทัวร์/บริการ</option>
        </select>
      </label>
      <label class="field"><span class="field__label">ชื่อหัวข้อ</span><input type="text" name="title" required /></label>
      <label class="field"><span class="field__label">ราคาเริ่มต้น</span><input type="text" name="price" placeholder="เช่น 1,290" /></label>
      <label class="field"><span class="field__label">สถานที่</span><input type="text" name="location" placeholder="เกาะลิบง, ตรัง" /></label>
      <label class="field span-2"><span class="field__label">รูปปก</span><input type="file" name="cover" accept="image/jpeg,image/png,image/webp" required /></label>
      <label class="field span-2"><span class="field__label">รายละเอียด</span><textarea name="content" rows="6" required placeholder="บริการ สิ่งอำนวยความสะดวก จุดเด่น..."></textarea></label>
      </div>
    </section>
    <div class="review-submit">
      <a href="posts.php" class="btn btn--ghost-dark">ยกเลิก</a>
      <button type="submit" class="btn btn--blue"><i data-lucide="send" class="icon"></i> ส่งโพสต์ (รออนุมัติ)</button>
    </div>
  </form>
<?php member_footer(); ?>
