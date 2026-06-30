<?php
declare(strict_types=1);

/**
 * เพิ่มรีวิวตัวอย่าง (รันครั้งเดียวหรือเมื่อต้องการเติมข้อมูล)
 * C:\xampp\php\php.exe seed-reviews.php
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

/** @return list<array<string, mixed>> */
function sample_reviews_data(): array
{
    $avatar = static fn (int $id): string => 'https://images.pexels.com/photos/'
        . $id . '/pexels-photo-' . $id . '.jpeg?auto=compress&cs=tinysrgb&w=200';

    return [
        [
            'title' => 'พักลิบง บีช รีสอร์ท วิวทะเลสวยมาก',
            'content' => 'ห้องสะอาด วิวทะเลตอนเช้าสวยมาก พนักงานยิ้มแย้ม อาหารเช้าอร่อย ลูกชอบเล่นชายหาดหน้ารีสอร์ทมาก จองผ่านเว็บตอบไว แนะนำครอบครัวที่อยากพักสบายๆ บนเกาะลิบง',
            'rating' => 5,
            'booking_place' => 'ลิบง บีช รีสอร์ท',
            'booking_date' => '2026-03-15',
            'guest_name' => 'คุณสมหญิง · ครอบครัว 4 ท่าน',
            'cover_image' => $avatar(6698714),
        ],
        [
            'title' => 'ทัวร์ 4 เกาะ คุ้มค่ามาก',
            'content' => 'ไปเกาะมุก ถ้ำมรกต เกาะกระดาน ครบทุกจุด ไกด์อธิบายดี อาหารกลางวันอร่อย น้ำทะเลใส ลูกดำน้ำสนุกมาก ราคาโปรโมชันดีกว่าจองหน้างาน ประทับใจทีมงานดูแลดีตลอดทริป',
            'rating' => 5,
            'booking_place' => 'แพ็คเกจทัวร์ 4 เกาะ',
            'booking_date' => '2026-05-20',
            'guest_name' => 'ครอบครัวธนกฤต · 8 ท่าน',
            'cover_image' => $avatar(237741),
        ],
        [
            'title' => 'ได้เห็นพะยูนจริงๆ ตื่นเต้นมาก',
            'content' => 'ล่องเรือชมพะยูนตอนเช้า เจอ 3 ตัวเลย ไกด์ท้องถิ่นรู้จักจุดดี อธิบายเรื่องหญ้าทะเลและการอนุรักษ์ชัดเจน บรรยากาศเงียบสงบ เหมาะกับคนชอบธรรมชาติ ไม่ผิดหวังเลย',
            'rating' => 5,
            'booking_place' => 'ทัวร์ชมพะยูน',
            'booking_date' => '2026-06-02',
            'guest_name' => 'มิ้นท์ · คู่รัก',
            'cover_image' => $avatar(90427),
        ],
        [
            'title' => 'โฮมสเตย์บ้านพร้าว อบอุ่นเหมือนบ้าน',
            'content' => 'เจ้าของบ้านใจดี ทำอาหารเย็นให้ทาน ห้องพักเรียบง่ายแต่สะอาด ตื่นมาเห็นทะเลหน้าบ้าน ราคาไม่แพง เหมาะกับสายประหยัดที่อยากสัมผัสวิถีชุมชน จะกลับมาอีกแน่นอน',
            'rating' => 4,
            'booking_place' => 'หลังเขา โฮมสเตย์ ลิบง',
            'booking_date' => '2026-04-10',
            'guest_name' => 'ก้อง · 2 คืน',
            'cover_image' => $avatar(1450363),
        ],
        [
            'title' => 'อาหารทะเลสดมาก รสชาติจัดจ้าน',
            'content' => 'มาทานมื้อเย็นที่ร้านแนะนำ ปูผัดผงกะหรี่และปลากะพงทอดน้ำปลาอร่อยมาก วัตถุดิบสดจากชาวประมงท้องถิ่น ราคาไม่แพงเมื่อเทียบกับคุณภาพ นั่งกินริมทะเลบรรยากาศดี',
            'rating' => 5,
            'booking_place' => 'ร้านอาหารทะเลบ้านพร้าว',
            'booking_date' => '2026-06-18',
            'guest_name' => 'เจน · กลุ่มเพื่อน 5 คน',
            'cover_image' => $avatar(31029704),
        ],
        [
            'title' => 'เช่าเรือตกหมึก สนุกมากยามค่ำคืน',
            'content' => 'จองเรือตกหมึกผ่านเว็บ ลุงเรือใจดี สอนวิธีตกให้ ได้หมึกหลายตัว เอาไปทำย่างที่โฮมสเตย์อร่อยมาก ประสบการณ์ที่หาไม่ได้ในเมือง แนะนำลองสักครั้ง',
            'rating' => 5,
            'booking_place' => 'เช่าเรือตกหมึก',
            'booking_date' => '2026-05-28',
            'guest_name' => 'ปิยะ · 4 ท่าน',
            'cover_image' => $avatar(13680386),
        ],
        [
            'title' => 'ดำน้ำดูปะการัง น้ำใสเห็นปลาเยอะ',
            'content' => 'น้ำรอบเกาะใสมาก เห็นปลาเล็กปลาใหญ่เยอะ อุปกรณ์ที่ให้ยืมสะอาด ครูดำน้ำดูแลดี เหมาะกับมือใหม่ แดดแรงแต่คุ้มค่า ถ่ายรูปได้สวยมาก',
            'rating' => 4,
            'booking_place' => 'ทัวร์ดำน้ำตื้น',
            'booking_date' => '2026-06-12',
            'guest_name' => 'น้ำหวาน · 3 ท่าน',
            'cover_image' => $avatar(6173672),
        ],
    ];
}

function seed_reviews_member_id(PDO $db): int
{
    foreach (['member@kohlibong.com', 'admin@kohlibong.com'] as $email) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
    }

    $hash = password_hash('member123', PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO users (email, password_hash, full_name, phone, member_type, role, status, subscription_start, subscription_end) VALUES (?,?,?,?,?,?,?,?,?)")
        ->execute([
            'member@kohlibong.com', $hash, 'สมาชิกทดสอบ', '0812345678', 'reviewer', 'member', 'active',
            date('Y-m-d'), date('Y-m-d', strtotime('+30 days')),
        ]);

    return (int) $db->lastInsertId();
}

$db = db();
$userId = seed_reviews_member_id($db);
$insert = $db->prepare("
    INSERT INTO posts (user_id, post_type, title, content, rating, booking_place, booking_date, guest_name, cover_image, status, reviewed_at)
    VALUES (?, 'review', ?, ?, ?, ?, ?, ?, ?, 'approved', NOW())
");

$added = 0;
foreach (sample_reviews_data() as $row) {
    $check = $db->prepare("SELECT id FROM posts WHERE post_type = 'review' AND title = ? LIMIT 1");
    $check->execute([$row['title']]);
    if ($check->fetch()) {
        continue;
    }

    $insert->execute([
        $userId,
        $row['title'],
        $row['content'],
        $row['rating'],
        $row['booking_place'],
        $row['booking_date'],
        $row['guest_name'],
        $row['cover_image'],
    ]);
    $added++;
}

export_approved_reviews_json();

echo "เพิ่มรีวิวใหม่ {$added} รายการ (user_id={$userId})\n";
echo 'export: data/reviews.json อัปเดตแล้ว — รวม ' . count(get_approved_reviews()) . " รีวิว\n";
