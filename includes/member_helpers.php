<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function days_until_subscription_end(?string $endDate): ?int
{
    if (!$endDate) {
        return null;
    }
    return (int) floor((strtotime($endDate) - strtotime(date('Y-m-d'))) / 86400);
}

function member_get_post(int $postId, int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM posts WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$postId, $userId]);
    return $stmt->fetch() ?: null;
}

function member_get_business_profile(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM business_profiles WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function post_public_url(array $post): string
{
    if ($post['status'] !== 'approved') {
        return '';
    }
    if ($post['post_type'] === 'review') {
        return SITE_BASE . '/index.html#reviews';
    }
    return match ($post['post_type']) {
        'hotel' => SITE_BASE . '/index.html#hotels',
        'restaurant' => SITE_BASE . '/index.html#restaurants',
        default => SITE_BASE . '/index.html#tours',
    };
}

function render_topbar_subscription(array $user): string
{
    if (($user['role'] ?? '') === 'admin') {
        return '';
    }

    $daysLine = static function (string $icon, string $text): string {
        return '<span class="mapp-sub-badge__days">'
            . '<i data-lucide="' . e($icon) . '" class="mapp-sub-badge__icon" aria-hidden="true"></i>'
            . '<span>' . e($text) . '</span>'
            . '</span>';
    };

    $days = days_until_subscription_end($user['subscription_end'] ?? null);
    $packLabel = 'แพ็กเกจ ' . SUBSCRIPTION_DAYS . ' วัน';
    $feeLabel = number_format(MEMBERSHIP_FEE) . ' บาท';

    if ($user['status'] === 'pending_approval') {
        return '<div class="mapp-sub-badge mapp-sub-badge--info">'
            . $daysLine('clock', 'รออนุมัติบัญชี')
            . '<span class="mapp-sub-badge__pack">' . e($packLabel) . ' · ' . e($feeLabel) . '</span>'
            . '</div>';
    }

    if ($days === null) {
        return '<a href="renew.php" class="mapp-sub-badge mapp-sub-badge--muted">'
            . $daysLine('calendar-off', 'ยังไม่เปิดใช้งาน')
            . '<span class="mapp-sub-badge__pack">' . e($packLabel) . '</span>'
            . '</a>';
    }

    if ($days < 0) {
        return '<a href="renew.php" class="mapp-sub-badge mapp-sub-badge--bad">'
            . $daysLine('alert-circle', 'หมดอายุแล้ว')
            . '<span class="mapp-sub-badge__pack">' . e($packLabel) . ' · ต่ออายุ</span>'
            . '</a>';
    }

    $tone = $days <= EXPIRY_WARN_DAYS ? 'warn' : 'ok';
    $icon = $days <= EXPIRY_WARN_DAYS ? 'alarm-clock' : 'calendar-days';
    $daysLabel = 'เหลือ ' . $days . ' วัน';

    return '<a href="renew.php" class="mapp-sub-badge mapp-sub-badge--' . $tone . '" title="ต่ออายุสมาชิก">'
        . $daysLine($icon, $daysLabel)
        . '<span class="mapp-sub-badge__pack">' . e($packLabel) . ' · ' . e($feeLabel) . '</span>'
        . '</a>';
}

/** @return list<array{type:string,title:string,message:string,link?:string}> */
function member_build_notifications(array $user, array $posts, array $payments): array
{
    $items = [];

    if ($user['status'] === 'pending_approval') {
        $pendingPay = array_filter($payments, static fn ($p) => $p['status'] === 'pending');
        if ($pendingPay) {
            $items[] = [
                'type' => 'info',
                'title' => 'สลิปรอตรวจ',
                'message' => 'แอดมินกำลังตรวจสอบสลิปชำระเงินของคุณ',
            ];
        }
    }

    if ($user['status'] === 'rejected') {
        $items[] = [
            'type' => 'error',
            'title' => 'บัญชีถูกปฏิเสธ',
            'message' => 'กรุณาติดต่อแอดมิน หรือส่งสลิปใหม่',
            'link' => 'renew.php',
        ];
    }

    $days = days_until_subscription_end($user['subscription_end'] ?? null);
    if ($user['role'] === 'member' && $days !== null) {
        if ($days < 0) {
            $items[] = [
                'type' => 'error',
                'title' => 'สมาชิกหมดอายุ',
                'message' => 'กรุณาชำระเงินและอัปโหลดสลิปเพื่อต่ออายุ',
                'link' => 'renew.php',
            ];
        } elseif ($days <= EXPIRY_WARN_DAYS) {
            $items[] = [
                'type' => 'info',
                'title' => 'ใกล้หมดอายุ',
                'message' => 'เหลืออีก ' . $days . ' วัน — ต่ออายุได้ล่วงหน้า',
                'link' => 'renew.php',
            ];
        }
    }

    foreach ($payments as $p) {
        if ($p['status'] === 'rejected' && !empty($p['admin_note'])) {
            $items[] = [
                'type' => 'error',
                'title' => 'สลิปถูกปฏิเสธ',
                'message' => $p['admin_note'],
                'link' => 'renew.php',
            ];
            break;
        }
    }

    foreach ($posts as $p) {
        if ($p['status'] === 'rejected') {
            $items[] = [
                'type' => 'error',
                'title' => 'โพสต์ถูกปฏิเสธ: ' . $p['title'],
                'message' => $p['admin_note'] ?: 'แก้ไขแล้วส่งใหม่ได้',
                'link' => 'post-edit.php?id=' . (int) $p['id'],
            ];
        }
    }

    $recentApproved = array_filter($posts, static fn ($p) =>
        $p['status'] === 'approved' && $p['reviewed_at'] && strtotime($p['reviewed_at']) > strtotime('-7 days'));
    foreach (array_slice($recentApproved, 0, 2) as $p) {
        $url = post_public_url($p);
        $items[] = [
            'type' => 'ok',
            'title' => 'อนุมัติแล้ว: ' . $p['title'],
            'message' => 'เนื้อหาของคุณเผยแพร่บนเว็บแล้ว',
            'link' => $url ?: 'post-view.php?id=' . (int) $p['id'],
            'external' => (bool) $url,
        ];
    }

    return $items;
}

function render_member_notifications(array $items): string
{
    if (!$items) {
        return '';
    }
    $html = '<div class="mapp-notifications">';
    foreach ($items as $n) {
        $type = e($n['type']);
        $html .= '<div class="mapp-notice mapp-notice--' . $type . '">';
        $html .= '<strong>' . e($n['title']) . '</strong>';
        $html .= '<p>' . e($n['message']) . '</p>';
        if (!empty($n['link'])) {
            $href = !empty($n['external']) ? e($n['link']) : e($n['link']);
            $target = !empty($n['external']) ? ' target="_blank" rel="noopener"' : '';
            $html .= '<a href="' . $href . '" class="mapp-notice__link"' . $target . '>ดูรายละเอียด</a>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

function business_type_label(string $type): string
{
    return match ($type) {
        'hotel' => 'ที่พัก',
        'restaurant' => 'ร้านอาหาร',
        'tour' => 'ทัวร์/บริการ',
        'other' => 'อื่น ๆ',
        default => $type,
    };
}
