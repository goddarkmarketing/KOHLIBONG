<?php
declare(strict_types=1);

/**
 * Auth + API data validation (CLI)
 */
$root = dirname(__DIR__);
require_once $root . '/includes/db.php';
require_once $root . '/includes/auth.php';
require_once $root . '/includes/helpers.php';
require_once $root . '/includes/site_content_helpers.php';

$ok = 0;
$fail = 0;

function check(bool $cond, string $label): void
{
    global $ok, $fail;
    if ($cond) {
        echo "[PASS] $label\n";
        $ok++;
    } else {
        echo "[FAIL] $label\n";
        $fail++;
    }
}

// API data shape
site_content_ensure_table();
$sections = ['activity', 'tour', 'boat', 'hotel', 'hotel_mini', 'restaurant'];
foreach ($sections as $s) {
    $rows = site_content_list($s, true);
    check(count($rows) > 0, "cms section $s has items");
    if ($rows) {
        $row = $rows[0];
        check(isset($row['title']) && $row['title'] !== '', "cms $s title field");
    }
}

$mapped = array_map('map_site_content_api_row', site_content_list('activity', true));
check(isset($mapped[0]['id'], $mapped[0]['title']), 'map_site_content_api_row structure');

$reviews = get_approved_reviews();
check(count($reviews) >= 1, 'approved reviews >= 1');
if ($reviews) {
    $api = map_review_api_row($reviews[0]);
    check(isset($api['text'], $api['rating']), 'map_review_api_row structure');
}

// Auth: password verify works for known test account
$stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->execute(['admin@kohlibong.com']);
$admin = $stmt->fetch();
if ($admin) {
    check(password_verify('admin123', $admin['password_hash']), 'admin test password (if unchanged)');
    check($admin['role'] === 'admin', 'admin role');
} else {
    echo "[WARN] admin@kohlibong.com not found\n";
}

$stmt->execute(['member@kohlibong.com']);
$member = $stmt->fetch();
if ($member) {
    check($member['role'] === 'member', 'member role');
} else {
    echo "[WARN] member@kohlibong.com not found\n";
}

// CSRF token generation
$_SESSION = [];
$tok = csrf_token();
check(is_string($tok) && strlen($tok) >= 16, 'csrf_token generates');

// XSS escape
check(e('<script>alert(1)</script>') === htmlspecialchars('<script>alert(1)</script>', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 'e() escapes HTML');

echo "\nPASSED: $ok  FAILED: $fail\n";
exit($fail > 0 ? 1 : 0);
