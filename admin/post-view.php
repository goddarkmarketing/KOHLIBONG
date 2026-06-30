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
    $action = $_POST['action'] ?? '';
    $note = trim($_POST['note'] ?? '') ?: null;
    try {
        if ($action === 'approve') {
            approve_post($id, (int) $admin['id'], true, $note);
            flash('ok', 'อนุมัติโพสต์แล้ว');
        } elseif ($action === 'reject') {
            approve_post($id, (int) $admin['id'], false, $note);
            flash('ok', 'ปฏิเสธโพสต์แล้ว');
        } elseif ($action === 'hide') {
            hide_post($id, (int) $admin['id'], $note);
            flash('ok', 'ถอนการเผยแพร่แล้ว');
        } else {
            throw new RuntimeException('คำสั่งไม่ถูกต้อง');
        }
        redirect('post-view.php?id=' . $id);
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
        redirect('post-view.php?id=' . $id);
    }
}

admin_header('ตรวจโพสต์', 'posts', e($post['title']));
?>
<div class="admin-back">
  <a href="posts.php" class="admin-table__link">← กลับรายการโพสต์</a>
</div>

<section class="admin-panel admin-panel--view">
  <div class="admin-panel__head admin-panel__head--row">
    <div>
      <h2 class="admin-panel__title"><?= e($post['title']) ?></h2>
      <p class="admin-panel__desc">
        <?= e(post_type_th($post['post_type'])) ?> · <?= status_badge($post['status']) ?>
        · โดย <?= e($post['full_name']) ?> (<?= e(member_type_th($post['member_type'])) ?>)
      </p>
    </div>
    <?php if ($post['cover_image']): ?>
      <a class="btn btn--sm btn--blue" href="../<?= e($post['cover_image']) ?>" target="_blank" rel="noopener">ดูรูปปก</a>
    <?php endif; ?>
  </div>

  <?php if ($post['post_type'] === 'review'): ?>
    <dl class="admin-dl">
      <div><dt>จองกับ</dt><dd><?= e($post['booking_place'] ?? '—') ?></dd></div>
      <div><dt>วันที่ใช้บริการ</dt><dd><?= e($post['booking_date'] ?? '—') ?></dd></div>
      <div><dt>ชื่อลูกค้า</dt><dd><?= e($post['guest_name'] ?? '—') ?></dd></div>
      <div><dt>คะแนน</dt><dd><?= (int) $post['rating'] ?> / 5</dd></div>
    </dl>
  <?php else: ?>
    <dl class="admin-dl">
      <div><dt>สถานที่</dt><dd><?= e($post['location'] ?? '—') ?></dd></div>
      <div><dt>ราคา</dt><dd><?= e($post['price'] ?? '—') ?></dd></div>
    </dl>
  <?php endif; ?>

  <div class="admin-content-box">
    <h3>เนื้อหา</h3>
    <p><?= nl2br(e($post['content'])) ?></p>
  </div>

  <?php if ($post['admin_note']): ?>
    <div class="admin-note-box">
      <strong>หมายเหตุแอดมิน</strong>
      <p><?= e($post['admin_note']) ?></p>
    </div>
  <?php endif; ?>

  <div class="admin-meta-row">
    <span>อีเมล: <?= e($post['email']) ?></span>
    <?php if ($post['phone']): ?><span>โทร: <?= e($post['phone']) ?></span><?php endif; ?>
    <span>ส่งเมื่อ: <?= e(substr($post['created_at'], 0, 16)) ?></span>
    <?php if ($post['reviewed_at']): ?><span>ตรวจเมื่อ: <?= e(substr($post['reviewed_at'], 0, 16)) ?></span><?php endif; ?>
  </div>

  <?php if ($post['status'] === 'pending'): ?>
    <form method="post" class="admin-action-bar">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <input type="text" name="note" placeholder="หมายเหตุ (ถ้ามี)" class="input-sm" />
      <button name="action" value="approve" class="btn btn--green">อนุมัติเผยแพร่</button>
      <button name="action" value="reject" class="btn btn--login">ปฏิเสธ</button>
    </form>
  <?php elseif ($post['status'] === 'approved'): ?>
    <form method="post" class="admin-action-bar" onsubmit="return confirm('ถอนการเผยแพร่จากเว็บ?');">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <input type="text" name="note" placeholder="เหตุผล (ถ้ามี)" class="input-sm" />
      <button name="action" value="hide" class="btn btn--login">ถอนการเผยแพร่</button>
      <a href="<?= e(post_public_url($post)) ?>" class="btn btn--blue" target="_blank" rel="noopener">ดูบนเว็บ</a>
    </form>
  <?php endif; ?>
</section>
<?php admin_footer(); ?>
