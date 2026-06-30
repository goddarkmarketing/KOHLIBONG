<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }
    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_verify(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
}

function save_upload(string $field, string $destDir, string $prefix): string
{
    if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('กรุณาอัปโหลดไฟล์ให้ถูกต้อง');
    }
    if ($_FILES[$field]['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('ไฟล์ใหญ่เกิน 5 MB');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$field]['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('รองรับเฉพาะ JPG, PNG, WEBP');
    }
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'img',
    };
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $name = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = $destDir . '/' . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $path)) {
        throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ');
    }
    return 'uploads/' . basename($destDir) . '/' . $name;
}

/** @return list<string> */
function save_upload_batch(string $field, string $destDir, string $prefix): array
{
    if (empty($_FILES[$field]['tmp_name'])) {
        return [];
    }

    $paths = [];
    $batch = $_FILES[$field];
    $names = is_array($batch['name']) ? $batch['name'] : [$batch['name']];
    $count = count($names);

    for ($i = 0; $i < $count; $i++) {
        $error = is_array($batch['error']) ? ($batch['error'][$i] ?? UPLOAD_ERR_NO_FILE) : $batch['error'];
        $tmp = is_array($batch['tmp_name']) ? ($batch['tmp_name'][$i] ?? '') : $batch['tmp_name'];
        $size = is_array($batch['size']) ? ($batch['size'][$i] ?? 0) : $batch['size'];
        $name = is_array($batch['name']) ? ($batch['name'][$i] ?? '') : $batch['name'];

        if ($error === UPLOAD_ERR_NO_FILE || !$tmp) {
            continue;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ');
        }
        if ($size > MAX_UPLOAD_BYTES) {
            throw new RuntimeException('ไฟล์ใหญ่เกิน 5 MB');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            throw new RuntimeException('รองรับเฉพาะ JPG, PNG, WEBP');
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'img',
        };
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $safe = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = $destDir . '/' . $safe;
        if (!move_uploaded_file($tmp, $path)) {
            throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ');
        }
        $paths[] = 'uploads/' . basename($destDir) . '/' . $safe;
    }

    return $paths;
}

function save_upload_optional(string $field, string $destDir, string $prefix, ?string $currentPath = null): string
{
    if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($currentPath) {
            return $currentPath;
        }
        throw new RuntimeException('กรุณาอัปโหลดไฟล์');
    }
    return save_upload($field, $destDir, $prefix);
}

function member_type_label(string $type): string
{
    return match ($type) {
        'reviewer' => 'สมาชิกรีวิว (ผู้ใช้บริการ)',
        'business' => 'ผู้ประกอบการ',
        default => $type,
    };
}

function status_badge(string $status): string
{
    return match ($status) {
        'pending_approval' => '<span class="badge badge--warn">รออนุมัติสมาชิก</span>',
        'active' => '<span class="badge badge--ok">ใช้งานได้</span>',
        'expired' => '<span class="badge badge--muted">หมดอายุ</span>',
        'rejected' => '<span class="badge badge--bad">ถูกปฏิเสธ</span>',
        'pending' => '<span class="badge badge--warn">รอตรวจ</span>',
        'approved' => '<span class="badge badge--ok">อนุมัติแล้ว</span>',
        'hidden' => '<span class="badge badge--muted">ซ่อนจากเว็บ</span>',
        default => '<span class="badge">' . e($status) . '</span>',
    };
}

/** @return array<int, string> */
function thai_months(): array
{
    return [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];
}

/** @return array{0: int, 1: int, 2: int} */
function date_picker_parts(?string $ymd): array
{
    if ($ymd && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
        return [(int) $m[3], (int) $m[2], (int) $m[1]];
    }

    return [0, 0, 0];
}

/** @return list<int> */
function year_options(int $fromYearsAgo = 3, int $toYearsAhead = 0): array
{
    $current = (int) date('Y');
    $years = [];
    for ($y = $current - $fromYearsAgo; $y <= $current + $toYearsAhead; $y++) {
        $years[] = $y;
    }

    return array_reverse($years);
}

function render_date_picker(
    string $prefix,
    ?string $ymd = null,
    bool $required = false,
    bool $allowEmpty = false,
    ?int $selectedDay = null,
    ?int $selectedMonth = null,
    ?int $selectedYear = null,
): string {
    if ($selectedDay !== null || $selectedMonth !== null || $selectedYear !== null) {
        $day = $selectedDay ?? 0;
        $month = $selectedMonth ?? 0;
        $year = $selectedYear ?? 0;
    } else {
        [$day, $month, $year] = date_picker_parts($ymd);
        if ($ymd === null && !$allowEmpty) {
            $day = (int) date('j');
            $month = (int) date('n');
            $year = (int) date('Y');
        }
    }

    $req = $required ? ' required' : '';
    $emptyDay = $allowEmpty ? '<option value="">วัน</option>' : '';
    $emptyMonth = $allowEmpty ? '<option value="">เดือน</option>' : '';
    $emptyYear = $allowEmpty ? '<option value="">ปี</option>' : '';

    $html = '<div class="date-selects">';
    $html .= '<select name="' . e($prefix) . '_day" aria-label="วัน"' . $req . '>' . $emptyDay;
    for ($d = 1; $d <= 31; $d++) {
        $sel = $day === $d ? ' selected' : '';
        $html .= '<option value="' . $d . '"' . $sel . '>' . $d . '</option>';
    }
    $html .= '</select>';

    $html .= '<select name="' . e($prefix) . '_month" aria-label="เดือน"' . $req . '>' . $emptyMonth;
    foreach (thai_months() as $num => $label) {
        $sel = $month === $num ? ' selected' : '';
        $html .= '<option value="' . $num . '"' . $sel . '>' . e($label) . '</option>';
    }
    $html .= '</select>';

    $html .= '<select name="' . e($prefix) . '_year" aria-label="ปี (ค.ศ.)"' . $req . '>' . $emptyYear;
    foreach (year_options() as $y) {
        $sel = $year === $y ? ' selected' : '';
        $html .= '<option value="' . $y . '"' . $sel . '>' . $y . ' (' . ($y + 543) . ')</option>';
    }
    $html .= '</select>';
    $html .= '</div>';

    return $html;
}

function parse_date_picker(string $prefix, ?array $source = null, bool $required = true): ?string
{
    $source = $source ?? $_POST;
    $dayRaw = trim((string) ($source[$prefix . '_day'] ?? ''));
    $monthRaw = trim((string) ($source[$prefix . '_month'] ?? ''));
    $yearRaw = trim((string) ($source[$prefix . '_year'] ?? ''));

    if ($dayRaw === '' && $monthRaw === '' && $yearRaw === '') {
        if ($required) {
            throw new RuntimeException('กรุณาเลือกวันที่');
        }

        return null;
    }

    if ($dayRaw === '' || $monthRaw === '' || $yearRaw === '') {
        throw new RuntimeException('กรุณาเลือกวัน เดือน และปีให้ครบ');
    }

    $day = (int) $dayRaw;
    $month = (int) $monthRaw;
    $year = (int) $yearRaw;

    if (!checkdate($month, $day, $year)) {
        throw new RuntimeException('วันที่ไม่ถูกต้อง');
    }

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function format_date_th(?string $ymd): string
{
    if (!$ymd) {
        return '—';
    }

    $ts = strtotime($ymd);
    if ($ts === false) {
        return '—';
    }

    $months = thai_months();
    $month = $months[(int) date('n', $ts)] ?? '';

    return (int) date('j', $ts) . ' ' . $month . ' ' . ((int) date('Y', $ts) + 543);
}

/** @return list<array<string, mixed>> */
function get_approved_reviews(int $limit = 20): array
{
    $stmt = db()->prepare("
        SELECT p.id, p.title, p.content, p.rating, p.booking_place, p.booking_date,
               p.guest_name, p.cover_image, p.created_at, u.full_name
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE p.post_type = 'review' AND p.status = 'approved'
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/** @param array<string, mixed> $row */
function map_review_api_row(array $row): array
{
    $cover = $row['cover_image'] ?? null;
    if ($cover && !preg_match('#^https?://#i', (string) $cover)) {
        $cover = SITE_BASE . '/' . ltrim((string) $cover, '/');
    }

    $bookingDate = $row['booking_date'] ?? null;
    if ($bookingDate) {
        $bookingDate = format_date_th((string) $bookingDate);
    }

    return [
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'text' => $row['content'],
        'rating' => (int) $row['rating'],
        'booking_place' => $row['booking_place'],
        'booking_date' => $bookingDate,
        'guest_name' => $row['guest_name'],
        'cover' => $cover,
        'author' => $row['full_name'],
        'date' => substr((string) $row['created_at'], 0, 10),
    ];
}

function export_approved_reviews_json(): void
{
    $reviews = array_map('map_review_api_row', get_approved_reviews());
    $dir = BASE_PATH . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        $dir . '/reviews.json',
        json_encode(['ok' => true, 'reviews' => $reviews], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

/** @return list<array<string, mixed>> */
function get_approved_listings(string $type, int $limit = 20): array
{
    if (!in_array($type, ['hotel', 'restaurant', 'tour'], true)) {
        return [];
    }

    $stmt = db()->prepare("
        SELECT p.id, p.title, p.content, p.price, p.location, p.cover_image, p.created_at, u.full_name
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE p.post_type = ? AND p.status = 'approved'
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $type, PDO::PARAM_STR);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/** @param array<string, mixed> $row */
function map_listing_api_row(array $row): array
{
    $cover = $row['cover_image'] ?? null;
    if ($cover) {
        $cover = SITE_BASE . '/' . ltrim((string) $cover, '/');
    }

    return [
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'text' => $row['content'],
        'price' => $row['price'],
        'location' => $row['location'],
        'cover' => $cover,
        'author' => $row['full_name'],
        'date' => substr((string) $row['created_at'], 0, 10),
    ];
}

function export_approved_listings_json(): void
{
    $payload = [
        'ok' => true,
        'hotels' => array_map('map_listing_api_row', get_approved_listings('hotel')),
        'restaurants' => array_map('map_listing_api_row', get_approved_listings('restaurant')),
        'tours' => array_map('map_listing_api_row', get_approved_listings('tour')),
    ];
    $dir = BASE_PATH . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        $dir . '/listings.json',
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

function export_public_content_json(): void
{
    export_approved_reviews_json();
    export_approved_listings_json();
}

function public_asset_url(?string $path): ?string
{
    if (!$path) {
        return null;
    }

    return SITE_BASE . '/' . ltrim($path, '/');
}
