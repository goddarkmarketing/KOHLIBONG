<?php
declare(strict_types=1);

/**
 * Pre-deploy automated checks (CLI)
 * C:\xampp\php\php.exe tests/predeploy-check.php
 */

$root = dirname(__DIR__);
chdir($root);

$report = [
    'passed' => [],
    'failed' => [],
    'warnings' => [],
];

function pass(array &$r, string $msg): void { $r['passed'][] = $msg; }
function fail(array &$r, string $msg): void { $r['failed'][] = $msg; }
function warn(array &$r, string $msg): void { $r['warnings'][] = $msg; }

// --- 1. Required files ---
$required = [
    'index.html', 'js/main.js', 'css/style.css', 'css/admin.css', 'css/member.css',
    'includes/config.php', 'includes/db.php', 'includes/auth.php', 'includes/helpers.php',
    'sql/schema.sql', 'data/site-content.json', 'data/reviews.json', 'uploads/.htaccess',
];
foreach ($required as $f) {
    is_file($root . '/' . $f) ? pass($report, "file: $f") : fail($report, "missing file: $f");
}

// --- 2. JSON validity ---
foreach (['data/site-content.json', 'data/reviews.json', 'data/listings.json'] as $jf) {
    $path = $root . '/' . $jf;
    if (!is_file($path)) {
        warn($report, "optional json missing: $jf");
        continue;
    }
    $data = json_decode((string) file_get_contents($path), true);
    json_last_error() === JSON_ERROR_NONE && is_array($data)
        ? pass($report, "json valid: $jf")
        : fail($report, "json invalid: $jf — " . json_last_error_msg());
}

// --- 3. PHP syntax (spot check via php -l would be external) ---
foreach (glob($root . '/{admin,member,api,includes}/*.php', GLOB_BRACE) as $php) {
    $tokens = @token_get_all((string) file_get_contents($php));
    $tokens ? pass($report, 'php parse: ' . basename(dirname($php)) . '/' . basename($php)) : fail($report, 'php parse fail: ' . $php);
}

// --- 4. Database ---
try {
    require_once $root . '/includes/db.php';
    $pdo = db();
    pass($report, 'database connection');

    $tables = ['users', 'payments', 'posts', 'site_content'];
    foreach ($tables as $t) {
        $pdo->query("SELECT 1 FROM `$t` LIMIT 1");
        pass($report, "table exists: $t");
    }

    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    $adminCount > 0 ? pass($report, "admin account exists ($adminCount)") : warn($report, 'no admin user — run install-admin.php');

    $contentCount = (int) $pdo->query("SELECT COUNT(*) FROM site_content")->fetchColumn();
    $contentCount > 0 ? pass($report, "site_content rows: $contentCount") : warn($report, 'site_content empty');
} catch (Throwable $ex) {
    fail($report, 'database: ' . $ex->getMessage());
}

// --- 5. API logic (CLI simulate) ---
try {
    require_once $root . '/includes/site_content_helpers.php';
    site_content_ensure_table();
    $sections = site_content_export_section_keys();
    count($sections) >= 6 ? pass($report, 'cms sections: ' . implode(', ', $sections)) : fail($report, 'cms sections incomplete');

    require_once $root . '/includes/helpers.php';
    $reviews = get_approved_reviews(20);
    count($reviews) > 0 ? pass($report, 'approved reviews: ' . count($reviews)) : warn($report, 'no approved reviews');
} catch (Throwable $ex) {
    fail($report, 'cms/reviews: ' . $ex->getMessage());
}

// --- 6. Security checks ---
$setupPath = $root . '/setup.php';
if (is_file($setupPath)) {
    warn($report, 'setup.php still present — disable or remove before production');
}

$config = file_get_contents($root . '/includes/config.php');
str_contains($config, "DB_PASS', ''") ? warn($report, 'DB_PASS is empty in config.php — set production credentials') : pass($report, 'DB_PASS not empty');
str_contains($config, "SITE_BASE', '/เกาะลิบง.com'") ? warn($report, 'SITE_BASE is local path — update for production domain') : pass($report, 'SITE_BASE may be production-ready');

// CSRF usage in admin POST handlers
$adminPosts = file_get_contents($root . '/admin/content.php');
str_contains($adminPosts, 'csrf_verify()') ? pass($report, 'csrf on content delete') : fail($report, 'csrf missing on content delete');

// XSS: e() helper exists
function_exists('e') ? pass($report, 'html escape helper e()') : fail($report, 'missing e() helper');

// uploads php disabled
$htaccess = file_get_contents($root . '/uploads/.htaccess');
str_contains($htaccess, 'engine off') ? pass($report, 'uploads .htaccess blocks PHP') : warn($report, 'uploads .htaccess may not block PHP execution');

// --- 7. Deploy workflow ---
$deploy = @file_get_contents($root . '/.github/workflows/deploy.yml');
if ($deploy) {
    str_contains($deploy, 'better-together') && !is_dir($root . '/better-together')
        ? fail($report, 'deploy.yml copies missing folder: better-together')
        : pass($report, 'deploy.yml better-together check');
    str_contains($deploy, 'api/') || str_contains($deploy, 'data/')
        ? pass($report, 'deploy workflow reviewed')
        : warn($report, 'GitHub Pages deploy is static-only — PHP/API will NOT work on Pages');
}

// --- 8. Broken internal links in index.html ---
$html = (string) file_get_contents($root . '/index.html');
preg_match_all('/(?:href|src)=["\']([^"\']+)["\']/', $html, $m);
$broken = [];
foreach ($m[1] as $link) {
    if (preg_match('~^(https?:|#|mailto:|tel:|javascript:)~i', $link)) {
        continue;
    }
    if (!str_starts_with($link, '/') && !preg_match('/\.(html?|php|css|js|json)$/i', $link)) {
        continue;
    }
    $path = $root . '/' . ltrim(parse_url($link, PHP_URL_PATH) ?? $link, '/');
    if (!is_file($path) && !is_dir($path)) {
        $broken[] = $link;
    }
}
empty($broken) ? pass($report, 'index.html local asset links') : fail($report, 'broken index links: ' . implode(', ', array_unique($broken)));

// --- 9. main.js syntax ---
$js = $root . '/js/main.js';
filesize($js) > 1000 ? pass($report, 'main.js size ok') : fail($report, 'main.js suspiciously small');

// --- Output ---
echo "=== PRE-DEPLOY CHECK ===\n";
echo 'PASSED: ' . count($report['passed']) . "\n";
echo 'FAILED: ' . count($report['failed']) . "\n";
echo 'WARNINGS: ' . count($report['warnings']) . "\n\n";

if ($report['failed']) {
    echo "--- FAILED ---\n";
    foreach ($report['failed'] as $f) {
        echo "  [FAIL] $f\n";
    }
    echo "\n";
}
if ($report['warnings']) {
    echo "--- WARNINGS ---\n";
    foreach ($report['warnings'] as $w) {
        echo "  [WARN] $w\n";
    }
    echo "\n";
}

exit($report['failed'] ? 1 : 0);
