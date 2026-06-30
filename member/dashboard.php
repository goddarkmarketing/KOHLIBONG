<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_member();
$postsStmt = db()->prepare('SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$postsStmt->execute([$user['id']]);
$myPosts = $postsStmt->fetchAll();

$paymentsStmt = db()->prepare('SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$paymentsStmt->execute([$user['id']]);
$myPayments = $paymentsStmt->fetchAll();

$biz = $user['member_type'] === 'business' ? member_get_business_profile((int) $user['id']) : null;

$pendingPosts = count(array_filter($myPosts, static fn ($p) => $p['status'] === 'pending'));
$approvedPosts = count(array_filter($myPosts, static fn ($p) => $p['status'] === 'approved'));
$rejectedPosts = count(array_filter($myPosts, static fn ($p) => $p['status'] === 'rejected'));

member_header('แดชบอร์ด', 'dashboard', 'สวัสดี, ' . $user['full_name']);
?>

<div class="mapp-stats">
  <article class="mapp-stat">
    <span class="mapp-stat__icon mapp-stat__icon--blue"><i data-lucide="badge-check"></i></span>
    <div>
      <span class="mapp-stat__label">สถานะสมาชิก</span>
      <span class="mapp-stat__value"><?= e(member_status_label($user['status'])) ?></span>
    </div>
  </article>
  <article class="mapp-stat">
    <span class="mapp-stat__icon mapp-stat__icon--teal"><i data-lucide="calendar"></i></span>
    <div>
      <span class="mapp-stat__label">ใช้ได้ถึง</span>
      <span class="mapp-stat__value"><?= $user['subscription_end'] ? e($user['subscription_end']) : '—' ?></span>
    </div>
  </article>
  <article class="mapp-stat">
    <span class="mapp-stat__icon mapp-stat__icon--purple"><i data-lucide="file-text"></i></span>
    <div>
      <span class="mapp-stat__label">รีวิว / โพสต์</span>
      <span class="mapp-stat__value"><?= $approvedPosts ?> อนุมัติ · <?= $pendingPosts ?> รอ · <?= $rejectedPosts ?> ปฏิเสธ</span>
    </div>
  </article>
</div>

<?php if ($user['status'] === 'active' && subscription_valid($user)): ?>
  <div class="mapp-actions">
    <?php if ($user['member_type'] === 'reviewer'): ?>
      <a href="review-new.php" class="btn btn--primary"><i data-lucide="star" class="icon"></i> เขียนรีวิวใหม่</a>
    <?php else: ?>
      <a href="listing-new.php" class="btn btn--primary"><i data-lucide="plus-square" class="icon"></i> โพสต์ที่พัก / ร้านอาหาร</a>
    <?php endif; ?>
    <a href="posts.php" class="btn btn--blue"><i data-lucide="folder-open" class="icon"></i> โพสต์ของฉัน</a>
    <a href="renew.php" class="btn btn--ghost-dark"><i data-lucide="credit-card" class="icon"></i> ต่ออายุ</a>
  </div>
<?php endif; ?>

<?php if ($biz): ?>
  <section class="mapp-panel mapp-panel--biz">
    <div class="mapp-panel__head mapp-panel__head--row">
      <div>
        <h2 class="mapp-panel__title"><i data-lucide="store" class="icon"></i> <?= e($biz['business_name']) ?></h2>
        <p class="mapp-panel__desc"><?= e($biz['address'] ?? '') ?> · <a href="profile.php">แก้ไขโปรไฟล์ธุรกิจ</a></p>
      </div>
    </div>
  </section>
<?php endif; ?>

<div class="mapp-grid">
  <section class="mapp-panel">
    <div class="mapp-panel__head mapp-panel__head--row">
      <div>
        <h2 class="mapp-panel__title">ประวัติการชำระเงิน</h2>
        <p class="mapp-panel__desc">สลิปล่าสุด — <a href="renew.php">ดูทั้งหมด / ต่ออายุ</a></p>
      </div>
    </div>
    <div class="mapp-table-wrap">
      <table class="mapp-table">
        <thead><tr><th class="at-col at-col--short">วันที่</th><th class="at-col at-col--short">จำนวน</th><th class="at-col at-col--short">สถานะ</th><th class="at-col at-col--short">สลิป</th></tr></thead>
        <tbody>
          <?php if (!$myPayments): ?>
            <tr><td colspan="4" class="mapp-table__empty">ยังไม่มีข้อมูล</td></tr>
          <?php endif; ?>
          <?php foreach ($myPayments as $p): ?>
            <tr>
              <td class="at-col at-col--short"><?= e(substr($p['created_at'], 0, 10)) ?></td>
              <td class="at-col at-col--short"><?= number_format((float) $p['amount'], 0) ?> บาท</td>
              <td class="at-col at-col--short"><?= status_badge($p['status']) ?></td>
              <td class="at-col at-col--short"><a class="mapp-table__link" href="../<?= e($p['slip_path']) ?>" target="_blank" rel="noopener">ดูสลิป</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="mapp-panel">
    <div class="mapp-panel__head mapp-panel__head--row">
      <div>
        <h2 class="mapp-panel__title">รีวิว / โพสต์ล่าสุด</h2>
        <p class="mapp-panel__desc"><a href="posts.php">จัดการทั้งหมด →</a></p>
      </div>
    </div>
    <div class="mapp-table-wrap">
      <table class="mapp-table">
        <thead><tr><th class="at-col at-col--main">หัวข้อ</th><th class="at-col at-col--short">สถานะ</th><th class="at-col at-col--actions"></th></tr></thead>
        <tbody>
          <?php if (!$myPosts): ?>
            <tr><td colspan="3" class="mapp-table__empty">ยังไม่มีรายการ</td></tr>
          <?php endif; ?>
          <?php foreach (array_slice($myPosts, 0, 5) as $p): ?>
            <tr>
              <td class="at-col at-col--main"><span class="mapp-table__primary"><?= e($p['title']) ?></span></td>
              <td class="at-col at-col--short"><?= status_badge($p['status']) ?></td>
              <td class="at-col at-col--actions"><a class="mapp-table__link" href="post-view.php?id=<?= (int) $p['id'] ?>">ดู</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php member_footer(); ?>
