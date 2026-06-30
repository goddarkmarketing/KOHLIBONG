<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/auth.php';

$user = current_user();

if (!$user) {
    echo json_encode(['ok' => true, 'logged_in' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

$isAdmin = $user['role'] === 'admin';
$dashboardUrl = $isAdmin ? ADMIN_BASE . '/index.php' : MEMBER_BASE . '/dashboard.php';
$logoutUrl = $isAdmin ? ADMIN_BASE . '/logout.php' : MEMBER_BASE . '/logout.php';

$actionUrl = $dashboardUrl;
$actionLabel = $isAdmin ? 'หน้าแอดมิน' : 'แดชบอร์ด';

if (!$isAdmin) {
    if ($user['member_type'] === 'reviewer' && subscription_valid($user)) {
        $actionUrl = MEMBER_BASE . '/review-new.php';
        $actionLabel = 'เขียนรีวิว';
    } elseif ($user['member_type'] === 'business' && subscription_valid($user)) {
        $actionUrl = MEMBER_BASE . '/listing-new.php';
        $actionLabel = 'โพสต์ข้อมูล';
    }
}

echo json_encode([
    'ok' => true,
    'logged_in' => true,
    'role' => $user['role'],
    'member_type' => $user['member_type'],
    'status' => $user['status'],
    'name' => $user['full_name'],
    'dashboard_url' => $dashboardUrl,
    'logout_url' => $logoutUrl,
    'action_url' => $actionUrl,
    'action_label' => $actionLabel,
    'can_post' => $isAdmin || subscription_valid($user),
], JSON_UNESCAPED_UNICODE);
