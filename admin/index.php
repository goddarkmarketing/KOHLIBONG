<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
require_admin();

$pendingPay = (int) db()->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
$pendingPosts = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status='pending'")->fetchColumn();
$activeMembers = (int) db()->query("SELECT COUNT(*) FROM users WHERE role='member' AND status='active'")->fetchColumn();
$expiringSoon = (int) db()->query("
    SELECT COUNT(*) FROM users
    WHERE role='member' AND status='active'
      AND subscription_end IS NOT NULL
      AND subscription_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL " . EXPIRY_WARN_DAYS . " DAY)
")->fetchColumn();

$pendingPayments = db()->query("
  SELECT p.id, p.amount, p.created_at, u.full_name, u.email
  FROM payments p JOIN users u ON u.id = p.user_id
  WHERE p.status='pending'
  ORDER BY p.created_at ASC LIMIT 5
")->fetchAll();

$pendingPostRows = db()->query("
  SELECT p.id, p.title, p.post_type, p.created_at, u.full_name
  FROM posts p JOIN users u ON u.id = p.user_id
  WHERE p.status='pending'
  ORDER BY p.created_at ASC LIMIT 5
")->fetchAll();

admin_header('ภาพรวม', 'dashboard', 'สรุปงานรอดำเนินการและทางลัดไปหน้าจัดการ');
?>
<div class="admin-stats">
  <article class="admin-stat">
    <span class="admin-stat__icon admin-stat__icon--warn"><i data-lucide="receipt"></i></span>
    <div>
      <span class="admin-stat__label">สลิปรอตรวจ</span>
      <span class="admin-stat__value"><?= $pendingPay ?></span>
      <span class="admin-stat__hint">รายการชำระเงินรออนุมัติ</span>
    </div>
  </article>
  <article class="admin-stat">
    <span class="admin-stat__icon admin-stat__icon--info"><i data-lucide="file-check"></i></span>
    <div>
      <span class="admin-stat__label">โพสต์รออนุมัติ</span>
      <span class="admin-stat__value"><?= $pendingPosts ?></span>
      <span class="admin-stat__hint">รีวิวและโพสต์ธุรกิจรอเผยแพร่</span>
    </div>
  </article>
  <article class="admin-stat">
    <span class="admin-stat__icon admin-stat__icon--ok"><i data-lucide="users"></i></span>
    <div>
      <span class="admin-stat__label">สมาชิกใช้งาน</span>
      <span class="admin-stat__value"><?= $activeMembers ?></span>
      <span class="admin-stat__hint"><?= $expiringSoon ?> คนใกล้หมดอายุ</span>
    </div>
  </article>
</div>

<div class="admin-actions">
  <a href="payments.php" class="btn btn--primary"><i data-lucide="receipt" class="icon"></i> ตรวจสลิปสมาชิก</a>
  <a href="members.php" class="btn btn--blue"><i data-lucide="users" class="icon"></i> รายชื่อสมาชิก</a>
  <a href="posts.php" class="btn btn--blue"><i data-lucide="file-check" class="icon"></i> อนุมัติโพสต์ / รีวิว</a>
</div>

<div class="admin-grid-2">
  <section class="admin-panel">
    <div class="admin-panel__head admin-panel__head--row">
      <h2 class="admin-panel__title">สลิปรอตรวจ</h2>
      <a href="payments.php" class="admin-table__link">ดูทั้งหมด →</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table admin-table--compact">
        <thead><tr><th class="at-col at-col--main">สมาชิก</th><th class="at-col at-col--short">จำนวน</th><th class="at-col at-col--short">วันที่</th></tr></thead>
        <tbody>
          <?php if (!$pendingPayments): ?>
            <tr><td colspan="3" class="admin-table__empty">ไม่มีรายการรอตรวจ</td></tr>
          <?php endif; ?>
          <?php foreach ($pendingPayments as $p): ?>
            <tr>
              <td class="at-col at-col--main"><span class="admin-table__primary"><?= e($p['full_name']) ?></span></td>
              <td class="at-col at-col--short"><?= number_format((float) $p['amount'], 0) ?> บ.</td>
              <td class="at-col at-col--short"><?= e(substr($p['created_at'], 0, 10)) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="admin-panel">
    <div class="admin-panel__head admin-panel__head--row">
      <h2 class="admin-panel__title">โพสต์รออนุมัติ</h2>
      <a href="posts.php" class="admin-table__link">ดูทั้งหมด →</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table admin-table--compact">
        <thead><tr><th class="at-col at-col--main">หัวข้อ</th><th class="at-col at-col--short">ประเภท</th><th class="at-col at-col--text">ผู้ส่ง</th></tr></thead>
        <tbody>
          <?php if (!$pendingPostRows): ?>
            <tr><td colspan="3" class="admin-table__empty">ไม่มีรายการรออนุมัติ</td></tr>
          <?php endif; ?>
          <?php foreach ($pendingPostRows as $p): ?>
            <tr>
              <td class="at-col at-col--main"><a class="admin-table__link" href="post-view.php?id=<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a></td>
              <td class="at-col at-col--short"><?= e(post_type_th($p['post_type'])) ?></td>
              <td class="at-col at-col--text"><?= e($p['full_name']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<div class="admin-hint">
  <strong>อนุมัติสลิป</strong> = เปิดใช้งานสมาชิก <?= SUBSCRIPTION_DAYS ?> วัน &nbsp;·&nbsp;
  <strong>อนุมัติโพสต์</strong> = แสดงบนหน้าเว็บหลัก &nbsp;·&nbsp;
  <strong>ซ่อน</strong> = ถอนจากเว็บโดยไม่ลบข้อมูล
</div>
<?php admin_footer(); ?>
