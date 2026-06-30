<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/** @return array<string, array{label:string,icon:string,desc:string,preview?:string,parent?:string}> */
function site_content_sections(): array
{
    return [
        'activity' => [
            'label' => 'ทำอะไรบนเกาะลิบง',
            'icon' => 'compass',
            'desc' => 'บทความ / ไฮไลต์กิจกรรมบนเกาะ',
            'preview' => 'ref/nav-activity.png',
        ],
        'tour' => [
            'label' => 'แพ็คเกจทัวร์',
            'icon' => 'map',
            'desc' => 'โปรแกรมทัวร์ ราคา และแบดจ์',
            'preview' => 'ref/nav-tour.png',
        ],
        'boat' => [
            'label' => 'ตั๋วเรือ - เช่าเรือ',
            'icon' => 'ship',
            'desc' => 'บริการเรือและราคาเช่าเหมา',
            'preview' => 'ref/nav-boat.png',
        ],
        'hotel' => [
            'label' => 'ที่พักบนเกาะลิบง',
            'icon' => 'bed-double',
            'desc' => 'การ์ดที่พักพร้อมอัลบั้มรูป',
            'preview' => 'ref/nav-hotel.png',
        ],
        'hotel_mini' => [
            'label' => 'สไลด์มินิที่พัก',
            'icon' => 'gallery-horizontal',
            'desc' => 'บทความที่พักในแถบสไลด์ใต้การ์ดหลัก',
            'preview' => 'ref/nav-hotel-mini.png',
            'parent' => 'hotel',
        ],
        'restaurant' => [
            'label' => 'ร้านอาหารบนเกาะลิบง',
            'icon' => 'utensils',
            'desc' => 'ร้านอาหารและคาเฟ่แนะนำ',
            'preview' => 'ref/nav-restaurant.png',
        ],
    ];
}

/** @return array<string, array{label:string,icon:string,desc:string,parent?:string}> */
function site_content_nav_sections(): array
{
    return array_filter(
        site_content_sections(),
        static fn (array $meta): bool => empty($meta['parent'])
    );
}

/** @return list<string> */
function site_content_export_section_keys(): array
{
    return array_keys(site_content_sections());
}

function site_content_section_label(string $section): string
{
    return site_content_sections()[$section]['label'] ?? $section;
}

function site_content_public_image(?string $path): ?string
{
    if (!$path) {
        return null;
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return SITE_BASE . '/' . ltrim($path, '/');
}

/** @return list<array<string, mixed>> */
function site_content_list(string $section, bool $activeOnly = false): array
{
    $sql = 'SELECT * FROM site_content WHERE section = ?';
    if ($activeOnly) {
        $sql .= ' AND is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute([$section]);

    return $stmt->fetchAll();
}

function site_content_get(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM site_content WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

function site_content_next_sort(string $section): int
{
    $stmt = db()->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM site_content WHERE section = ?');
    $stmt->execute([$section]);

    return (int) $stmt->fetchColumn();
}

/** @param array<string, mixed> $row */
function map_site_content_api_row(array $row): array
{
    $image = site_content_public_image($row['image_path'] ?? null);
    $gallery = [];
    if (!empty($row['gallery_images'])) {
        $decoded = json_decode((string) $row['gallery_images'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $g) {
                $gallery[] = site_content_public_image((string) $g) ?? (string) $g;
            }
        }
    }
    $amenities = [];
    if (!empty($row['amenities'])) {
        $decoded = json_decode((string) $row['amenities'], true);
        if (is_array($decoded)) {
            $amenities = $decoded;
        }
    }

    return [
        'id' => (int) $row['id'],
        'section' => $row['section'],
        'title' => $row['title'],
        'subtitle' => $row['subtitle'],
        'tag' => $row['tag'],
        'badge' => $row['badge'],
        'badge_type' => $row['badge_type'] ?: 'left',
        'location' => $row['location'],
        'description' => $row['description'],
        'price' => $row['price'],
        'price_old' => $row['price_old'],
        'rating' => $row['rating'],
        'review_count' => $row['review_count'],
        'stars' => $row['stars'] !== null ? (int) $row['stars'] : null,
        'amenities' => $amenities,
        'gallery' => $gallery,
        'image' => $image,
    ];
}

function export_site_content_json(): void
{
    $payload = ['ok' => true];
    foreach (site_content_export_section_keys() as $section) {
        $rows = site_content_list($section, true);
        $payload[$section] = array_map('map_site_content_api_row', $rows);
    }

    $dir = BASE_PATH . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        $dir . '/site-content.json',
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

function site_content_save_from_post(array $post, ?array $files = null): int
{
    $section = (string) ($post['section'] ?? '');
    $sections = site_content_sections();
    if (!isset($sections[$section])) {
        throw new RuntimeException('ประเภทเนื้อหาไม่ถูกต้อง');
    }

    $id = (int) ($post['id'] ?? 0);
    $existing = $id > 0 ? site_content_get($id) : null;
    if ($id > 0 && !$existing) {
        throw new RuntimeException('ไม่พบรายการ');
    }
    if ($existing && $existing['section'] !== $section) {
        throw new RuntimeException('ไม่สามารถเปลี่ยนหมวดได้');
    }

    $title = trim((string) ($post['title'] ?? ''));
    if ($title === '') {
        throw new RuntimeException('กรุณากรอกหัวข้อ');
    }

    $imagePath = $existing['image_path'] ?? null;
    if (!empty($files['image']['tmp_name']) && ($files['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if (!is_dir(SITE_CONTENT_DIR)) {
            mkdir(SITE_CONTENT_DIR, 0755, true);
        }
        $imagePath = save_upload('image', SITE_CONTENT_DIR, $section);
    } elseif (trim((string) ($post['image_url'] ?? '')) !== '') {
        $imagePath = trim((string) $post['image_url']);
    }

    $galleryImages = site_content_resolve_gallery($post, $files, $existing, $section);

    $amenitiesRaw = trim((string) ($post['amenities'] ?? ''));
    $amenities = null;
    if ($amenitiesRaw !== '') {
        $items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $amenitiesRaw))));
        $amenities = json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    $data = [
        'title' => $title,
        'subtitle' => trim((string) ($post['subtitle'] ?? '')) ?: null,
        'tag' => trim((string) ($post['tag'] ?? '')) ?: null,
        'badge' => trim((string) ($post['badge'] ?? '')) ?: null,
        'badge_type' => in_array($post['badge_type'] ?? '', ['left', 'right', 'sale'], true) ? $post['badge_type'] : 'left',
        'location' => trim((string) ($post['location'] ?? '')) ?: null,
        'description' => trim((string) ($post['description'] ?? '')) ?: null,
        'price' => trim((string) ($post['price'] ?? '')) ?: null,
        'price_old' => trim((string) ($post['price_old'] ?? '')) ?: null,
        'rating' => trim((string) ($post['rating'] ?? '')) ?: null,
        'review_count' => trim((string) ($post['review_count'] ?? '')) ?: null,
        'stars' => ($post['stars'] ?? '') !== '' ? (int) $post['stars'] : null,
        'amenities' => $amenities,
        'gallery_images' => $galleryImages,
        'image_path' => $imagePath,
        'sort_order' => (int) ($post['sort_order'] ?? ($existing['sort_order'] ?? site_content_next_sort($section))),
        'is_active' => isset($post['is_active']) ? 1 : 0,
    ];

    if ($id > 0) {
        $stmt = db()->prepare('UPDATE site_content SET section=?, title=?, subtitle=?, tag=?, badge=?, badge_type=?, location=?, description=?, price=?, price_old=?, rating=?, review_count=?, stars=?, amenities=?, gallery_images=?, image_path=?, sort_order=?, is_active=? WHERE id=?');
        $stmt->execute([
            $section, $data['title'], $data['subtitle'], $data['tag'], $data['badge'], $data['badge_type'],
            $data['location'], $data['description'], $data['price'], $data['price_old'], $data['rating'],
            $data['review_count'], $data['stars'], $data['amenities'], $data['gallery_images'], $data['image_path'],
            $data['sort_order'], $data['is_active'], $id,
        ]);
    } else {
        $stmt = db()->prepare('INSERT INTO site_content (section, title, subtitle, tag, badge, badge_type, location, description, price, price_old, rating, review_count, stars, amenities, gallery_images, image_path, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $section, $data['title'], $data['subtitle'], $data['tag'], $data['badge'], $data['badge_type'],
            $data['location'], $data['description'], $data['price'], $data['price_old'], $data['rating'],
            $data['review_count'], $data['stars'], $data['amenities'], $data['gallery_images'], $data['image_path'],
            $data['sort_order'], $data['is_active'],
        ]);
        $id = (int) db()->lastInsertId();
    }

    export_site_content_json();

    return $id;
}

function site_content_delete(int $id): void
{
    $row = site_content_get($id);
    if (!$row) {
        throw new RuntimeException('ไม่พบรายการ');
    }

    db()->prepare('DELETE FROM site_content WHERE id = ?')->execute([$id]);
    export_site_content_json();
}

function site_content_gallery_lines(?array $row): string
{
    if (!$row || empty($row['gallery_images'])) {
        return '';
    }
    $decoded = json_decode((string) $row['gallery_images'], true);

    return is_array($decoded) ? implode("\n", $decoded) : '';
}

function site_content_amenities_text(?array $row): string
{
    if (!$row || empty($row['amenities'])) {
        return '';
    }
    $decoded = json_decode((string) $row['amenities'], true);

    return is_array($decoded) ? implode("\n", $decoded) : '';
}

/** @return list<string> */
function site_content_gallery_paths(?array $row): array
{
    if (!$row || empty($row['gallery_images'])) {
        return [];
    }
    $decoded = json_decode((string) $row['gallery_images'], true);

    return is_array($decoded) ? array_values($decoded) : [];
}

function site_content_resolve_gallery(array $post, ?array $files, ?array $existing, string $section): ?string
{
    $gallery = [];
    $keep = $post['gallery_keep'] ?? null;
    if (is_array($keep)) {
        foreach ($keep as $path) {
            $path = trim((string) $path);
            if ($path !== '') {
                $gallery[] = $path;
            }
        }
    } elseif ($existing && !empty($existing['gallery_images'])) {
        $gallery = site_content_gallery_paths($existing);
    }

    if ($files && !empty($files['gallery_upload']['tmp_name'])) {
        if (!is_dir(SITE_CONTENT_DIR)) {
            mkdir(SITE_CONTENT_DIR, 0755, true);
        }
        $uploaded = save_upload_batch('gallery_upload', SITE_CONTENT_DIR, $section . '_gal');
        $gallery = array_merge($gallery, $uploaded);
    }

    $galleryRaw = trim((string) ($post['gallery_urls'] ?? ''));
    if ($galleryRaw !== '') {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $galleryRaw))));
        $gallery = array_merge($gallery, $lines);
    }

    $gallery = array_values(array_unique($gallery));

    return $gallery ? json_encode($gallery, JSON_UNESCAPED_UNICODE) : null;
}

/** @return list<array<string, mixed>> */
function site_content_hotel_mini_default_rows(): array
{
    $px = static fn (int $id, int $w = 420): string =>
        "https://images.pexels.com/photos/{$id}/pexels-photo-{$id}.jpeg?auto=compress&cs=tinysrgb&w={$w}";

    $slides = [
        ['รีวิวโฮมสเตย์ริมทะเลเกาะลิบง บรรยากาศดี ราคาประหยัด', '03 มิ.ย. 2026', '402', 271624],
        ['10 ที่พักเกาะลิบง วิวทะเลสวย ใกล้ชายหาด', '26 พ.ค. 2026', '296', 261102],
        ['พักบังกะโลเกาะลิบง ชมพระอาทิตย์ตกที่แหลมจุโหย', '18 พ.ค. 2026', '886', 1032650],
        ['โฮมสเตย์วิถีชุมชนมุสลิม อาหารพื้นบ้านอร่อย', '20 มิ.ย. 2026', '567', 2037926],
        ['รีสอร์ทริมหาดบ้านพร้าว เงียบสงบ เหมาะพักผ่อน', '04 มิ.ย. 2026', '612', 29974430],
        ['ที่พักเกาะลิบง ใกล้ท่าเรือ เดินทางสะดวก', '02 มิ.ย. 2026', '380', 1591376],
        ['รีวิวที่พักครอบครัวบนเกาะลิบง ปลอดภัย สะอาด', '29 พ.ค. 2026', '529', 5740342],
        ['เช็กลิสต์จองที่พักเกาะลิบง ก่อนไปเที่ยว', '29 พ.ค. 2026', '219', 1450363],
    ];

    $rows = [];
    foreach ($slides as $i => [$title, $date, $views, $imgId]) {
        $rows[] = site_content_pack_row([
            'section' => 'hotel_mini',
            'title' => $title,
            'subtitle' => $date,
            'tag' => 'ที่พัก',
            'review_count' => $views,
            'image' => $px($imgId),
            'sort' => $i + 1,
        ]);
    }

    return $rows;
}

function site_content_seed_hotel_mini(): void
{
    $count = (int) db()->query("SELECT COUNT(*) FROM site_content WHERE section = 'hotel_mini'")->fetchColumn();
    if ($count > 0) {
        return;
    }

    site_content_insert_rows(site_content_hotel_mini_default_rows());
}

function site_content_ensure_hotel_mini_enum(): void
{
    db()->exec("
        ALTER TABLE site_content
        MODIFY section ENUM('activity', 'tour', 'boat', 'hotel', 'hotel_mini', 'restaurant') NOT NULL
    ");
}

function site_content_pack_row(array $r): array
{
  $amenities = $r['amenities'] ?? null;
  $gallery = $r['gallery'] ?? null;

  return [
    $r['section'],
    $r['title'],
    $r['subtitle'] ?? null,
    $r['tag'] ?? null,
    $r['badge'] ?? null,
    $r['badge_type'] ?? 'left',
    $r['location'] ?? null,
    $r['description'] ?? null,
    $r['price'] ?? null,
    $r['price_old'] ?? null,
    $r['rating'] ?? null,
    $r['review_count'] ?? null,
    $r['stars'] ?? null,
    is_array($amenities) ? json_encode($amenities, JSON_UNESCAPED_UNICODE) : null,
    is_array($gallery) ? json_encode($gallery, JSON_UNESCAPED_UNICODE) : null,
    $r['image'] ?? null,
    (int) ($r['sort'] ?? 0),
  ];
}

/** @return list<array<int, mixed>> */
function site_content_default_rows(): array
{
  $px = static fn (int $id, int $w = 640): string =>
    "https://images.pexels.com/photos/{$id}/pexels-photo-{$id}.jpeg?auto=compress&cs=tinysrgb&w={$w}";

  $g = static fn (array $ids, int $w = 600): array => array_map(static fn (int $id) => $px($id, $w), $ids);

  $rows = [
    // ทำอะไรบนเกาะลิบง (8)
    ['section' => 'activity', 'title' => 'ดูพะยูนกลางทะเลอันดามัน สัญลักษณ์ของเกาะลิบง', 'subtitle' => 'เที่ยวเกาะ · 03 มิ.ย. 2026 · 402 อ่าน', 'tag' => 'ไฮไลต์', 'image' => $px(6691933), 'sort' => 1],
    ['section' => 'activity', 'title' => 'ล่องเรือชมป่าชายเลนและหญ้าทะเลผืนใหญ่', 'subtitle' => 'เที่ยวเกาะ · 26 พ.ค. 2026 · 296 อ่าน', 'tag' => 'ธรรมชาติ', 'image' => $px(36405819), 'sort' => 2],
    ['section' => 'activity', 'title' => 'ชมพระอาทิตย์ตกที่แหลมจุโหย จุดชมวิวสุดโรแมนติก', 'subtitle' => 'เที่ยวเกาะ · 18 พ.ค. 2026 · 886 อ่าน', 'tag' => 'วิวสวย', 'image' => $px(1032650), 'sort' => 3],
    ['section' => 'activity', 'title' => 'สัมผัสวิถีชุมชนมุสลิมและอาหารพื้นบ้านรสเด็ด', 'subtitle' => 'เที่ยวเกาะ · 30 มิ.ย. 2026 · 530 อ่าน', 'tag' => 'วัฒนธรรม', 'image' => $px(2037926), 'sort' => 4],
    ['section' => 'activity', 'title' => 'ดำน้ำดูปะการังน้ำตื้นรอบเกาะ น้ำใสมองเห็นพื้นทราย', 'subtitle' => 'เที่ยวเกาะ · 04 มิ.ย. 2026 · 898 อ่าน', 'tag' => 'ดำน้ำ', 'image' => $px(15763636), 'sort' => 5],
    ['section' => 'activity', 'title' => 'ตกหมึกยามค่ำคืน กิจกรรมสุดฮิตของนักท่องเที่ยว', 'subtitle' => 'เที่ยวเกาะ · 02 มิ.ย. 2026 · 463 อ่าน', 'tag' => 'กิจกรรม', 'image' => $px(4171737), 'sort' => 6],
    ['section' => 'activity', 'title' => 'ปั่นจักรยานเที่ยวรอบเกาะ สูดอากาศบริสุทธิ์', 'subtitle' => 'เที่ยวเกาะ · 29 พ.ค. 2026 · 529 อ่าน', 'tag' => 'ผจญภัย', 'image' => $px(100582), 'sort' => 7],
    ['section' => 'activity', 'title' => 'พักโฮมสเตย์ริมทะเล สัมผัสชีวิตชาวเล', 'subtitle' => 'เที่ยวเกาะ · 29 พ.ค. 2026 · 2,190 อ่าน', 'tag' => 'ที่พัก', 'image' => $px(271624), 'sort' => 8],

    // แพ็คเกจทัวร์ (8)
    ['section' => 'tour', 'title' => 'โปรแกรม 4 เกาะ ทะเลตรัง', 'subtitle' => 'เกาะมุก → ถ้ำมรกต → เกาะกระดาน → ...', 'badge' => 'ขายดี', 'badge_type' => 'left', 'price' => '3,500', 'price_old' => '4,200', 'rating' => '4.9', 'review_count' => '3.2พัน', 'image' => $px(14573822), 'sort' => 1],
    ['section' => 'tour', 'title' => 'โปรแกรมเกาะกระดาน ดำน้ำดูปะการัง', 'subtitle' => 'เกาะกระดาน → จุดดำน้ำ → ชมพระอาทิตย์', 'badge' => 'ประหยัด 18%', 'badge_type' => 'sale', 'price' => '4,500', 'price_old' => '5,500', 'rating' => '4.8', 'review_count' => '1.8พัน', 'image' => $px(1450363), 'sort' => 2],
    ['section' => 'tour', 'title' => 'โปรแกรมดำน้ำลึกเกาะลิบง', 'subtitle' => 'เกาะลิบง → เกาะม้า → เกาะแหวน', 'badge' => 'ประหยัด 15%', 'badge_type' => 'sale', 'price' => '5,500', 'price_old' => '6,500', 'rating' => '4.9', 'review_count' => '1.4พัน', 'image' => $px(14573822), 'sort' => 3],
    ['section' => 'tour', 'title' => 'โปรแกรมเกาะมุก / ถ้ำมรกต', 'subtitle' => 'เกาะมุก → ถ้ำมรกต → จุดดำน้ำ', 'badge' => 'ไฮไลต์', 'badge_type' => 'right', 'price' => '3,200', 'price_old' => '3,800', 'rating' => '4.9', 'review_count' => '2.5พัน', 'image' => $px(1450363), 'sort' => 4],
    ['section' => 'tour', 'title' => 'โปรแกรมชมพะยูนเกาะลิบง', 'subtitle' => 'แหลมจุโหย → หญ้าทะเล → ดูพะยูน', 'badge' => 'ขายดี', 'badge_type' => 'left', 'price' => '3,900', 'price_old' => '4,500', 'rating' => '5.0', 'review_count' => '980', 'image' => $px(15763636), 'sort' => 5],
    ['section' => 'tour', 'title' => 'โปรแกรมตกหมึกยามค่ำคืน', 'subtitle' => 'ท่าเรือบ้านพร้าว → จุดตกหมึก', 'badge' => 'ประหยัด 20%', 'badge_type' => 'sale', 'price' => '1,990', 'price_old' => '2,500', 'rating' => '4.7', 'review_count' => '640', 'image' => $px(1647064), 'sort' => 6],
    ['section' => 'tour', 'title' => 'โปรแกรมล่องเรือชมป่าชายเลน', 'subtitle' => 'คลองลิบง → ป่าโกงกาง → ชมนก', 'badge' => 'ธรรมชาติ', 'badge_type' => 'right', 'price' => '1,500', 'price_old' => '1,800', 'rating' => '4.8', 'review_count' => '410', 'image' => $px(36405819), 'sort' => 7],
    ['section' => 'tour', 'title' => 'โปรแกรมเหมาลำส่วนตัว 1 วัน', 'subtitle' => 'จัดเส้นทางได้เอง → เรือส่วนตัว', 'badge' => 'พรีเมียม', 'badge_type' => 'left', 'price' => '6,900', 'price_old' => '8,000', 'rating' => '5.0', 'review_count' => '255', 'image' => $px(17942107), 'sort' => 8],

    // ตั๋วเรือ - เช่าเรือ (4)
    ['section' => 'boat', 'title' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก', 'description' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก น่าเที่ยว เรือออกทุกชั่วโมง บริการรับ-ส่งถึงท่าเรือ', 'price' => '1,800', 'image' => $px(14573822), 'sort' => 1],
    ['section' => 'boat', 'title' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก', 'description' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก น่าเที่ยว เรือออกทุกชั่วโมง บริการรับ-ส่งถึงท่าเรือ', 'price' => '1,800', 'image' => $px(1647064), 'sort' => 2],
    ['section' => 'boat', 'title' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก', 'description' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก น่าเที่ยว เรือออกทุกชั่วโมง บริการรับ-ส่งถึงท่าเรือ', 'price' => '1,800', 'image' => $px(36405819), 'sort' => 3],
    ['section' => 'boat', 'title' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก', 'description' => 'จองตั๋วเรือไปเกาะลิบง ตกหมึก น่าเที่ยว เรือออกทุกชั่วโมง บริการรับ-ส่งถึงท่าเรือ', 'price' => '1,800', 'image' => $px(15763636), 'sort' => 4],

    // ที่พักบนเกาะลิบง (4)
    ['section' => 'hotel', 'title' => 'ลิบง บีช รีสอร์ท', 'location' => 'หาดบ้านพร้าว, เกาะลิบง', 'description' => 'ที่พักริมหาดบรรยากาศเงียบสงบ มองเห็นวิวทะเลอันดามัน เดินถึงชายหาดได้ทันที', 'price' => '4,740', 'stars' => 4, 'amenities' => ['Wi-Fi ฟรี', 'ติดทะเล', 'อาหารเช้า'], 'gallery' => $g([261102, 29974430, 1450363, 13419316, 31029704, 271624, 5740342, 18297054]), 'image' => $px(261102), 'sort' => 1],
    ['section' => 'hotel', 'title' => 'หลังเขา โฮมสเตย์ ลิบง', 'location' => 'บ้านบาตูปูเต๊ะ, เกาะลิบง', 'description' => 'โฮมสเตย์วิถีชุมชน สัมผัสชีวิตชาวเลแท้ ๆ พร้อมอาหารพื้นบ้านรสเด็ดทุกมื้อ', 'price' => '1,290', 'stars' => 4, 'amenities' => ['Wi-Fi ฟรี', 'จักรยานฟรี', 'จุดชมวิว'], 'gallery' => $g([271624, 1457842, 2037926, 1267320, 31029704, 1591376, 6698714, 457881]), 'image' => $px(271624), 'sort' => 2],
    ['section' => 'hotel', 'title' => 'ดูหยง ซีวิว บังกะโล', 'location' => 'แหลมจุโหย, เกาะลิบง', 'description' => 'บังกะโลริมทะเลพร้อมระเบียงส่วนตัว ชมพระอาทิตย์ตกได้จากห้องพัก', 'price' => '3,200', 'stars' => 5, 'amenities' => ['Wi-Fi ฟรี', 'สระว่ายน้ำ', 'ติดทะเล'], 'gallery' => $g([1450363, 2486168, 1032650, 271624, 13419316, 29974430, 18297054, 28581876]), 'image' => $px(1450363), 'sort' => 3],
    ['section' => 'hotel', 'title' => 'เลตรัง รีสอร์ท แอนด์ สปา', 'location' => 'หาดทุ่งหญ้าคา, เกาะลิบง', 'description' => 'รีสอร์ทระดับพรีเมียม พร้อมสปาและสระว่ายน้ำ บริการครบครันเพื่อการพักผ่อน', 'price' => '5,900', 'stars' => 5, 'amenities' => ['สปา', 'สระว่ายน้ำ', 'อาหารเช้า'], 'gallery' => $g([5740342, 338504, 261181, 261102, 31029704, 1287146, 14573822, 457881]), 'image' => $px(5740342), 'sort' => 4],

    // ร้านอาหารบนเกาะลิบง (8)
    ['section' => 'restaurant', 'title' => 'ครัวเล ลิบง ซีฟู้ด อาหารทะเลสดจากเรือประมงพื้นบ้าน', 'subtitle' => 'ร้านอาหาร · 03 มิ.ย. 2026 · 402 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(566566), 'sort' => 1],
    ['section' => 'restaurant', 'title' => 'ร้านป้าแดง ข้าวยำปักษ์ใต้ น้ำบูดูสูตรต้นตำรับ', 'subtitle' => 'ร้านอาหาร · 26 พ.ค. 2026 · 296 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(2673353), 'sort' => 2],
    ['section' => 'restaurant', 'title' => 'ลานเล คาเฟ่ ริมทะเล กาแฟสดและของหวาน', 'subtitle' => 'ร้านอาหาร · 18 พ.ค. 2026 · 886 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(302899), 'sort' => 3],
    ['section' => 'restaurant', 'title' => 'ครัวบ้านพร้าว ปูม้านึ่ง กุ้งเผา ราคาชาวบ้าน', 'subtitle' => 'ร้านอาหาร · 20 มิ.ย. 2026 · 4,567 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(566345), 'sort' => 4],
    ['section' => 'restaurant', 'title' => 'ร้านลุงหมึก ปลาหมึกย่าง สดใหม่ทุกวัน', 'subtitle' => 'ร้านอาหาร · 04 มิ.ย. 2026 · 612 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(1438671), 'sort' => 5],
    ['section' => 'restaurant', 'title' => 'ครัวยายแป้น แกงส้มปลากะพง รสจัดจ้าน', 'subtitle' => 'ร้านอาหาร · 02 มิ.ย. 2026 · 380 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(2098085), 'sort' => 6],
    ['section' => 'restaurant', 'title' => 'ซีฟู้ดบ้านบาตู ออส่วน หอยนางรมสด', 'subtitle' => 'ร้านอาหาร · 29 พ.ค. 2026 · 529 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(725991), 'sort' => 7],
    ['section' => 'restaurant', 'title' => 'ครัวลิบงวิว อาหารใต้ พร้อมวิวทะเลพาโนรามา', 'subtitle' => 'ร้านอาหาร · 29 พ.ค. 2026 · 2,190 อ่าน', 'tag' => 'แนะนำ', 'image' => $px(1581384), 'sort' => 8],
  ];

  return array_map('site_content_pack_row', $rows);
}

function site_content_expected_total(): int
{
  return count(site_content_default_rows()) + count(site_content_hotel_mini_default_rows());
}

function site_content_insert_rows(array $packedRows): void
{
  $stmt = db()->prepare('INSERT INTO site_content (section, title, subtitle, tag, badge, badge_type, location, description, price, price_old, rating, review_count, stars, amenities, gallery_images, image_path, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)');

  foreach ($packedRows as $r) {
    $stmt->execute($r);
  }
}

function site_content_apply_defaults(bool $replace = false): void
{
  if ($replace) {
    db()->exec('DELETE FROM site_content');
  }

  site_content_insert_rows(site_content_default_rows());
  site_content_insert_rows(site_content_hotel_mini_default_rows());
  export_site_content_json();
}

const SITE_CONTENT_SEED_VERSION = 3;

function site_content_maybe_upgrade_seed(): void
{
  $flag = BASE_PATH . '/data/.site-content-seed-ver';
  $current = is_file($flag) ? (int) file_get_contents($flag) : 0;

  if ($current < 3) {
    site_content_ensure_hotel_mini_enum();
    site_content_seed_hotel_mini();
    export_site_content_json();
    if (!is_dir(BASE_PATH . '/data')) {
      mkdir(BASE_PATH . '/data', 0755, true);
    }
    file_put_contents($flag, '3');
    $current = 3;
  }

  $expected = site_content_expected_total();
  $count = (int) db()->query('SELECT COUNT(*) FROM site_content')->fetchColumn();

  if ($current >= SITE_CONTENT_SEED_VERSION && $count === $expected) {
    return;
  }

  site_content_apply_defaults(true);

  if (!is_dir(BASE_PATH . '/data')) {
    mkdir(BASE_PATH . '/data', 0755, true);
  }
  file_put_contents($flag, (string) SITE_CONTENT_SEED_VERSION);
}

function seed_site_content_defaults(): void
{
  $count = (int) db()->query('SELECT COUNT(*) FROM site_content')->fetchColumn();
  if ($count > 0) {
    site_content_maybe_upgrade_seed();

    return;
  }

  site_content_apply_defaults(false);

  $flag = BASE_PATH . '/data/.site-content-seed-ver';
  if (!is_dir(BASE_PATH . '/data')) {
    mkdir(BASE_PATH . '/data', 0755, true);
  }
  file_put_contents($flag, (string) SITE_CONTENT_SEED_VERSION);
}

function site_content_ensure_table(): void
{
    db()->exec("
        CREATE TABLE IF NOT EXISTS site_content (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          section ENUM('activity', 'tour', 'boat', 'hotel', 'hotel_mini', 'restaurant') NOT NULL,
          title VARCHAR(300) NOT NULL,
          subtitle VARCHAR(500) DEFAULT NULL,
          tag VARCHAR(80) DEFAULT NULL,
          badge VARCHAR(80) DEFAULT NULL,
          badge_type ENUM('left', 'right', 'sale') NOT NULL DEFAULT 'left',
          location VARCHAR(200) DEFAULT NULL,
          description TEXT DEFAULT NULL,
          price VARCHAR(50) DEFAULT NULL,
          price_old VARCHAR(50) DEFAULT NULL,
          rating VARCHAR(10) DEFAULT NULL,
          review_count VARCHAR(30) DEFAULT NULL,
          stars TINYINT UNSIGNED DEFAULT NULL,
          amenities JSON DEFAULT NULL,
          gallery_images JSON DEFAULT NULL,
          image_path VARCHAR(500) DEFAULT NULL,
          sort_order INT NOT NULL DEFAULT 0,
          is_active TINYINT(1) NOT NULL DEFAULT 1,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          INDEX idx_section_sort (section, sort_order, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    site_content_ensure_hotel_mini_enum();
    seed_site_content_defaults();
}
