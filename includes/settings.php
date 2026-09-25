<?php
declare(strict_types=1);

/**
 * Site settings (JSON) — editable from admin/settings.php
 * Loaded after BASE_PATH is defined in config.php
 */

function site_settings_path(): string
{
    return BASE_PATH . '/data/site-settings.json';
}

function site_install_lock_path(): string
{
    return BASE_PATH . '/data/install.lock';
}

function site_settings_defaults(): array
{
    return [
        'membership_fee' => 299,
        'subscription_days' => 30,
        'bank_info' => 'ธนาคารกสิกรไทย · ชื่อบัญชี เกาะลิบง.com · เลขที่ 123-4-56789-0',
        'contact_line' => '@talaytrang',
        'contact_email' => 'admin@kohlibong.com',
        'office_address' => 'ทะเลตรัง ออกฟ คอม 54/43 หมู่ 4 ตำบลไม้ฝาด อำเภอกันตัง จังหวัดตรัง 92150',
        'office_hours' => 'เปิดบริการทุกวัน 07:00 – 21:00 น.',
        'pier_name' => 'ท่าเรือหาดยาว',
        'map_lat' => '7.309793',
        'map_lng' => '99.40204',
        'tour_contact_1' => 'คุณกิว 095-4404646',
        'tour_contact_2' => 'คุณทราย 095-4404747',
        'boat_contact_1' => 'คุณอเล็กซ์ 091-0401234',
        'hero_caption' => 'เกาะลิบง / วิถีชุมชนที่สวยงาม',
        'footer_tagline' => 'เกาะลิบง วิถีชุมชนที่มีความสุข',
        'updated_at' => null,
    ];
}

function site_settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $defaults = site_settings_defaults();
    $path = site_settings_path();
    if (!is_file($path)) {
        $cache = $defaults;
        return $cache;
    }

    $raw = json_decode((string) file_get_contents($path), true);
    if (!is_array($raw)) {
        $cache = $defaults;
        return $cache;
    }

    $cache = array_merge($defaults, $raw);
    return $cache;
}

function site_setting(string $key, mixed $default = null): mixed
{
    $all = site_settings();
    return array_key_exists($key, $all) ? $all[$key] : $default;
}

function site_settings_public(): array
{
    $s = site_settings();
    return [
        'contact_line' => (string) $s['contact_line'],
        'contact_email' => (string) $s['contact_email'],
        'office_address' => (string) $s['office_address'],
        'office_hours' => (string) $s['office_hours'],
        'pier_name' => (string) $s['pier_name'],
        'map_lat' => (string) $s['map_lat'],
        'map_lng' => (string) $s['map_lng'],
        'tour_contact_1' => (string) $s['tour_contact_1'],
        'tour_contact_2' => (string) $s['tour_contact_2'],
        'boat_contact_1' => (string) $s['boat_contact_1'],
        'hero_caption' => (string) $s['hero_caption'],
        'footer_tagline' => (string) $s['footer_tagline'],
        'line_url' => site_settings_line_url((string) $s['contact_line']),
    ];
}

function site_settings_line_url(string $lineId): string
{
    $id = ltrim(trim($lineId), '@');
    return $id === '' ? '#' : 'https://line.me/R/ti/p/@' . rawurlencode($id);
}

function site_settings_save(array $input): array
{
    $defaults = site_settings_defaults();
    $current = site_settings();
    $next = $current;

    $intKeys = ['membership_fee', 'subscription_days'];
    $textKeys = [
        'bank_info', 'contact_line', 'contact_email', 'office_address', 'office_hours',
        'pier_name', 'map_lat', 'map_lng', 'tour_contact_1', 'tour_contact_2',
        'boat_contact_1', 'hero_caption', 'footer_tagline',
    ];

    foreach ($intKeys as $key) {
        if (!array_key_exists($key, $input)) {
            continue;
        }
        $val = (int) $input[$key];
        if ($key === 'membership_fee' && ($val < 1 || $val > 100000)) {
            throw new RuntimeException('ค่าสมาชิกไม่ถูกต้อง');
        }
        if ($key === 'subscription_days' && ($val < 1 || $val > 365)) {
            throw new RuntimeException('จำนวนวันสมาชิกไม่ถูกต้อง');
        }
        $next[$key] = $val;
    }

    foreach ($textKeys as $key) {
        if (!array_key_exists($key, $input)) {
            continue;
        }
        $next[$key] = trim((string) $input[$key]);
    }

    if ($next['contact_email'] !== '' && !filter_var($next['contact_email'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('รูปแบบอีเมลไม่ถูกต้อง');
    }

    $next['updated_at'] = date('c');

    $dir = dirname(site_settings_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode($next, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false || file_put_contents(site_settings_path(), $json . "\n") === false) {
        throw new RuntimeException('บันทึกการตั้งค่าไม่สำเร็จ');
    }

    // clear static cache by writing file; next request reloads
    return $next;
}

function site_install_locked(): bool
{
    return is_file(site_install_lock_path());
}

function site_write_install_lock(): void
{
    $dir = dirname(site_install_lock_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        site_install_lock_path(),
        "installed_at=" . date('c') . "\n" . "host=" . ($_SERVER['HTTP_HOST'] ?? 'cli') . "\n"
    );
}

function app_setup_allowed(): bool
{
    if (defined('ALLOW_SETUP')) {
        return (bool) ALLOW_SETUP;
    }
    return !site_install_locked();
}

function app_show_demo_accounts(): bool
{
    if (defined('SHOW_DEMO_ACCOUNTS')) {
        return (bool) SHOW_DEMO_ACCOUNTS;
    }
    // Local / unlocked installs show demo; production lock hides them
    return !site_install_locked();
}

function require_setup_allowed(string $toolName = 'ติดตั้ง'): void
{
    if (app_setup_allowed()) {
        return;
    }
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "ปิดการใช้งาน {$toolName} แล้ว (พบ data/install.lock)\n";
    echo "ถ้าต้องการเปิดใหม่ ให้ตั้ง ALLOW_SETUP=true ใน includes/config.local.php หรือลบไฟล์ install.lock\n";
    exit;
}
