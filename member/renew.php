<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = require_member();
$error = null;

$filterDay = trim((string) ($_GET['filter_day'] ?? ''));
$filterMonth = trim((string) ($_GET['filter_month'] ?? ''));
$filterYear = trim((string) ($_GET['filter_year'] ?? ''));

$paymentSql = 'SELECT * FROM payments WHERE user_id = ?';
$paymentParams = [$user['id']];
$dateExpr = 'COALESCE(transfer_date, DATE(created_at))';

if ($filterYear !== '') {
    $paymentSql .= ' AND YEAR(' . $dateExpr . ') = ?';
    $paymentParams[] = (int) $filterYear;
}
if ($filterMonth !== '') {
    $paymentSql .= ' AND MONTH(' . $dateExpr . ') = ?';
    $paymentParams[] = (int) $filterMonth;
}
if ($filterDay !== '') {
    $paymentSql .= ' AND DAY(' . $dateExpr . ') = ?';
    $paymentParams[] = (int) $filterDay;
}

$paymentSql .= ' ORDER BY created_at DESC';
$payments = db()->prepare($paymentSql);
$payments->execute($paymentParams);
$allPayments = $payments->fetchAll();

$daysLeft = days_until_subscription_end($user['subscription_end'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $amount = (float) ($_POST['amount'] ?? MEMBERSHIP_FEE);
        $transferDate = parse_date_picker('transfer', required: false);
        $slipPath = save_upload('slip', SLIP_DIR, 'renew');
        $stmt = db()->prepare('INSERT INTO payments (user_id, slip_path, amount, transfer_date, status) VALUES (?,?,?,?,?)');
        $stmt->execute([$user['id'], $slipPath, $amount, $transferDate ?: null, 'pending']);
        flash('ok', 'ส่งสลิปแล้ว — รอแอดมินอนุมัติ');
        redirect('renew.php');
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

member_header('ต่ออายุสมาชิก', 'renew', 'ชำระเงินและอัปโหลดสลิปเพื่อใช้งานต่อ');
?>
<?php if ($error): ?><div class="mapp-alert mapp-alert--error"><?= e($error) ?></div><?php endif; ?>

<?php if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= EXPIRY_WARN_DAYS): ?>
  <div class="mapp-banner mapp-banner--info">
    <i data-lucide="alarm-clock" class="mapp-banner__icon"></i>
    <div>
      <strong>ใกล้หมดอายุ — เหลือ <?= $daysLeft ?> วัน</strong>
      <p>ต่ออายุล่วงหน้าได้เลย แอดมินอนุมัติแล้วจะได้ใช้งานต่อ <?= SUBSCRIPTION_DAYS ?> วัน</p>
    </div>
  </div>
<?php elseif ($daysLeft !== null && $daysLeft < 0): ?>
  <div class="mapp-banner mapp-banner--error">
    <i data-lucide="alert-circle" class="mapp-banner__icon"></i>
    <div>
      <strong>สมาชิกหมดอายุแล้ว</strong>
      <p>อัปโหลดสลิปด้านล่างเพื่อขอเปิดใช้งานใหม่</p>
    </div>
  </div>
<?php endif; ?>

<div class="mapp-grid mapp-grid--renew">
  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">ข้อมูลการชำระเงิน</h2>
    </div>
    <div class="renew-summary">
      <div class="renew-summary__row">
        <span>ค่าสมาชิก</span>
        <strong><?= number_format(MEMBERSHIP_FEE) ?> บาท / <?= SUBSCRIPTION_DAYS ?> วัน</strong>
      </div>
      <div class="renew-summary__row">
        <span>ใช้ได้ถึง</span>
        <strong><?= $user['subscription_end'] ? e($user['subscription_end']) : '—' ?></strong>
      </div>
      <?php if ($daysLeft !== null): ?>
        <div class="renew-summary__row">
          <span>เหลืออีก</span>
          <strong class="<?= $daysLeft < 0 ? 'text-bad' : ($daysLeft <= EXPIRY_WARN_DAYS ? 'text-warn' : '') ?>">
            <?= $daysLeft < 0 ? 'หมดอายุแล้ว' : $daysLeft . ' วัน' ?>
          </strong>
        </div>
      <?php endif; ?>
    </div>
    <div class="bank-box">
      <strong>โอนเงิน <?= number_format(MEMBERSHIP_FEE) ?> บาท</strong><br>
      <?= e(BANK_INFO) ?><br>
      <span class="field__hint">อัปโหลดสลิปหลังโอน — แอดมินตรวจภายใน 24 ชม.</span>
    </div>
    <form method="post" enctype="multipart/form-data" class="review-fields review-fields--stack">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <label class="field">
        <span class="field__label">จำนวนเงิน (บาท)</span>
        <input type="number" name="amount" step="0.01" value="<?= MEMBERSHIP_FEE ?>" required />
      </label>
      <div class="field">
        <span class="field__label">วันที่โอน</span>
        <?= render_date_picker('transfer', date('Y-m-d')) ?>
      </div>
      <label class="upload-zone">
        <input type="file" name="slip" accept="image/jpeg,image/png,image/webp" required hidden />
        <span class="upload-zone__icon"><i data-lucide="receipt"></i></span>
        <span class="upload-zone__title">อัปโหลดสลิปโอนเงิน</span>
        <span class="upload-zone__file">คลิกเพื่อเลือกไฟล์</span>
      </label>
      <button type="submit" class="btn btn--green btn--block">ส่งสลิปต่ออายุ</button>
    </form>
  </section>

  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">ประวัติสลิปทั้งหมด</h2>
      <p class="mapp-panel__desc">ดูสถานะและหมายเหตุจากแอดมิน</p>
    </div>
    <form method="get" class="date-filter">
      <span class="field__label">กรองตามวันที่</span>
      <?= render_date_picker(
          'filter',
          allowEmpty: true,
          selectedDay: $filterDay !== '' ? (int) $filterDay : null,
          selectedMonth: $filterMonth !== '' ? (int) $filterMonth : null,
          selectedYear: $filterYear !== '' ? (int) $filterYear : null,
      ) ?>
      <div class="date-filter__actions">
        <button type="submit" class="btn btn--green">แสดงผล</button>
        <?php if ($filterDay !== '' || $filterMonth !== '' || $filterYear !== ''): ?>
          <a href="renew.php" class="btn btn--ghost">ล้างตัวกรอง</a>
        <?php endif; ?>
      </div>
    </form>
    <div class="mapp-table-wrap">
      <table class="mapp-table">
        <thead><tr><th class="at-col at-col--short">วันที่</th><th class="at-col at-col--short">จำนวน</th><th class="at-col at-col--short">สถานะ</th><th class="at-col at-col--main">หมายเหตุ</th></tr></thead>
        <tbody>
          <?php if (!$allPayments): ?>
            <tr><td colspan="4" class="mapp-table__empty">
              <?= ($filterDay !== '' || $filterMonth !== '' || $filterYear !== '') ? 'ไม่พบรายการในช่วงที่เลือก' : 'ยังไม่มีประวัติ' ?>
            </td></tr>
          <?php endif; ?>
          <?php foreach ($allPayments as $p): ?>
            <tr>
              <td class="at-col at-col--short"><?= e(format_date_th($p['transfer_date'] ?: substr($p['created_at'], 0, 10))) ?></td>
              <td class="at-col at-col--short"><?= number_format((float) $p['amount'], 0) ?> บ.</td>
              <td class="at-col at-col--short"><?= status_badge($p['status']) ?></td>
              <td class="at-col at-col--main">
                <div class="mapp-table__cell">
                <?php if ($p['admin_note']): ?><span class="mapp-table__sub"><?= e($p['admin_note']) ?></span><?php endif; ?>
                <a class="mapp-table__link" href="../<?= e($p['slip_path']) ?>" target="_blank" rel="noopener">สลิป</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php member_footer(); ?>
