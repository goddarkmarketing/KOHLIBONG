<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_member();
$id = (int) ($_GET['id'] ?? 0);
$post = member_get_post($id, (int) $user['id']);
if (!$post) {
    flash('error', 'ไม่พบรายการ');
    redirect('posts.php');
}

$publicUrl = post_public_url($post);

member_header('รายละเอียดโพสต์', 'posts', e($post['title']));
?>
<div class="mapp-actions mapp-actions--top">
  <a href="posts.php" class="btn btn--ghost-dark"><i data-lucide="arrow-left" class="icon"></i> กลับ</a>
  <?php if ($post['status'] !== 'pending'): ?>
    <a href="post-edit.php?id=<?= (int) $post['id'] ?>" class="btn btn--blue">แก้ไข</a>
  <?php endif; ?>
  <?php if ($publicUrl): ?>
    <a href="<?= e($publicUrl) ?>" class="btn btn--green" target="_blank" rel="noopener">ดูบนเว็บหลัก</a>
  <?php endif; ?>
</div>

<div class="mapp-grid mapp-grid--view">
  <section class="mapp-panel">
    <?php if ($post['cover_image']): ?>
      <img class="post-view__cover" src="../<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>" />
    <?php endif; ?>
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title"><?= e($post['title']) ?></h2>
      <p class="mapp-panel__desc">
        <?= e(post_type_label($post['post_type'])) ?> · <?= status_badge($post['status']) ?> · <?= e(substr($post['created_at'], 0, 16)) ?>
      </p>
    </div>

    <?php if ($post['post_type'] === 'review'): ?>
      <dl class="mapp-dl">
        <div><dt>จองกับ</dt><dd><?= e($post['booking_place'] ?? '') ?></dd></div>
        <div><dt>วันที่ใช้บริการ</dt><dd><?= e($post['booking_date'] ?? '') ?></dd></div>
        <div><dt>ชื่อลูกค้า</dt><dd><?= e($post['guest_name'] ?? '') ?></dd></div>
        <div><dt>คะแนน</dt><dd><?= (int) $post['rating'] ?> ดาว</dd></div>
      </dl>
    <?php else: ?>
      <dl class="mapp-dl">
        <div><dt>สถานที่</dt><dd><?= e($post['location'] ?? '—') ?></dd></div>
        <div><dt>ราคา</dt><dd><?= e($post['price'] ?? '—') ?></dd></div>
      </dl>
    <?php endif; ?>

    <div class="post-view__content">
      <h3>รายละเอียด</h3>
      <p><?= nl2br(e($post['content'])) ?></p>
    </div>

    <?php if ($post['status'] === 'rejected' && $post['admin_note']): ?>
      <div class="mapp-banner mapp-banner--error" style="margin-top:1rem;margin-bottom:0">
        <i data-lucide="message-square-warning" class="mapp-banner__icon"></i>
        <div>
          <strong>หมายเหตุจากแอดมิน</strong>
          <p><?= e($post['admin_note']) ?></p>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">สถานะการเผยแพร่</h2>
    </div>
    <dl class="mapp-dl">
      <div><dt>สถานะ</dt><dd><?= status_badge($post['status']) ?></dd></div>
      <?php if ($post['reviewed_at']): ?>
        <div><dt>ตรวจเมื่อ</dt><dd><?= e(substr($post['reviewed_at'], 0, 16)) ?></dd></div>
      <?php endif; ?>
    </dl>
    <?php if ($post['status'] === 'pending'): ?>
      <p class="field__hint">รอแอดมินอนุมัติ — ยังแก้ไขไม่ได้จนกว่าจะตรวจเสร็จ</p>
    <?php elseif ($post['status'] === 'rejected'): ?>
      <a href="post-edit.php?id=<?= (int) $post['id'] ?>" class="btn btn--primary btn--block">แก้ไขแล้วส่งใหม่</a>
    <?php endif; ?>
  </section>
</div>
<?php member_footer(); ?>
