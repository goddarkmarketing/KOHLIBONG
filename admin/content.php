<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
require_once __DIR__ . '/../includes/site_content_helpers.php';

$admin = require_admin();
site_content_ensure_table();

$sections = site_content_sections();
$section = $_GET['section'] ?? 'activity';
if (!isset($sections[$section])) {
    $section = 'activity';
}

$listSection = $section;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    try {
        site_content_delete((int) ($_POST['id'] ?? 0));
        flash('ok', 'ลบรายการแล้ว');
    } catch (Throwable $ex) {
        flash('error', $ex->getMessage());
    }
    redirect('content.php?section=' . urlencode($listSection));
}

$rows = site_content_list($listSection);
$activeKey = site_content_admin_active_key($listSection);
$panelTitle = $sections[$listSection]['label'];
$emptyColspan = in_array($listSection, ['hotel', 'hotel_mini'], true) ? 6 : 5;

admin_header($panelTitle, $activeKey, $sections[$listSection]['desc']);
?>

<section class="admin-panel admin-panel--content">
  <div class="admin-panel__head admin-panel__head--row">
    <div>
      <h2 class="admin-panel__title"><?= e($panelTitle) ?></h2>
      <p class="admin-panel__desc">
        <?php if ($listSection === 'hotel_mini'): ?>
          จัดการบทความในแถบสไลด์ใต้การ์ดที่พัก — จำนวนการ์ดบนเว็บตรงกับรายการที่ «แสดง»
        <?php elseif ($listSection === 'hotel'): ?>
          จัดการการ์ดที่พักและอัลบั้มรูป — จำนวนการ์ดบนเว็บตรงกับรายการที่ «แสดง»
        <?php else: ?>
          จัดการรายการที่แสดงบนหน้าเว็บหลัก — จำนวนการ์ดบนเว็บจะตรงกับรายการที่ «แสดง» ในระบบ
        <?php endif; ?>
      </p>
    </div>
    <a href="content-edit.php?section=<?= e($listSection) ?>" class="btn btn--primary">
      <i data-lucide="plus" class="icon"></i> เพิ่มรายการ
    </a>
  </div>

  <div class="admin-table-wrap admin-table-wrap--content">
    <table class="admin-table admin-table--content">
      <thead>
        <tr>
          <th class="cms-col cms-col--thumb" scope="col">รูป</th>
          <th class="cms-col cms-col--title" scope="col">หัวข้อ</th>
          <?php if ($listSection === 'hotel'): ?>
            <th class="cms-col cms-col--gallery" scope="col">อัลบั้ม</th>
          <?php endif; ?>
          <?php if ($listSection === 'hotel_mini'): ?>
            <th class="cms-col cms-col--meta" scope="col">เมตา</th>
          <?php endif; ?>
          <th class="cms-col cms-col--sort" scope="col">ลำดับ</th>
          <th class="cms-col cms-col--status" scope="col">สถานะ</th>
          <th class="cms-col cms-col--actions" scope="col">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr>
            <td colspan="<?= $emptyColspan ?>" class="admin-table__empty">ยังไม่มีรายการ — กด «เพิ่มรายการ» เพื่อเริ่มต้น</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($rows as $r):
            $img = site_content_public_image($r['image_path']);
            $galleryCount = count(site_content_gallery_paths($r));
        ?>
          <tr class="cms-row">
            <td class="cms-col cms-col--thumb">
              <div class="cms-thumb-cell">
                <?php if ($img): ?>
                  <img src="<?= e($img) ?>" alt="" class="cms-list-thumb" loading="lazy" />
                <?php else: ?>
                  <span class="cms-list-thumb cms-list-thumb--empty" aria-hidden="true">—</span>
                <?php endif; ?>
              </div>
            </td>
            <td class="cms-col cms-col--title">
              <div class="cms-title-cell">
                <span class="admin-table__primary"><?= e($r['title']) ?></span>
                <?php if ($r['subtitle'] && $listSection !== 'hotel_mini'): ?>
                  <span class="admin-table__sub"><?= e(mb_strimwidth($r['subtitle'], 0, 80, '…')) ?></span>
                <?php endif; ?>
              </div>
            </td>
            <?php if ($listSection === 'hotel'): ?>
              <td class="cms-col cms-col--gallery">
              <div class="admin-table__cell admin-table__cell--center">
                <span class="badge badge--muted cms-badge-num"><?= $galleryCount ?> รูป</span>
              </div>
            </td>
            <?php endif; ?>
            <?php if ($listSection === 'hotel_mini'): ?>
              <td class="cms-col cms-col--meta">
                <span class="admin-table__meta"><?= e($r['subtitle'] ?: '—') ?> · <?= e($r['review_count'] ?: '0') ?> อ่าน</span>
              </td>
            <?php endif; ?>
            <td class="cms-col cms-col--sort">
              <div class="admin-table__cell admin-table__cell--center">
                <span class="cms-num"><?= (int) $r['sort_order'] ?></span>
              </div>
            </td>
            <td class="cms-col cms-col--status">
              <div class="admin-table__cell admin-table__cell--center">
              <?= (int) $r['is_active'] ? '<span class="badge badge--ok">แสดง</span>' : '<span class="badge badge--muted">ซ่อน</span>' ?>
              </div>
            </td>
            <td class="cms-col cms-col--actions">
              <div class="admin-table__cell admin-table__cell--actions">
              <div class="admin-row-actions admin-row-actions--inline cms-row-actions">
                <a href="content-edit.php?id=<?= (int) $r['id'] ?>" class="btn btn--sm btn--blue">แก้ไข</a>
                <form method="post" class="cms-row-actions__form" onsubmit="return confirm('ลบรายการนี้?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
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
<?php admin_footer(); ?>
