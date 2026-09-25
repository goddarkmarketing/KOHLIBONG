<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);
    $filter = $_POST['status_filter'] ?? 'all';
    $q = trim((string) ($_POST['q'] ?? ''));
    try {
        if ($action === 'extend') {
            $days = (int) ($_POST['days'] ?? SUBSCRIPTION_DAYS);
            admin_extend_member($userId, $days, (int) $admin['id']);
            flash('ok', 'ขยายสมาชิก ' . $days . ' วันแล้ว');
        } elseif ($action === 'reject') {
            admin_set_member_status($userId, 'rejected');
            flash('ok', 'ระงับ/ปฏิเสธสมาชิกแล้ว');
        } elseif ($action === 'activate') {
            admin_set_member_status($userId, 'active');
            flash('ok', 'เปิดใช้งานสมาชิกแล้ว');
        } elseif ($action === 'expire') {
            admin_set_member_status($userId, 'expired');
            flash('ok', 'ตั้งสถานะหมดอายุแล้ว');
        } elseif ($action === 'reset_password') {
            $pass = (string) ($_POST['new_password'] ?? '');
            admin_reset_member_password($userId, $pass);
            flash('ok', 'รีเซ็ตรหัสผ่านแล้ว');
        } elseif ($action === 'delete') {
            admin_delete_member($userId);
            flash('ok', 'ลบบัญชีสมาชิกแล้ว');
        }
        $qs = 'status=' . urlencode($filter);
        if ($q !== '') {
            $qs .= '&q=' . urlencode($q);
        }
        redirect('members.php?' . $qs);
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
        redirect('members.php');
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending_approval', 'active', 'expired', 'rejected'];
if (!in_array($statusFilter, $allowed, true)) {
    $statusFilter = 'all';
}
$q = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT u.*, bp.business_name, bp.business_type
        FROM users u
        LEFT JOIN business_profiles bp ON bp.user_id = u.id
        WHERE u.role = 'member'";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= ' AND u.status = ?';
    $params[] = $statusFilter;
}
if ($q !== '') {
    $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR bp.business_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}

$sql .= ' ORDER BY FIELD(u.status, \'pending_approval\', \'active\', \'expired\', \'rejected\'), u.created_at DESC LIMIT 200';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = db()->query("
    SELECT status, COUNT(*) AS c FROM users WHERE role = 'member' GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

admin_header('รายชื่อสมาชิก', 'members', 'ค้นหา ระงับ รีเซ็ตรหัสผ่าน และขยายแพ็กเกจ');
?>
<div class="admin-filters">
  <?php
  $filters = [
      'all' => 'ทั้งหมด',
      'pending_approval' => 'รออนุมัติ',
      'active' => 'ใช้งาน',
      'expired' => 'หมดอายุ',
      'rejected' => 'ถูกปฏิเสธ',
  ];
  foreach ($filters as $key => $label):
      $count = $key === 'all'
          ? array_sum(array_map('intval', $counts))
          : (int) ($counts[$key] ?? 0);
      $cls = 'admin-filter' . ($statusFilter === $key ? ' is-active' : '');
      $href = 'members.php?status=' . urlencode($key) . ($q !== '' ? '&q=' . urlencode($q) : '');
  ?>
    <a href="<?= e($href) ?>" class="<?= $cls ?>"><?= e($label) ?> (<?= $count ?>)</a>
  <?php endforeach; ?>
</div>

<section class="admin-panel">
  <div class="admin-panel__head admin-panel__head--row">
    <div>
      <h2 class="admin-panel__title">สมาชิกทั้งหมด</h2>
      <p class="admin-panel__desc">แพ็กเกจมาตรฐาน <?= SUBSCRIPTION_DAYS ?> วัน · <?= number_format(MEMBERSHIP_FEE) ?> บาท</p>
    </div>
    <form method="get" class="admin-search">
      <input type="hidden" name="status" value="<?= e($statusFilter) ?>" />
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="ค้นหาชื่อ อีเมล โทร ธุรกิจ" class="input-sm" />
      <button type="submit" class="btn btn--sm btn--ghost-dark">ค้นหา</button>
    </form>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table admin-table--members">
      <thead>
        <tr>
          <th class="at-col at-col--main">สมาชิก</th>
          <th class="at-col at-col--short">ประเภท</th>
          <th class="at-col at-col--short">สถานะ</th>
          <th class="at-col at-col--short">ใช้ได้ถึง</th>
          <th class="at-col at-col--short">เหลือ</th>
          <th class="at-col at-col--actions">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="admin-table__empty">ไม่พบสมาชิกในตัวกรองนี้</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r):
            $days = days_until_subscription_end($r['subscription_end'] ?? null);
            $daysLabel = $days === null ? '—' : ($days < 0 ? 'หมดอายุ' : $days . ' วัน');
        ?>
          <tr>
            <td class="at-col at-col--main">
              <div class="admin-table__cell">
                <span class="admin-table__primary"><?= e($r['full_name']) ?></span>
                <span class="admin-table__sub"><?= e($r['email']) ?><?= $r['phone'] ? ' · ' . e($r['phone']) : '' ?></span>
                <?php if (!empty($r['business_name'])): ?>
                  <span class="admin-table__sub"><?= e($r['business_name']) ?></span>
                <?php endif; ?>
              </div>
            </td>
            <td class="at-col at-col--short"><?= e(member_type_th($r['member_type'])) ?></td>
            <td class="at-col at-col--short"><?= status_badge($r['status']) ?></td>
            <td class="at-col at-col--short"><?= $r['subscription_end'] ? e($r['subscription_end']) : '—' ?></td>
            <td class="at-col at-col--short"><?= e($daysLabel) ?></td>
            <td class="at-col at-col--actions">
              <div class="admin-table__cell admin-table__cell--actions">
                <div class="admin-row-actions admin-row-actions--wrap">
                  <a href="payments.php#user-<?= (int) $r['id'] ?>" class="btn btn--sm btn--ghost-dark">สลิป</a>
                  <form method="post" class="admin-inline-extend">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="action" value="extend" />
                    <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>" />
                    <input type="hidden" name="status_filter" value="<?= e($statusFilter) ?>" />
                    <input type="hidden" name="q" value="<?= e($q) ?>" />
                    <input type="number" name="days" value="<?= SUBSCRIPTION_DAYS ?>" min="1" max="365" class="input-sm input-sm--days" title="จำนวนวัน" />
                    <button type="submit" class="btn btn--sm btn--green">+วัน</button>
                  </form>
                  <?php if ($r['status'] !== 'rejected'): ?>
                    <form method="post" onsubmit="return confirm('ระงับสมาชิกนี้?');">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                      <input type="hidden" name="action" value="reject" />
                      <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>" />
                      <input type="hidden" name="status_filter" value="<?= e($statusFilter) ?>" />
                      <input type="hidden" name="q" value="<?= e($q) ?>" />
                      <button class="btn btn--sm btn--login">ระงับ</button>
                    </form>
                  <?php else: ?>
                    <form method="post">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                      <input type="hidden" name="action" value="activate" />
                      <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>" />
                      <input type="hidden" name="status_filter" value="<?= e($statusFilter) ?>" />
                      <input type="hidden" name="q" value="<?= e($q) ?>" />
                      <button class="btn btn--sm btn--green">เปิดใช้</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" class="admin-inline-extend" onsubmit="return confirm('รีเซ็ตรหัสผ่านสมาชิกนี้?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="action" value="reset_password" />
                    <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>" />
                    <input type="hidden" name="status_filter" value="<?= e($statusFilter) ?>" />
                    <input type="hidden" name="q" value="<?= e($q) ?>" />
                    <input type="text" name="new_password" value="member123" minlength="6" class="input-sm input-sm--pass" title="รหัสผ่านใหม่" required />
                    <button type="submit" class="btn btn--sm btn--ghost-dark">รีเซ็ตรหัส</button>
                  </form>
                  <form method="post" onsubmit="return confirm('ลบบัญชีและข้อมูลที่เกี่ยวข้องทั้งหมด?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>" />
                    <input type="hidden" name="status_filter" value="<?= e($statusFilter) ?>" />
                    <input type="hidden" name="q" value="<?= e($q) ?>" />
                    <button class="btn btn--sm btn--login">ลบ</button>
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
<?php admin_footer(); ?>
