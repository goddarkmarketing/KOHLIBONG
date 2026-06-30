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
        approve_payment($id, (int) $admin['id'], $action === 'approve', $note);
        flash('ok', $action === 'approve' ? 'อนุมัติสมาชิกแล้ว (+30 วัน)' : 'ปฏิเสธสลิปแล้ว');
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
    }
    redirect('payments.php');
}

$rows = db()->query("
  SELECT p.*, u.email, u.full_name, u.member_type, u.phone, u.status AS user_status
  FROM payments p
  JOIN users u ON u.id = p.user_id
  ORDER BY FIELD(p.status,'pending','approved','rejected'), p.created_at DESC
  LIMIT 100
")->fetchAll();

$pendingCount = count(array_filter($rows, static fn ($r) => $r['status'] === 'pending'));

admin_header('รายการชำระเงิน', 'payments', 'ตรวจสอบสลิปโอนเงินและอนุมัติการใช้งาน');
?>
<section class="admin-panel">
  <div class="admin-panel__head">
    <h2 class="admin-panel__title">รายการชำระเงิน</h2>
    <p class="admin-panel__desc">
      <?= $pendingCount > 0 ? "มี {$pendingCount} รายการรอตรวจ" : 'ไม่มีรายการรอตรวจในขณะนี้' ?>
      — อนุมัติแล้วสมาชิกจะใช้งานได้ <?= SUBSCRIPTION_DAYS ?> วัน
    </p>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table admin-table--payments">
      <thead>
        <tr>
          <th class="at-col at-col--main">สมาชิก</th>
          <th class="at-col at-col--short">ประเภท</th>
          <th class="at-col at-col--short">จำนวน</th>
          <th class="at-col at-col--short">สลิป</th>
          <th class="at-col at-col--short">สถานะ</th>
          <th class="at-col at-col--actions">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="admin-table__empty">ยังไม่มีรายการชำระเงิน</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr id="user-<?= (int) $r['user_id'] ?>">
            <td class="at-col at-col--main">
              <div class="admin-table__cell">
                <span class="admin-table__primary"><?= e($r['full_name']) ?></span>
                <span class="admin-table__sub"><?= e($r['email']) ?><?= $r['phone'] ? ' · ' . e($r['phone']) : '' ?></span>
              </div>
            </td>
            <td class="at-col at-col--short"><?= e(member_type_th($r['member_type'])) ?></td>
            <td class="at-col at-col--short"><?= number_format((float) $r['amount'], 0) ?> บาท</td>
            <td class="at-col at-col--short">
              <a class="admin-table__link" href="../<?= e($r['slip_path']) ?>" target="_blank" rel="noopener">ดูสลิป</a>
            </td>
            <td class="at-col at-col--short"><?= status_badge($r['status']) ?></td>
            <td class="at-col at-col--actions">
              <div class="admin-table__cell admin-table__cell--actions">
              <?php if ($r['status'] === 'pending'): ?>
                <form method="post" class="admin-row-actions">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                  <input type="text" name="note" placeholder="หมายเหตุ (ถ้ามี)" class="input-sm" />
                  <button name="action" value="approve" class="btn btn--sm btn--green">อนุมัติ</button>
                  <button name="action" value="reject" class="btn btn--sm btn--login">ปฏิเสธ</button>
                </form>
              <?php else: ?>
                <span class="admin-table__sub">
                  <?= e($r['period_start'] ?? '') ?><?= ($r['period_start'] && $r['period_end']) ? ' → ' . e($r['period_end']) : '' ?>
                </span>
              <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php admin_footer(); ?>
