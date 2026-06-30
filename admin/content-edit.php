<?php
declare(strict_types=1);

require_once __DIR__ . '/_admin.php';
require_once __DIR__ . '/../includes/site_content_helpers.php';

$admin = require_admin();
site_content_ensure_table();

$sections = site_content_sections();
$id = (int) ($_GET['id'] ?? 0);
$item = $id > 0 ? site_content_get($id) : null;
$section = $item['section'] ?? ($_GET['section'] ?? 'activity');

if (!isset($sections[$section])) {
    flash('error', 'ประเภทเนื้อหาไม่ถูกต้อง');
    redirect('content.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    try {
        $_POST['id'] = (string) $id;
        $_POST['section'] = $section;
        $newId = site_content_save_from_post($_POST, $_FILES);
        flash('ok', $id > 0 ? 'บันทึกแล้ว' : 'เพิ่มรายการแล้ว');
        redirect('content-edit.php?id=' . $newId);
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

if ($item) {
    $id = (int) $item['id'];
} else {
    $item = [
        'section' => $section,
        'title' => '',
        'subtitle' => '',
        'tag' => $section === 'hotel_mini' ? 'ที่พัก' : '',
        'badge' => '',
        'badge_type' => 'left',
        'location' => '',
        'description' => '',
        'price' => '',
        'price_old' => '',
        'rating' => '',
        'review_count' => '',
        'stars' => '',
        'sort_order' => site_content_next_sort($section),
        'is_active' => 1,
        'image_path' => '',
        'gallery_images' => null,
        'amenities' => null,
    ];
}

$parentSection = $sections[$section]['parent'] ?? null;
$activeKey = site_content_admin_active_key($section);
$backUrl = 'content.php?section=' . urlencode($section);
$backLabel = $sections[$section]['label'];
$previewImg = site_content_public_image($item['image_path'] ?? null) ?: 'https://images.pexels.com/photos/14573822/pexels-photo-14573822.jpeg?auto=compress&cs=tinysrgb&w=640';
$galleryPaths = site_content_gallery_paths($item);
$amenitiesText = site_content_amenities_text($item);

admin_header(($id ? 'แก้ไข' : 'เพิ่ม') . ' — ' . $sections[$section]['label'], $activeKey);
?>
<div class="cms-back">
  <a href="<?= e($backUrl) ?>" class="admin-table__link">← กลับรายการ<?= e($backLabel) ?></a>
</div>

<?php if ($error): ?><div class="admin-alert admin-alert--error"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="cms-editor" id="cmsEditorForm" data-section="<?= e($section) ?>">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />

  <aside class="cms-preview-panel">
    <div class="cms-preview-panel__head">
      <h2>พรีวิว</h2>
      <p>ตัวอย่างการแสดงผลบนหน้าเว็บ</p>
    </div>
    <div class="cms-preview-stage" id="cmsPreviewStage">
      <!-- filled by JS -->
    </div>
  </aside>

  <div class="cms-form-panel">
    <div class="cms-form-panel__head admin-panel__head--row">
      <div>
        <h2 class="admin-panel__title"><?= $id ? 'แก้ไขรายการ' : 'เพิ่มรายการใหม่' ?></h2>
        <p class="admin-panel__desc"><?= e($sections[$section]['desc']) ?></p>
      </div>
      <label class="cms-toggle">
        <input type="checkbox" name="is_active" value="1" <?= (int) ($item['is_active'] ?? 1) ? 'checked' : '' ?> />
        <span>แสดงบนเว็บ</span>
      </label>
    </div>

    <div class="cms-fields">
      <label class="cms-field cms-field--full">
        <span class="cms-field__label">หัวข้อ <em>*</em></span>
        <input type="text" name="title" id="fTitle" required value="<?= e($item['title'] ?? '') ?>" placeholder="ชื่อรายการ" />
      </label>

      <label class="cms-field cms-field--section cms-field--activity cms-field--tour cms-field--restaurant">
        <span class="cms-field__label" id="lblSubtitle">คำอธิบายย่อ / เส้นทาง</span>
        <input type="text" name="subtitle" id="fSubtitle" value="<?= e($item['subtitle'] ?? '') ?>" placeholder="เที่ยวเกาะ · วันที่ · จำนวนอ่าน" />
      </label>

      <label class="cms-field cms-field--section cms-field--hotel_mini">
        <span class="cms-field__label">วันที่ (แสดงในสไลด์)</span>
        <input type="text" name="subtitle" id="fMiniDate" value="<?= e($item['subtitle'] ?? '') ?>" placeholder="03 มิ.ย. 2026" />
      </label>

      <label class="cms-field cms-field--section cms-field--hotel_mini">
        <span class="cms-field__label">จำนวนอ่าน</span>
        <input type="text" name="review_count" id="fMiniViews" value="<?= e($item['review_count'] ?? '') ?>" placeholder="402" />
      </label>

      <label class="cms-field cms-field--section cms-field--hotel_mini">
        <span class="cms-field__label">ป้ายแท็ก</span>
        <input type="text" name="tag" id="fMiniTag" value="<?= e($item['tag'] ?? 'ที่พัก') ?>" placeholder="ที่พัก" />
      </label>

      <label class="cms-field cms-field--section cms-field--activity cms-field--restaurant">
        <span class="cms-field__label">แท็ก / ป้าย</span>
        <input type="text" name="tag" id="fTag" value="<?= e($item['tag'] ?? '') ?>" placeholder="ไฮไลต์, แนะนำ" />
      </label>

      <label class="cms-field cms-field--section cms-field--tour">
        <span class="cms-field__label">ป้ายโปรโมชัน</span>
        <input type="text" name="badge" id="fBadge" value="<?= e($item['badge'] ?? '') ?>" placeholder="ขายดี, ประหยัด 18%" />
      </label>

      <label class="cms-field cms-field--section cms-field--tour">
        <span class="cms-field__label">ตำแหน่งป้าย</span>
        <select name="badge_type" id="fBadgeType">
          <?php foreach (['left' => 'ซ้าย', 'right' => 'ขวา', 'sale' => 'ลดราคา (sale)'] as $val => $lab): ?>
            <option value="<?= $val ?>" <?= ($item['badge_type'] ?? 'left') === $val ? 'selected' : '' ?>><?= e($lab) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="cms-field cms-field--section cms-field--tour">
        <span class="cms-field__label">คะแนน</span>
        <input type="text" name="rating" id="fRating" value="<?= e($item['rating'] ?? '') ?>" placeholder="4.9" />
      </label>

      <label class="cms-field cms-field--section cms-field--tour">
        <span class="cms-field__label">จำนวนรีวิว</span>
        <input type="text" name="review_count" id="fReviewCount" value="<?= e($item['review_count'] ?? '') ?>" placeholder="3.2พัน" />
      </label>

      <label class="cms-field cms-field--section cms-field--hotel">
        <span class="cms-field__label">สถานที่</span>
        <input type="text" name="location" id="fLocation" value="<?= e($item['location'] ?? '') ?>" placeholder="หาดบ้านพร้าว, เกาะลิบง" />
      </label>

      <label class="cms-field cms-field--section cms-field--hotel">
        <span class="cms-field__label">ดาว (1-5)</span>
        <input type="number" name="stars" id="fStars" min="1" max="5" value="<?= e((string) ($item['stars'] ?? '')) ?>" placeholder="4" />
      </label>

      <label class="cms-field cms-field--section cms-field--hotel cms-field--full">
        <span class="cms-field__label">สิ่งอำนวยความสะดวก (บรรทัดละ 1 รายการ)</span>
        <textarea name="amenities" id="fAmenities" rows="3" placeholder="Wi-Fi ฟรี&#10;ติดทะเล"><?= e($amenitiesText) ?></textarea>
      </label>

      <label class="cms-field cms-field--section cms-field--boat cms-field--hotel cms-field--full">
        <span class="cms-field__label">รายละเอียด</span>
        <textarea name="description" id="fDescription" rows="4" placeholder="อธิบายเพิ่มเติม..."><?= e($item['description'] ?? '') ?></textarea>
      </label>

      <label class="cms-field cms-field--section cms-field--tour cms-field--boat cms-field--hotel">
        <span class="cms-field__label" id="lblPrice">ราคา</span>
        <input type="text" name="price" id="fPrice" value="<?= e($item['price'] ?? '') ?>" placeholder="3,500" />
      </label>

      <label class="cms-field cms-field--section cms-field--tour">
        <span class="cms-field__label">ราคาเดิม (ขีดฆ่า)</span>
        <input type="text" name="price_old" id="fPriceOld" value="<?= e($item['price_old'] ?? '') ?>" placeholder="4,200" />
      </label>

      <label class="cms-field">
        <span class="cms-field__label">ลำดับแสดง</span>
        <input type="number" name="sort_order" value="<?= (int) ($item['sort_order'] ?? 0) ?>" min="0" />
      </label>

      <label class="cms-field cms-field--full">
        <span class="cms-field__label">รูปภาพหลัก</span>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" id="fImageFile" />
        <input type="url" name="image_url" id="fImageUrl" class="cms-field__sub" value="<?= e(preg_match('#^https?://#i', (string) ($item['image_path'] ?? '')) ? $item['image_path'] : '') ?>" placeholder="หรือวาง URL รูปภาพ" />
        <?php if (!empty($item['image_path'])): ?>
          <span class="cms-field__hint">รูปปัจจุบัน: <?= e(basename((string) $item['image_path'])) ?></span>
        <?php endif; ?>
      </label>

      <div class="cms-field cms-field--section cms-field--hotel cms-field--full">
        <span class="cms-field__label">อัลบั้มรูปที่พัก</span>
        <div class="cms-gallery" id="cmsGallery">
          <?php foreach ($galleryPaths as $path):
              $pub = site_content_public_image($path) ?: $path;
          ?>
            <div class="cms-gallery__item">
              <img src="<?= e($pub) ?>" alt="" loading="lazy" />
              <input type="hidden" name="gallery_keep[]" value="<?= e($path) ?>" />
              <button type="button" class="cms-gallery__remove" title="ลบรูปนี้" aria-label="ลบรูป">&times;</button>
            </div>
          <?php endforeach; ?>
        </div>
        <input type="file" name="gallery_upload[]" accept="image/jpeg,image/png,image/webp" multiple class="cms-gallery__upload" />
        <span class="cms-field__hint">เลือกได้หลายรูป — รูปแรกจะแสดงเป็นภาพหลักในแกลเลอรี (นอกจากรูปปก)</span>
        <details class="cms-gallery__url">
          <summary>เพิ่มจาก URL (ทางเลือก)</summary>
          <textarea name="gallery_urls" id="fGallery" rows="3" placeholder="https://... (บรรทัดละ 1 รูป)"></textarea>
        </details>
      </div>
    </div>

    <div class="cms-form-actions">
      <a href="<?= e($backUrl) ?>" class="btn btn--ghost-dark">ยกเลิก</a>
      <button type="submit" class="btn btn--primary"><i data-lucide="save" class="icon"></i> บันทึก</button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('cmsEditorForm');
  const stage = document.getElementById('cmsPreviewStage');
  if (!form || !stage) return;

  const section = form.dataset.section;
  const $ = (id) => document.getElementById(id);
  const val = (id) => $(id)?.value?.trim() || '';

  const fields = form.querySelectorAll('.cms-field--section');
  fields.forEach((el) => {
    el.hidden = !el.classList.contains('cms-field--' + section);
  });

  document.getElementById('cmsGallery')?.addEventListener('click', (e) => {
    const btn = e.target.closest('.cms-gallery__remove');
    if (!btn) return;
    btn.closest('.cms-gallery__item')?.remove();
    renderPreview();
  });

  const previewImg = <?= json_encode($previewImg, JSON_UNESCAPED_UNICODE) ?>;
  const galleryPreview = <?= json_encode(array_map(static fn ($p) => site_content_public_image($p) ?: $p, $galleryPaths), JSON_UNESCAPED_UNICODE) ?>;

  function starsHtml(n) {
    n = Math.max(0, Math.min(5, parseInt(n, 10) || 0));
    let h = '<span class="stars">';
    for (let i = 0; i < n; i++) h += '<span class="star-icon star-icon--filled">★</span>';
    for (let i = n; i < 5; i++) h += '<span class="star-icon">★</span>';
    return h + '</span>';
  }

  function getImage() {
    return val('fImageUrl') || previewImg;
  }

  function renderPreview() {
    const title = val('fTitle') || 'หัวข้อรายการ';
    const img = getImage();
    let html = '';

    if (section === 'activity' || section === 'restaurant') {
      const tag = val('fTag') || 'แท็ก';
      const meta = val('fSubtitle') || 'คำอธิบายย่อ';
      html = `<article class="card cms-preview-card">
        <div class="card__media"><img src="${img}" alt=""><span class="card__badge card__badge--left">${tag}</span></div>
        <div class="card__body"><h4 class="card__title">${title}</h4><p class="card__meta">${meta}</p></div>
      </article>`;
    } else if (section === 'tour') {
      const route = val('fSubtitle') || 'เส้นทางทัวร์';
      const badge = val('fBadge') || 'ขายดี';
      const bt = val('fBadgeType') || 'left';
      const rate = val('fRating') || '5.0';
      const count = val('fReviewCount') || '0';
      const now = val('fPrice') || '0';
      const old = val('fPriceOld') || '';
      html = `<article class="card cms-preview-card">
        <div class="card__media"><img src="${img}" alt=""><span class="card__badge card__badge--left">ทะเลตรัง</span><span class="card__badge card__badge--${bt}">${badge}</span></div>
        <div class="card__body"><h4 class="card__title">${title}</h4><p class="card__route">${route}</p>
        <div class="card__rating">${starsHtml(5)} <b>${rate}</b> /5 · ${count} รีวิว</div>
        <div class="card__price">${old ? `<span class="card__price-old">THB ${old}</span>` : ''}<span class="card__price-new">THB ${now}</span></div></div>
      </article>`;
    } else if (section === 'boat') {
      const desc = val('fDescription') || 'รายละเอียดบริการเรือ';
      const price = val('fPrice') || '0';
      html = `<article class="card boat-card cms-preview-card">
        <div class="card__media"><img src="${img}" alt=""></div>
        <div class="card__body"><h4 class="card__title">${title}</h4><p class="card__desc">${desc}</p>
        <div class="card__price"><span class="card__price-new">ราคา ${price} บาท/ต่อลำ</span></div></div>
      </article>`;
    } else if (section === 'hotel') {
      const loc = val('fLocation') || 'เกาะลิบง';
      const stars = val('fStars') || '4';
      const desc = val('fDescription') || '';
      const price = val('fPrice') || '0';
      const am = val('fAmenities').split(/[\n,]/).map(s => s.trim()).filter(Boolean);
      const kept = [...document.querySelectorAll('#cmsGallery input[name="gallery_keep[]"]')].map((el) => el.value);
      const thumbs = kept.length ? kept.slice(0, 4) : galleryPreview.slice(0, 4);
      const thumbHtml = thumbs.map((src) => `<span class="cms-preview-thumb"><img src="${src}" alt=""></span>`).join('');
      html = `<article class="card hotel-card cms-preview-card">
        <div class="card__media cms-preview-card__hero-only"><img src="${img}" alt=""></div>
        ${thumbHtml ? `<div class="cms-preview-gallery">${thumbHtml}</div>` : ''}
        <div class="card__body"><h4 class="card__title">${title}</h4><div class="card__rating">${starsHtml(stars)}</div>
        <p class="card__loc">📍 ${loc}</p>
        <div class="amenities">${am.map(a => `<span>${a}</span>`).join('')}</div>
        ${desc ? `<p class="card__desc">${desc}</p>` : ''}
        <div class="card__price"><span class="card__price-new">฿ ${price}</span></div></div>
      </article>`;
    } else if (section === 'hotel_mini') {
      const date = val('fMiniDate') || 'วันที่';
      const views = val('fMiniViews') || '0';
      const tag = val('fMiniTag') || 'ที่พัก';
      html = `<a href="#" class="mini-slider__card cms-preview-mini">
        <div class="mini-slider__media"><img src="${img}" alt=""><span class="mini-slider__tag">${tag}</span></div>
        <div class="mini-slider__card-body"><h4 class="mini-slider__card-title">${title}</h4>
        <div class="mini-slider__card-meta"><time>${date}</time><span class="mini-slider__dot"></span><span>${views} อ่าน</span></div></div>
      </a>`;
    }

    stage.innerHTML = html;
  }

  form.querySelectorAll('input, textarea, select').forEach((el) => {
    el.addEventListener('input', renderPreview);
    el.addEventListener('change', renderPreview);
  });

  document.querySelector('.cms-gallery__upload')?.addEventListener('change', (e) => {
    const input = e.target;
    if (!input.files?.length) return;
    const img = getImage();
    [...input.files].slice(0, 4).forEach((file) => {
      const url = URL.createObjectURL(file);
      galleryPreview.push(url);
    });
    renderPreview();
  });

  renderPreview();
  window.lucide?.createIcons?.();
});
</script>
<?php admin_footer(); ?>
