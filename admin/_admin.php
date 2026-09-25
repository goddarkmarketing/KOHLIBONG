<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/member_helpers.php';
require_once __DIR__ . '/../includes/site_content_helpers.php';

function admin_nav_preview_btn(string $label, string $previewPath): string
{
    $url = site_content_public_image($previewPath) ?? (SITE_BASE . '/' . ltrim($previewPath, '/'));

    return '<button type="button" class="admin-nav__peek" aria-label="ดูตำแหน่งบนหน้าเว็บ: ' . e($label) . '">'
        . '<i data-lucide="scan-eye" class="admin-nav__peek-icon"></i>'
        . '<span class="admin-nav__peek-pop" role="tooltip" hidden>'
        . '<img src="' . e($url) . '" alt="" loading="lazy" decoding="async">'
        . '<span class="admin-nav__peek-cap">' . e($label) . '</span>'
        . '</span></button>';
}

function admin_nav_item(string $href, string $label, string $icon, string $active, string $key, ?string $preview = null): string
{
    $cls = 'admin-nav__link' . ($active === $key ? ' is-active' : '');
    $link = '<a href="' . e($href) . '" class="' . $cls . '">'
        . '<i data-lucide="' . e($icon) . '" class="admin-nav__icon"></i>'
        . '<span class="admin-nav__label">' . e($label) . '</span></a>';

    if (!$preview) {
        return $link;
    }

    return '<div class="admin-nav__item">'
        . $link
        . admin_nav_preview_btn($label, $preview)
        . '</div>';
}

function admin_nav_subitem(string $href, string $label, string $active, string $key, ?string $preview = null): string
{
    $cls = 'admin-nav__sublink' . ($active === $key ? ' is-active' : '');
    $link = '<a href="' . e($href) . '" class="' . $cls . '"><span>' . e($label) . '</span></a>';

    if (!$preview) {
        return $link;
    }

    return '<div class="admin-nav__item admin-nav__item--sub">'
        . $link
        . admin_nav_preview_btn($label, $preview)
        . '</div>';
}

function site_content_admin_active_key(string $section): string
{
    return 'content-' . str_replace('_', '-', $section);
}

function admin_header(string $title, string $active = 'dashboard', ?string $subtitle = null): void
{
    $user = current_user();
    ?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($title) ?> — แอดมิน</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/admin.css" />
  <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.min.js" defer></script>
</head>
<body class="admin-app">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__head">
      <a href="../index.html" class="admin-brand">
        <span class="admin-brand__koh">KOH</span><span class="admin-brand__libong">LIBONG</span>
      </a>
      <span class="admin-brand__badge">Admin</span>
    </div>

    <nav class="admin-nav" aria-label="เมนูแอดมิน">
      <p class="admin-nav__group">จัดการระบบ</p>
      <?= admin_nav_item('index.php', 'ภาพรวม', 'layout-dashboard', $active, 'dashboard') ?>
      <?= admin_nav_item('payments.php', 'รายการชำระเงิน', 'receipt', $active, 'payments') ?>
      <?= admin_nav_item('members.php', 'รายชื่อสมาชิก', 'users', $active, 'members') ?>
      <?= admin_nav_item('posts.php', 'โพสต์ / รีวิว', 'file-check', $active, 'posts') ?>
      <?= admin_nav_item('settings.php', 'ตั้งค่าระบบ', 'settings', $active, 'settings') ?>
      <?= admin_nav_item('backup.php', 'สำรองข้อมูล', 'archive', $active, 'backup') ?>

      <p class="admin-nav__group">เนื้อหาหน้าเว็บ</p>
      <?php foreach (site_content_nav_sections() as $key => $meta): ?>
        <?php if ($key === 'hotel'): ?>
          <?php $miniMeta = site_content_sections()['hotel_mini']; ?>
          <div class="admin-nav__branch<?= in_array($active, ['content-hotel', 'content-hotel-mini'], true) ? ' is-expanded' : '' ?>">
            <?= admin_nav_item('content.php?section=hotel', $meta['label'], $meta['icon'], $active, 'content-hotel', $meta['preview'] ?? null) ?>
            <?= admin_nav_subitem('content.php?section=hotel_mini', 'สไลด์มินิ', $active, 'content-hotel-mini', $miniMeta['preview'] ?? null) ?>
          </div>
        <?php else: ?>
          <?= admin_nav_item('content.php?section=' . $key, $meta['label'], $meta['icon'], $active, site_content_admin_active_key($key), $meta['preview'] ?? null) ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <p class="admin-nav__group">เว็บไซต์</p>
      <a href="../index.html" class="admin-nav__link">
        <i data-lucide="globe" class="admin-nav__icon"></i>
        <span>หน้าเว็บหลัก</span>
      </a>
    </nav>

    <div class="admin-sidebar__foot">
      <div class="admin-user">
        <span class="admin-user__avatar"><i data-lucide="shield" class="admin-nav__icon"></i></span>
        <div class="admin-user__meta">
          <strong><?= e($user['full_name'] ?? 'แอดมิน') ?></strong>
          <small><?= e($user['email'] ?? '') ?></small>
        </div>
      </div>
      <a href="logout.php" class="admin-nav__link admin-nav__link--logout">
        <i data-lucide="log-out" class="admin-nav__icon"></i>
        <span>ออกจากระบบ</span>
      </a>
    </div>
  </aside>

  <div class="admin-shell">
    <header class="admin-topbar">
      <button type="button" class="admin-topbar__toggle" id="adminMenuToggle" aria-label="เปิดเมนู">
        <i data-lucide="menu"></i>
      </button>
      <div class="admin-topbar__titles">
        <h1 class="admin-topbar__title"><?= e($title) ?></h1>
        <?php if ($subtitle): ?>
          <p class="admin-topbar__sub"><?= e($subtitle) ?></p>
        <?php endif; ?>
      </div>
    </header>

    <main class="admin-main">
      <div class="admin-main__inner">
        <?php
        foreach (['error', 'ok', 'info'] as $k) {
            if ($msg = flash($k)) {
                echo '<div class="admin-alert admin-alert--' . e($k) . '">' . e($msg) . '</div>';
            }
        }
        ?>
<?php
}

function admin_footer(): void
{
    ?>
      </div>
    </main>
  </div>
  <div class="admin-overlay" id="adminOverlay" hidden></div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.lucide?.createIcons?.();
      const sidebar = document.getElementById('adminSidebar');
      const toggle = document.getElementById('adminMenuToggle');
      const overlay = document.getElementById('adminOverlay');
      const open = () => { sidebar?.classList.add('is-open'); overlay?.removeAttribute('hidden'); };
      const close = () => { sidebar?.classList.remove('is-open'); overlay?.setAttribute('hidden', ''); };
      toggle?.addEventListener('click', () => sidebar?.classList.contains('is-open') ? close() : open());
      overlay?.addEventListener('click', close);

      document.querySelectorAll('.admin-nav__peek').forEach((btn) => {
        const pop = btn.querySelector('.admin-nav__peek-pop');
        if (!pop) return;

        const place = () => {
          const r = btn.getBoundingClientRect();
          const popW = 400;
          let left = r.right + 12;
          if (left + popW > window.innerWidth - 12) {
            left = Math.max(12, r.left - popW - 12);
          }
          let top = r.top + r.height / 2;
          pop.style.left = left + 'px';
          pop.style.top = top + 'px';
        };

        const show = () => {
          place();
          pop.hidden = false;
          requestAnimationFrame(() => pop.classList.add('is-visible'));
        };
        const hide = () => {
          pop.classList.remove('is-visible');
          window.setTimeout(() => {
            if (!pop.classList.contains('is-visible')) pop.hidden = true;
          }, 160);
        };

        btn.addEventListener('mouseenter', show);
        btn.addEventListener('focus', show);
        btn.addEventListener('mouseleave', hide);
        btn.addEventListener('blur', hide);
        window.addEventListener('resize', () => {
          if (pop.classList.contains('is-visible')) place();
        });
      });
    });
  </script>
</body>
</html>
<?php
}

function approve_payment(int $paymentId, int $adminId, bool $approve, ?string $note = null): void
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ? AND status = ?');
    $stmt->execute([$paymentId, 'pending']);
    $pay = $stmt->fetch();
    if (!$pay) {
        throw new RuntimeException('ไม่พบรายการหรือตรวจแล้ว');
    }

    if (!$approve) {
        $pdo->prepare('UPDATE payments SET status=?, admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?')
            ->execute(['rejected', $note, $adminId, $paymentId]);
        $pdo->prepare("UPDATE users SET status='rejected' WHERE id=? AND status='pending_approval'")
            ->execute([$pay['user_id']]);
        return;
    }

    $start = date('Y-m-d');
    $end = date('Y-m-d', strtotime('+' . SUBSCRIPTION_DAYS . ' days'));

    $user = $pdo->prepare('SELECT subscription_end, status FROM users WHERE id = ?');
    $user->execute([$pay['user_id']]);
    $u = $user->fetch();
    if ($u && $u['subscription_end'] && $u['subscription_end'] >= $start) {
        $start = date('Y-m-d', strtotime($u['subscription_end'] . ' +1 day'));
        $end = date('Y-m-d', strtotime($start . ' +' . SUBSCRIPTION_DAYS . ' days'));
    }

    $pdo->prepare('UPDATE payments SET status=?, admin_note=?, reviewed_by=?, reviewed_at=NOW(), period_start=?, period_end=? WHERE id=?')
        ->execute(['approved', $note, $adminId, $start, $end, $paymentId]);
    $pdo->prepare("UPDATE users SET status='active', subscription_start=?, subscription_end=? WHERE id=?")
        ->execute([$start, $end, $pay['user_id']]);
}

function approve_post(int $postId, int $adminId, bool $approve, ?string $note = null): void
{
    $status = $approve ? 'approved' : 'rejected';
    db()->prepare('UPDATE posts SET status=?, admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=? AND status=?')
        ->execute([$status, $note, $adminId, $postId, 'pending']);

    export_public_content_json();
}

function unhide_post(int $postId, int $adminId, ?string $note = null): void
{
    $note = $note ?: 'เผยแพร่ซ้ำโดยแอดมิน';
    $stmt = db()->prepare("UPDATE posts SET status='approved', admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=? AND status='hidden'");
    $stmt->execute([$note, $adminId, $postId]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('ไม่พบโพสต์ที่ถูกซ่อน');
    }
    export_public_content_json();
}

function delete_post(int $postId): void
{
    $stmt = db()->prepare('SELECT cover_image FROM posts WHERE id = ? LIMIT 1');
    $stmt->execute([$postId]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new RuntimeException('ไม่พบโพสต์');
    }
    db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$postId]);
    if (!empty($row['cover_image'])) {
        $path = BASE_PATH . '/' . ltrim((string) $row['cover_image'], '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }
    export_public_content_json();
}

function admin_update_post(int $postId, array $data): void
{
    $stmt = db()->prepare('SELECT id, post_type FROM posts WHERE id = ? LIMIT 1');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if (!$post) {
        throw new RuntimeException('ไม่พบโพสต์');
    }

    $title = trim((string) ($data['title'] ?? ''));
    $content = trim((string) ($data['content'] ?? ''));
    if ($title === '' || $content === '') {
        throw new RuntimeException('กรอกหัวข้อและเนื้อหา');
    }

    if ($post['post_type'] === 'review') {
        db()->prepare('UPDATE posts SET title=?, content=?, booking_place=?, booking_date=?, guest_name=?, rating=? WHERE id=?')
            ->execute([
                $title,
                $content,
                trim((string) ($data['booking_place'] ?? '')) ?: null,
                trim((string) ($data['booking_date'] ?? '')) ?: null,
                trim((string) ($data['guest_name'] ?? '')) ?: null,
                max(1, min(5, (int) ($data['rating'] ?? 5))),
                $postId,
            ]);
    } else {
        db()->prepare('UPDATE posts SET title=?, content=?, location=?, price=? WHERE id=?')
            ->execute([
                $title,
                $content,
                trim((string) ($data['location'] ?? '')) ?: null,
                trim((string) ($data['price'] ?? '')) ?: null,
                $postId,
            ]);
    }

    export_public_content_json();
}

function hide_post(int $postId, int $adminId, ?string $note = null): void
{
    $note = $note ?: 'ถอนการเผยแพร่โดยแอดมิน';
    $stmt = db()->prepare("UPDATE posts SET status='hidden', admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=? AND status='approved'");
    $stmt->execute([$note, $adminId, $postId]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('ไม่พบโพสต์ที่เผยแพร่อยู่');
    }

    export_public_content_json();
}

function admin_extend_member(int $userId, int $days, int $adminId): void
{
    if ($days < 1 || $days > 365) {
        throw new RuntimeException('จำนวนวันไม่ถูกต้อง');
    }

    $stmt = db()->prepare("SELECT id, subscription_end FROM users WHERE id = ? AND role = 'member' LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        throw new RuntimeException('ไม่พบสมาชิก');
    }

    $today = date('Y-m-d');
    $base = $today;
    if (!empty($user['subscription_end']) && $user['subscription_end'] >= $today) {
        $base = $user['subscription_end'];
    }

    $newEnd = date('Y-m-d', strtotime($base . ' +' . $days . ' days'));
    $start = $today;

    db()->prepare("UPDATE users SET status='active', subscription_start=?, subscription_end=? WHERE id=?")
        ->execute([$start, $newEnd, $userId]);
}

function admin_set_member_status(int $userId, string $status): void
{
    $allowed = ['active', 'expired', 'rejected', 'pending_approval'];
    if (!in_array($status, $allowed, true)) {
        throw new RuntimeException('สถานะไม่ถูกต้อง');
    }
    $stmt = db()->prepare("UPDATE users SET status=? WHERE id=? AND role='member'");
    $stmt->execute([$status, $userId]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('ไม่พบสมาชิก');
    }
}

function admin_reset_member_password(int $userId, string $password): void
{
    if (strlen($password) < 6) {
        throw new RuntimeException('รหัสผ่านอย่างน้อย 6 ตัวอักษร');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare("UPDATE users SET password_hash=? WHERE id=? AND role='member'");
    $stmt->execute([$hash, $userId]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('ไม่พบสมาชิก');
    }
}

function admin_delete_member(int $userId): void
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id=? AND role='member' LIMIT 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('ไม่พบสมาชิก');
    }

    $pdo->beginTransaction();
    try {
        $covers = $pdo->prepare('SELECT cover_image FROM posts WHERE user_id=?');
        $covers->execute([$userId]);
        foreach ($covers->fetchAll() as $row) {
            if (!empty($row['cover_image'])) {
                $path = BASE_PATH . '/' . ltrim((string) $row['cover_image'], '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
        $slips = $pdo->prepare('SELECT slip_path FROM payments WHERE user_id=?');
        $slips->execute([$userId]);
        foreach ($slips->fetchAll() as $row) {
            if (!empty($row['slip_path'])) {
                $path = BASE_PATH . '/' . ltrim((string) $row['slip_path'], '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
        $pdo->prepare('DELETE FROM posts WHERE user_id=?')->execute([$userId]);
        $pdo->prepare('DELETE FROM payments WHERE user_id=?')->execute([$userId]);
        $pdo->prepare('DELETE FROM business_profiles WHERE user_id=?')->execute([$userId]);
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$userId]);
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }

    export_public_content_json();
}

function admin_get_post(int $postId): ?array
{
    $stmt = db()->prepare('
        SELECT p.*, u.full_name, u.email, u.member_type, u.phone
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE p.id = ?
        LIMIT 1
    ');
    $stmt->execute([$postId]);

    return $stmt->fetch() ?: null;
}

function member_type_th(string $type): string
{
    return match ($type) {
        'reviewer' => 'สมาชิกรีวิว',
        'business' => 'ผู้ประกอบการ',
        default => $type,
    };
}

function post_type_th(string $type): string
{
    return match ($type) {
        'review' => 'รีวิว',
        'hotel' => 'ที่พัก',
        'restaurant' => 'ร้านอาหาร',
        'tour' => 'ทัวร์',
        default => $type,
    };
}
