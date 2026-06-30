<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_member();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $post = member_get_post($id, (int) $user['id']);
    if ($post) {
        db()->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
        flash('ok', 'ลบรายการแล้ว');
    }
    redirect('posts.php');
}

$posts = db()->prepare('SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC');
$posts->execute([$user['id']]);
$rows = $posts->fetchAll();

member_header('โพสต์ของฉัน', 'posts', 'จัดการรีวิวและโพสต์ทั้งหมด');
?>
<section class="mapp-panel">
  <div class="mapp-panel__head mapp-panel__head--row">
    <div>
      <h2 class="mapp-panel__title">รายการทั้งหมด (<?= count($rows) ?>)</h2>
      <p class="mapp-panel__desc">ดู แก้ไข ลบ หรือเปิดดูบนเว็บหลังอนุมัติ</p>
    </div>
    <?php if ($user['status'] === 'active' && subscription_valid($user)): ?>
      <a href="<?= $user['member_type'] === 'reviewer' ? 'review-new.php' : 'listing-new.php' ?>" class="btn btn--primary btn--sm">
        <i data-lucide="plus" class="icon"></i> สร้างใหม่
      </a>
    <?php endif; ?>
  </div>
  <div class="mapp-table-wrap">
    <table class="mapp-table mapp-table--posts">
      <thead>
        <tr>
          <th class="at-col at-col--main">หัวข้อ</th>
          <th class="at-col at-col--short">ประเภท</th>
          <th class="at-col at-col--short">สถานะ</th>
          <th class="at-col at-col--short">วันที่</th>
          <th class="at-col at-col--actions">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="mapp-table__empty">ยังไม่มีรายการ — เริ่มสร้างได้จากเมนูด้านซ้าย</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $p): ?>
          <tr>
            <td class="at-col at-col--main">
              <div class="mapp-table__cell">
                <span class="mapp-table__primary"><?= e($p['title']) ?></span>
                <?php if (in_array($p['status'], ['rejected', 'hidden'], true) && $p['admin_note']): ?>
                  <span class="mapp-table__sub mapp-table__sub--bad">แอดมิน: <?= e($p['admin_note']) ?></span>
                <?php endif; ?>
              </div>
            </td>
            <td class="at-col at-col--short"><?= e(post_type_label($p['post_type'])) ?></td>
            <td class="at-col at-col--short"><?= status_badge($p['status']) ?></td>
            <td class="at-col at-col--short"><?= e(substr($p['created_at'], 0, 10)) ?></td>
            <td class="at-col at-col--actions">
              <div class="mapp-table__cell mapp-table__cell--actions">
              <div class="mapp-row-actions">
                <a href="post-view.php?id=<?= (int) $p['id'] ?>" class="btn btn--sm btn--ghost-dark">ดู</a>
                <?php if ($p['status'] !== 'pending'): ?>
                  <a href="post-edit.php?id=<?= (int) $p['id'] ?>" class="btn btn--sm btn--blue">แก้ไข</a>
                <?php endif; ?>
                <?php if ($p['status'] === 'approved'): ?>
                  <a href="<?= e(post_public_url($p)) ?>" class="btn btn--sm btn--green" target="_blank" rel="noopener">บนเว็บ</a>
                <?php endif; ?>
                <form method="post" class="mapp-inline-form" onsubmit="return confirm('ลบรายการนี้?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                  <button type="submit" class="btn btn--sm btn--login">ลบ</button>
                </form>
              </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php member_footer(); ?>
