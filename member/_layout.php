<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/member_helpers.php';

function member_nav_link(string $href, string $label, string $icon, string $active, string $key): string
{
    $cls = 'mapp-nav__link' . ($active === $key ? ' is-active' : '');
    return '<a href="' . e($href) . '" class="' . $cls . '">'
        . '<i data-lucide="' . e($icon) . '" class="mapp-nav__icon"></i>'
        . '<span>' . e($label) . '</span></a>';
}

function member_header(string $title, string $active = '', ?string $subtitle = null): void
{
    $user = current_user();
    $isApp = (bool) $user;
    ?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($title) ?> — <?= e(SITE_NAME) ?></title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/member.css" />
  <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.min.js" defer></script>
</head>
<body class="member-page<?= $isApp ? ' member-app' : '' ?>">
<?php if ($isApp): ?>
  <aside class="mapp-sidebar" id="mappSidebar">
    <div class="mapp-sidebar__head">
      <a href="../index.html" class="mapp-brand">
        <span class="mapp-brand__koh">KOH</span><span class="mapp-brand__libong">LIBONG</span>
      </a>
      <span class="mapp-brand__badge">สมาชิก</span>
    </div>
    <nav class="mapp-nav" aria-label="เมนูสมาชิก">
      <p class="mapp-nav__group">บัญชีของฉัน</p>
      <?= member_nav_link('dashboard.php', 'แดชบอร์ด', 'layout-dashboard', $active, 'dashboard') ?>
      <?php if ($user['member_type'] === 'reviewer'): ?>
        <?= member_nav_link('review-new.php', 'เขียนรีวิว', 'star', $active, 'review') ?>
      <?php else: ?>
        <?= member_nav_link('listing-new.php', 'โพสต์ข้อมูล', 'plus-square', $active, 'listing') ?>
      <?php endif; ?>
      <?= member_nav_link('renew.php', 'ต่ออายุสมาชิก', 'credit-card', $active, 'renew') ?>
      <?= member_nav_link('posts.php', 'โพสต์ของฉัน', 'folder-open', $active, 'posts') ?>
      <?= member_nav_link('profile.php', 'โปรไฟล์', 'settings', $active, 'profile') ?>

      <p class="mapp-nav__group">อื่น ๆ</p>
      <?= member_nav_link('help.php', 'ช่วยเหลือ', 'help-circle', $active, 'help') ?>
      <a href="../index.html" class="mapp-nav__link">
        <i data-lucide="globe" class="mapp-nav__icon"></i><span>หน้าเว็บหลัก</span>
      </a>
    </nav>
    <div class="mapp-sidebar__foot">
      <div class="mapp-user">
        <span class="mapp-user__avatar"><i data-lucide="user" class="mapp-nav__icon"></i></span>
        <div class="mapp-user__meta">
          <strong><?= e($user['full_name']) ?></strong>
          <small><?= e(member_type_label($user['member_type'])) ?></small>
        </div>
      </div>
      <a href="logout.php" class="mapp-nav__link mapp-nav__link--logout">
        <i data-lucide="log-out" class="mapp-nav__icon"></i><span>ออกจากระบบ</span>
      </a>
    </div>
  </aside>
  <div class="mapp-shell">
    <header class="mapp-topbar">
      <button type="button" class="mapp-topbar__toggle" id="mappMenuToggle" aria-label="เปิดเมนู">
        <i data-lucide="menu"></i>
      </button>
      <div class="mapp-topbar__titles">
        <h1 class="mapp-topbar__title"><?= e($title) ?></h1>
        <?php if ($subtitle): ?>
          <p class="mapp-topbar__sub"><?= e($subtitle) ?></p>
        <?php endif; ?>
      </div>
      <div class="mapp-topbar__aside">
        <?= render_topbar_subscription($user) ?>
      </div>
    </header>
    <main class="mapp-main">
      <div class="mapp-main__inner">
        <?php member_flash_messages(); ?>
<?php else: ?>
  <header class="member-top">
    <div class="container member-top__inner">
      <a href="../index.html" class="brand brand--mini"><span class="brand__koh">KOH</span><span class="brand__libong">LIBONG</span></a>
      <nav class="member-nav">
        <a href="login.php">เข้าสู่ระบบ</a>
        <a href="register.php">สมัครสมาชิก</a>
      </nav>
    </div>
  </header>
  <main class="member-main member-main--guest">
    <?php member_flash_messages(); ?>
<?php endif;
}

function member_flash_messages(): void
{
    foreach (['error', 'ok', 'info'] as $k) {
        if ($msg = flash($k)) {
            $cls = $k === 'error' ? 'error' : ($k === 'ok' ? 'ok' : 'info');
            echo '<div class="mapp-alert mapp-alert--' . e($cls) . '">' . e($msg) . '</div>';
        }
    }
}

function member_footer(): void
{
    $user = current_user();
    if ($user): ?>
      </div>
    </main>
  </div>
  <div class="mapp-overlay" id="mappOverlay" hidden></div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.lucide?.createIcons?.();
      const sidebar = document.getElementById('mappSidebar');
      const toggle = document.getElementById('mappMenuToggle');
      const overlay = document.getElementById('mappOverlay');
      const close = () => { sidebar?.classList.remove('is-open'); overlay?.setAttribute('hidden', ''); };
      const open = () => { sidebar?.classList.add('is-open'); overlay?.removeAttribute('hidden'); };
      toggle?.addEventListener('click', () => sidebar?.classList.contains('is-open') ? close() : open());
      overlay?.addEventListener('click', close);

      document.querySelectorAll('.star-picker input').forEach((input) => {
        input.addEventListener('change', () => {
          const val = document.querySelector('.star-picker input:checked')?.value;
          const label = document.getElementById('starPickerLabel');
          if (label && val) label.textContent = val + ' ดาว';
        });
      });

      const coverInput = document.getElementById('coverInput');
      const coverLabel = document.getElementById('coverFileName');
      coverInput?.addEventListener('change', () => {
        if (coverLabel) coverLabel.textContent = coverInput.files?.[0]?.name || 'ยังไม่ได้เลือกไฟล์';
      });
    });
  </script>
<?php else: ?>
  </main>
  <script>document.addEventListener('DOMContentLoaded',()=>window.lucide?.createIcons?.());</script>
<?php endif; ?>
</body>
</html>
<?php
}

function post_type_label(string $type): string
{
    return match ($type) {
        'review' => 'รีวิว',
        'hotel' => 'ที่พัก',
        'restaurant' => 'ร้านอาหาร',
        'tour' => 'ทัวร์',
        default => $type,
    };
}

function member_status_label(string $status): string
{
    return match ($status) {
        'pending_approval' => 'รออนุมัติ',
        'active' => 'ใช้งานได้',
        'expired' => 'หมดอายุ',
        'rejected' => 'ถูกปฏิเสธ',
        default => $status,
    };
}
