<?php
declare(strict_types=1);

/**
 * HTTP smoke test (requires Apache running)
 * C:\xampp\php\php.exe tests/http-smoke.php
 */

$folder = rawurlencode('เกาะลิบง.com');
$base = "http://127.0.0.1/{$folder}";

$routes = [
    'GET /index.html' => '/index.html',
    'GET /api/site-content.php' => '/api/site-content.php',
    'GET /api/reviews.php' => '/api/reviews.php',
    'GET /api/listings.php' => '/api/listings.php',
    'GET /api/me.php' => '/api/me.php',
    'GET /data/site-content.json' => '/data/site-content.json',
    'GET /data/reviews.json' => '/data/reviews.json',
    'GET /member/login.php' => '/member/login.php',
    'GET /admin/index.php' => '/admin/index.php',
    'GET /member/dashboard.php' => '/member/dashboard.php',
    'GET /css/style.css' => '/css/style.css',
    'GET /js/main.js' => '/js/main.js',
];

$failed = 0;
$passed = 0;
$skipped = 0;

echo "=== HTTP SMOKE: $base ===\n\n";

foreach ($routes as $label => $path) {
    $url = $base . $path;
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\d{3}/', $http_response_header[0], $m)) {
        $code = (int) $m[0];
    }

    if ($code === 0 && $body === false) {
        echo "[SKIP] $label — server unreachable\n";
        $skipped++;
        continue;
    }

    $ok = $code >= 200 && $code < 400;
  // admin/member protected pages may redirect 302 to login
    if (str_contains($path, 'admin/index') || str_contains($path, 'member/dashboard')) {
        $ok = in_array($code, [200, 302], true);
    }

    if ($ok) {
        echo "[PASS] $label — HTTP $code\n";
        $passed++;

        if (str_ends_with($path, '.php') && str_contains($path, 'api/')) {
            $json = json_decode((string) $body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "       [WARN] invalid JSON response\n";
            } elseif (isset($json['ok']) && $json['ok'] === false && $code === 200) {
                echo "       [WARN] ok:false in body\n";
            }
        }
    } else {
        echo "[FAIL] $label — HTTP $code\n";
        $failed++;
    }
}

echo "\nPASSED: $passed  FAILED: $failed  SKIPPED: $skipped\n";
exit($failed > 0 ? 1 : ($skipped === count($routes) ? 2 : 0));
