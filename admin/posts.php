<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note = trim($_POST['note'] ?? '') ?: null;
    try {
        if ($action === 'hide') {
            hide_post($id, (int) $admin['id'], $note);
            flash('ok', 'ถอนการเผยแพร่แล้ว');
        } else {
            approve_post($id, (int) $admin['id'], $action === 'approve', $note);
            flash('ok', $action === 'approve' ? 'อนุมัติโพสต์แล้ว' : 'ปฏิเสธโพสต์แล้ว');
        }
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
    }
    $type = $_POST['type_filter'] ?? 'all';
    redirect('posts.php' . ($type !== 'all' ? '?type=' . urlencode($type) : ''));
}

$typeFilter = $_GET['type'] ?? 'all';
$types = ['all', 'review', 'hotel', 'restaurant', 'tour'];
if (!in_array($typeFilter, $types, true)) {
    $typeFilter = 'all';
}

$sql = '
  SELECT p.*, u.full_name, u.email, u.member_type
  FROM posts p
  JOIN users u ON u.id = p.user_id
';
$params = [];
if ($typeFilter !== 'all') {
    $sql .= ' WHERE p.post_type = ?';
    $params[] = $typeFilter;
}
$sql .= " ORDER BY FIELD(p.status,'pending','approved','hidden','rejected'), p.created_at DESC LIMIT 100";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pendingCount = count(array_filter($rows, static fn ($r) => $r['status'] === 'pending'));

admin_header('โพสต์ / รีวิว', 'posts', 'ตรวจสอบเนื้อหาก่อนเผยแพร่บนหน้าเว็บ');
?>
<div class="admin-filters">
  <?php
  $typeLabels = [
      'all' => 'ทั้งหมด',
      'review' => 'รีวิว',
      'hotel' => 'ที่พัก',
      'restaurant' => 'ร้านอาหาร',
      'tour' => 'ทัวร์',
  ];
  foreach ($typeLabels as $key => $label):
      $cls = 'admin-filter' . ($typeFilter === $key ? ' is-active' : '');
  ?>
    <a href="posts.php?type=<?= e($key) ?>" class="<?= $cls ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<section class="admin-panel">
  <div class="admin-panel__head">
    <h2 class="admin-panel__title">รายการโพสต์และรีวิว</h2>
    <p class="admin-panel__desc">
      <?= $pendingCount > 0 ? "มี {$pendingCount} รายการรออนุมัติ" : 'ไม่มีรายการรออนุมัติในขณะนี้' ?>
    </p>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table admin-table--posts">
      <thead>
        <tr>
          <th class="at-col at-col--main">หัวข้อ</th>
          <th class="at-col at-col--short">ประเภท</th>
          <th class="at-col at-col--text">ผู้โพสต์</th>
          <th class="at-col at-col--short">สถานะ</th>
          <th class="at-col at-col--actions">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="admin-table__empty">ยังไม่มีโพสต์หรือรีวิว</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="at-col at-col--main">
              <div class="admin-table__cell">
                <a class="admin-table__primary admin-table__link" href="post-view.php?id=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a>
                <?php if ($r['post_type'] === 'review'): ?>
                  <span class="admin-table__sub">
                    <?= e($r['booking_place'] ?? '') ?> · <?= (int) $r['rating'] ?> ดาว
                  </span>
                <?php else: ?>
                  <span class="admin-table__sub"><?= e($r['location'] ?? '') ?><?= $r['price'] ? ' · ' . e($r['price']) : '' ?></span>
                <?php endif; ?>
              </div>
            </td>
            <td class="at-col at-col--short"><?= e(post_type_th($r['post_type'])) ?></td>
            <td class="at-col at-col--text">
              <div class="admin-table__cell">
                <span class="admin-table__primary"><?= e($r['full_name']) ?></span>
                <span class="admin-table__sub"><?= e(member_type_th($r['member_type'])) ?></span>
              </div>
            </td>
            <td class="at-col at-col--short"><?= status_badge($r['status']) ?></td>
            <td class="at-col at-col--actions">
              <div class="admin-table__cell admin-table__cell--actions">
              <div class="admin-row-actions">
                <a href="post-view.php?id=<?= (int) $r['id'] ?>" class="btn btn--sm btn--ghost-dark">ดู</a>
                <?php if ($r['status'] === 'pending'): ?>
                  <form method="post" class="admin-row-actions">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                    <input type="hidden" name="type_filter" value="<?= e($typeFilter) ?>" />
                    <button name="action" value="approve" class="btn btn--sm btn--green">อนุมัติ</button>
                    <button name="action" value="reject" class="btn btn--sm btn--login">ปฏิเสธ</button>
                  </form>
                <?php elseif ($r['status'] === 'approved'): ?>
                  <form method="post" class="admin-row-actions" onsubmit="return confirm('ถอนการเผยแพร่?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                    <input type="hidden" name="type_filter" value="<?= e($typeFilter) ?>" />
                    <button name="action" value="hide" class="btn btn--sm btn--login">ซ่อน</button>
                  </form>
                <?php else: ?>
                  <span class="admin-table__sub"><?= e($r['admin_note'] ?? '—') ?></span>
                <?php endif; ?>
              </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php admin_footer(); ?>
