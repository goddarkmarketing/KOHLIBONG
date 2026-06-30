<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
require_once __DIR__ . '/../includes/backup_helpers.php';

require_admin();

if (isset($_GET['download'])) {
    backup_send_download((string) $_GET['download']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $result = backup_create_full();
        if ($result['ok']) {
            flash('ok', $result['message'] . ' — ' . ($result['file'] ?? ''));
        } else {
            flash('error', $result['message']);
        }
        redirect('backup.php');
    }

    if ($action === 'delete') {
        $file = backup_resolve_archive((string) ($_POST['file'] ?? ''));
        if ($file && @unlink($file)) {
            flash('ok', 'ลบไฟล์สำรองแล้ว');
        } else {
            flash('error', 'ลบไฟล์ไม่สำเร็จ');
        }
        redirect('backup.php');
    }
}

$archives = backup_list_archives();

admin_header('สำรองข้อมูล', 'backup', 'สร้างและดาวน์โหลดไฟล์ Full Backup ทั้งเว็บไซต์');
?>
<section class="admin-panel">
  <div class="admin-panel__head admin-panel__head--row">
    <div>
      <h2 class="admin-panel__title">Full Backup</h2>
      <p class="admin-panel__desc">
        สำรองไฟล์ทั้งโปรเจกต์ (โค้ด, รูป, ข้อมูล JSON) และฐานข้อมูล MySQL หากเป็นไปได้
        — ชื่อไฟล์มีวัน เดือน ปี และเวลา เช่น <code>kohlibong_full_<?= e(backup_timestamp_label()) ?>.zip</code>
      </p>
    </div>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <input type="hidden" name="action" value="create" />
      <button type="submit" class="btn btn--primary">
        <i data-lucide="archive" class="icon"></i> สร้าง Backup ใหม่
      </button>
    </form>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table admin-table--backup">
      <thead>
        <tr>
          <th class="at-col at-col--main">ชื่อไฟล์</th>
          <th class="at-col at-col--short">ขนาด</th>
          <th class="at-col at-col--short">สร้างเมื่อ</th>
          <th class="at-col at-col--actions">ดาวน์โหลด</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$archives): ?>
          <tr>
            <td colspan="4" class="admin-table__empty">ยังไม่มีไฟล์สำรอง — กด «สร้าง Backup ใหม่»</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($archives as $a): ?>
          <tr>
            <td class="at-col at-col--main">
              <div class="admin-table__cell">
                <span class="admin-table__primary"><?= e($a['name']) ?></span>
                <span class="admin-table__sub">รูปแบบ: วัน-เดือน-ปี_ชั่วโมง-นาที-วินาที</span>
              </div>
            </td>
            <td class="at-col at-col--short"><?= e($a['size_label']) ?></td>
            <td class="at-col at-col--short"><?= e($a['created']) ?></td>
            <td class="at-col at-col--actions">
              <div class="admin-table__cell admin-table__cell--actions">
                <div class="admin-row-actions admin-row-actions--inline">
                <a href="backup.php?download=<?= urlencode($a['file']) ?>" class="btn btn--sm btn--green">
                  <i data-lucide="download" class="icon"></i> โหลด
                </a>
                <form method="post" class="admin-inline-extend" onsubmit="return confirm('ลบไฟล์สำรองนี้?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="file" value="<?= e($a['file']) ?>" />
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

  <div class="admin-hint" style="margin-top:20px">
    <strong>หมายเหตุ:</strong> ไฟล์เก็บในโฟลเดอร์ <code>backups/</code> (ห้ามเข้าถึงตรงจากเว็บ)
    · ดาวน์โหลดได้เฉพาะแอดมินที่ล็อกอินแล้ว
    · แนะนำเก็บไฟล์สำรองไว้นอกเซิร์ฟเวอร์ด้วย
  </div>
</section>
<?php admin_footer(); ?>
