/* ============================================================
   เกาะลิบง.com — หน้ารายละเอียดที่พัก (Trip.com-style layout)
   ============================================================ */

(function () {
  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function lucide(name, cls = 'icon') {
    return `<i data-lucide="${esc(name)}" class="${cls}" aria-hidden="true"></i>`;
  }

  function refreshLucide(root) {
    if (!window.lucide?.createIcons) return;
    const opts = { attrs: { 'stroke-width': 2 } };
    if (root) opts.root = root;
    window.lucide.createIcons(opts);
  }

  function starRow(n) {
    const count = Math.max(0, Math.min(5, Number(n) || 0));
    let html = '<span class="hd-stars" aria-label="' + count + ' ดาว">';
    for (let i = 0; i < count; i++) {
      html += lucide('star', 'icon hd-stars__icon hd-stars__icon--on');
    }
    return html + '</span>';
  }

  function amenityIcon(label) {
    const t = String(label || '');
    if (/wi-?fi|ไวไฟ/i.test(t)) return 'wifi';
    if (/ทะเล|หาด|ติดทะเล/i.test(t)) return 'waves';
    if (/อาหารเช้า|เช้า/i.test(t)) return 'coffee';
    if (/จักรยาน/i.test(t)) return 'bike';
    if (/วิว|ชมวิว/i.test(t)) return 'eye';
    if (/สระ|ว่ายน้ำ/i.test(t)) return 'droplets';
    if (/สปา/i.test(t)) return 'sparkles';
    if (/จอด|parking/i.test(t)) return 'car';
    if (/สูบ|บุหรี่/i.test(t)) return 'cigarette-off';
    if (/ห้องน้ำ|อาบน้ำ|ฝักบัว/i.test(t)) return 'bath';
    if (/ตู้เย็น/i.test(t)) return 'box';
    if (/เตียง/i.test(t)) return 'bed-double';
    return 'check';
  }

  const HOTEL_DETAILS = [
    {
      name: 'ลิบง บีช รีสอร์ท',
      opened: 'เปิดบริการปี 2018',
      openedYear: '2018',
      roomCount: '28 ห้อง',
      phone: '075-123-456',
      address: 'หาดบ้านพร้าว เกาะลิบง อำเภอกันตัง จังหวัดตรัง 92110',
      mapUrl: 'https://maps.google.com/?q=Koh+Libong+Beach',
      score: '8.9',
      scoreLabel: 'ดีมาก',
      reviewCount: '214',
      scoreBreakdown: [
        { label: 'ความสะอาด', value: 9.0 },
        { label: 'สิ่งอำนวยความสะดวก', value: 8.7 },
        { label: 'ทำเลที่ตั้ง', value: 9.2 },
        { label: 'บริการ', value: 8.8 },
      ],
      reviewSnippets: [
        'ที่พักสะอาด วิวทะเลสวย เดินลงหาดได้ทันที',
        'เจ้าของใจดี อาหารเช้าอร่อย แนะนำสำหรับครอบครัว',
      ],
      guestReviews: [
        {
          name: 'คุณนภา',
          tripType: 'ครอบครัว',
          text: 'ห้องสะอาด วิวทะเลดีมาก เด็ก ๆ เล่นทรายได้เลย อาหารเช้าพอดีและเจ้าหน้าที่สุภาพ',
          date: 'มี.ค. 2026',
        },
        {
          name: 'คุณอาร์ม',
          tripType: 'คู่รัก',
          text: 'บรรยากาศเงียบสงบ เหมาะพักผ่อน พระอาทิตย์ขึ้นสวย เดินถึงชายหาดในไม่กี่นาที',
          date: 'ก.พ. 2026',
        },
        {
          name: 'คุณมุก',
          tripType: 'เพื่อน',
          text: 'Wi-Fi ใช้ได้ดี ที่จอดรถสะดวก ใกล้ตลาดบ้านพร้าว ซื้อของกินง่าย',
          date: 'ม.ค. 2026',
        },
      ],
      highlights: [
        { icon: 'map-pin', label: 'ทำเลดีเยี่ยม' },
        { icon: 'wifi', label: 'Wi-Fi ฟรีในห้อง' },
        { icon: 'car', label: 'ที่จอดรถฟรี' },
        { icon: 'cigarette-off', label: 'ปลอดบุหรี่' },
      ],
      surroundings: [
        { icon: 'shopping-bag', category: 'ช้อปปิ้ง', name: 'ตลาดบ้านพร้าว', dist: '800 ม.' },
        { icon: 'landmark', category: 'การเดินทาง', name: 'ท่าเรือบ้านพร้าว', dist: '1.2 กม.' },
        { icon: 'mountain', category: 'ท่องเที่ยว', name: 'แหลมจุโหย', dist: '4.5 กม.' },
        { icon: 'waves', category: 'ชายหาด', name: 'หาดบ้านพร้าว', dist: '50 ม.' },
        { icon: 'utensils', category: 'ร้านอาหาร', name: 'ร้านอาหารทะเลพื้นบ้าน', dist: '600 ม.' },
        { icon: 'trees', category: 'ธรรมชาติ', name: 'ป่าชายเลนเกาะลิบง', dist: '2.1 กม.' },
      ],
      facilities: [
        { icon: 'car', label: 'ที่จอดรถส่วนตัว — ฟรี' },
        { icon: 'wifi', label: 'Wi-Fi ในพื้นที่ส่วนกลาง' },
        { icon: 'cigarette-off', label: 'ห้ามสูบบุหรี่ในพื้นที่ส่วนกลาง' },
        { icon: 'shield', label: 'เจ้าหน้าที่รักษาความปลอดภัย' },
        { icon: 'key-round', label: 'เข้าห้องด้วยคีย์การ์ด' },
        { icon: 'video', label: 'กล้องวงจรปิดพื้นที่ส่วนกลาง' },
        { icon: 'waves', label: 'ติดชายหาด' },
        { icon: 'coffee', label: 'อาหารเช้า' },
      ],
      facilityGroups: [
        {
          title: 'อินเทอร์เน็ต',
          items: ['Wi-Fi ฟรีในห้องพัก', 'Wi-Fi ในพื้นที่ส่วนกลาง'],
        },
        {
          title: 'ที่จอดรถ',
          items: ['ที่จอดรถส่วนตัว — ฟรี', 'ที่จอดรถมอเตอร์ไซค์'],
        },
        {
          title: 'พื้นที่ส่วนกลาง',
          items: ['ล็อบบี้', 'ระเบียงชมวิวทะเล', 'มุมนั่งเล่นกลางแจ้ง'],
        },
        {
          title: 'ความปลอดภัย',
          items: ['กล้องวงจรปิด', 'เจ้าหน้าที่รักษาความปลอดภัย', 'เข้าห้องด้วยคีย์การ์ด'],
        },
        {
          title: 'บริการ',
          items: ['อาหารเช้า', 'บริการรับฝากกระเป๋า', 'เคาน์เตอร์ต้อนรับ'],
        },
        {
          title: 'ห้องพัก',
          items: ['ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ', 'ห้ามสูบบุหรี่'],
        },
      ],
      policies: {
        checkIn: '14:00 – 22:00',
        checkOut: 'ก่อน 12:00',
        children: 'ยินดีต้อนรับเด็กทุกวัย (อาจมีค่าใช้จ่ายเพิ่มสำหรับเตียงเสริม)',
        breakfast: 'มีอาหารเช้า (เลือกซื้อเพิ่มได้ที่หน้าเคาน์เตอร์)',
        pets: 'ไม่อนุญาตให้นำสัตว์เลี้ยงเข้าพัก',
        deposit: 'อาจมีการเก็บมัดจำเงินสดหรือบัตรเครดิตเมื่อเช็คอิน',
      },
      about:
        'ลิบง บีช รีสอร์ท ตั้งอยู่ริมหาดบ้านพร้าว เหมาะสำหรับครอบครัวและคู่รักที่อยากพักใกล้ทะเลอันดามัน เดินลงหาดได้ทันที พร้อมวิวพระอาทิตย์ขึ้นและบรรยากาศเงียบสงบของเกาะลิบง',
      faqs: [
        {
          q: 'ที่พักอยู่ห่างจากชายหาดเท่าไร?',
          a: 'เดินถึงหาดบ้านพร้าวได้ในระยะประมาณ 50 เมตร จากตัวที่พัก',
        },
        {
          q: 'มีที่จอดรถหรือไม่?',
          a: 'มีที่จอดรถส่วนตัวให้บริการฟรีสำหรับผู้เข้าพัก',
        },
        {
          q: 'มีอาหารเช้าไหม?',
          a: 'มีบริการอาหารเช้า สามารถสอบถามรายละเอียดและจองเพิ่มได้ที่เคาน์เตอร์',
        },
        {
          q: 'เช็คอิน–เช็คเอาต์กี่โมง?',
          a: 'เช็คอินตั้งแต่ 14:00–22:00 และเช็คเอาต์ก่อน 12:00 น.',
        },
      ],
      rooms: [
        {
          name: 'ห้องดีลักซ์วิวทะเล',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 12,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'ห้องแฟมิลี่ 4 ท่าน',
          bed: 'เตียงใหญ่ 1 เตียงและเตียงเดี่ยว 2 เตียง',
          photos: 9,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'ห้องสุพีเรียร์ติดหาด',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 10,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
      ],
    },
    {
      name: 'หลังเขา โฮมสเตย์',
      opened: 'เปิดบริการปี 2015',
      openedYear: '2015',
      roomCount: '12 ห้อง',
      phone: '075-234-567',
      address: 'บ้านบาตูปูเต๊ะ เกาะลิบง อำเภอกันตัง จังหวัดตรัง 92110',
      mapUrl: 'https://maps.google.com/?q=Koh+Libong+Homestay',
      score: '8.6',
      scoreLabel: 'ดีมาก',
      reviewCount: '128',
      scoreBreakdown: [
        { label: 'ความสะอาด', value: 8.5 },
        { label: 'สิ่งอำนวยความสะดวก', value: 8.2 },
        { label: 'ทำเลที่ตั้ง', value: 8.8 },
        { label: 'บริการ', value: 9.0 },
      ],
      reviewSnippets: [
        'โฮมสเตย์อบอุ่นแบบวิถีชุมชน อาหารพื้นบ้านอร่อย',
        'จักรยานขี่ชมวิวได้ เงียบสงบเหมาะพักผ่อน',
      ],
      guestReviews: [
        {
          name: 'คุณฝน',
          tripType: 'เดี่ยว',
          text: 'บรรยากาศบ้าน ๆ เจ้าของใจดีมาก ได้คุยกับคนในชุมชนและชิมอาหารพื้นบ้านจริง ๆ',
          date: 'มี.ค. 2026',
        },
        {
          name: 'คุณกอล์ฟ',
          tripType: 'เพื่อน',
          text: 'จักรยานฟรีสะดวกมาก จุดชมวิวหลังเขาสวย ห้องเรียบง่ายแต่สะอาด',
          date: 'ก.พ. 2026',
        },
        {
          name: 'คุณพิมพ์',
          tripType: 'ครอบครัว',
          text: 'เหมาะพาเด็กมาสัมผัสวิถีชาวเล อาหารมื้อเย็นอร่อย ราคาคุ้มค่า',
          date: 'ธ.ค. 2025',
        },
      ],
      highlights: [
        { icon: 'map-pin', label: 'ใกล้ชุมชนท้องถิ่น' },
        { icon: 'wifi', label: 'Wi-Fi ฟรี' },
        { icon: 'bike', label: 'จักรยานฟรี' },
        { icon: 'eye', label: 'จุดชมวิว' },
      ],
      surroundings: [
        { icon: 'landmark', category: 'สถานที่สำคัญ', name: 'มัสยิดชุมชนบาตู', dist: '350 ม.' },
        { icon: 'shopping-bag', category: 'ช้อปปิ้ง', name: 'ร้านค้าชุมชน', dist: '500 ม.' },
        { icon: 'mountain', category: 'ท่องเที่ยว', name: 'จุดชมวิวหลังเขา', dist: '1.0 กม.' },
        { icon: 'waves', category: 'ชายหาด', name: 'ชายหาดใกล้บ้าน', dist: '1.5 กม.' },
        { icon: 'utensils', category: 'ร้านอาหาร', name: 'ครัวบ้านพื้นเมือง', dist: '200 ม.' },
        { icon: 'trees', category: 'กิจกรรม', name: 'เส้นทางปั่นจักรยาน', dist: '100 ม.' },
      ],
      facilities: [
        { icon: 'wifi', label: 'Wi-Fi ในพื้นที่ส่วนกลาง' },
        { icon: 'bike', label: 'จักรยานให้เช่า — ฟรี' },
        { icon: 'cigarette-off', label: 'ปลอดบุหรี่' },
        { icon: 'utensils', label: 'อาหารพื้นบ้าน' },
        { icon: 'eye', label: 'จุดชมวิวบนเนิน' },
        { icon: 'users', label: 'ต้อนรับแบบครอบครัว' },
      ],
      facilityGroups: [
        {
          title: 'อินเทอร์เน็ต',
          items: ['Wi-Fi ฟรีในห้องพัก', 'Wi-Fi ในพื้นที่ส่วนกลาง'],
        },
        {
          title: 'ที่จอดรถ',
          items: ['ที่จอดรถหน้าบ้าน — ฟรี'],
        },
        {
          title: 'พื้นที่ส่วนกลาง',
          items: ['ระเบียงบ้าน', 'มุมรับประทานอาหารรวม', 'สวนเล็ก ๆ'],
        },
        {
          title: 'ความปลอดภัย',
          items: ['พื้นที่ปิดล้อม', 'เจ้าของอยู่ประจำ'],
        },
        {
          title: 'บริการ',
          items: ['อาหารพื้นบ้าน', 'จักรยานฟรี', 'แนะนำเส้นทางท่องเที่ยว'],
        },
        {
          title: 'ห้องพัก',
          items: ['ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ', 'ห้ามสูบบุหรี่'],
        },
      ],
      policies: {
        checkIn: '13:00 – 21:00',
        checkOut: 'ก่อน 11:00',
        children: 'ยินดีต้อนรับเด็ก (แนะนำแจ้งล่วงหน้าหากต้องการเตียงเสริม)',
        breakfast: 'อาหารเช้าแบบบ้าน ๆ รวมในบางแพ็กเกจ',
        pets: 'สอบถามเจ้าของล่วงหน้า (กรณีพิเศษเท่านั้น)',
        deposit: 'อาจมีการเก็บมัดจำเล็กน้อยเมื่อเช็คอิน',
      },
      about:
        'หลังเขา โฮมสเตย์ ลิบง เป็นที่พักวิถีชุมชนในบ้านบาตูปูเต๊ะ เน้นสัมผัสชีวิตชาวเลอย่างใกล้ชิด มีจักรยานให้ขี่ชมวิว และอาหารพื้นบ้านรสชาติดั้งเดิม เหมาะกับนักเดินทางที่อยากพักแบบเรียบง่ายและอบอุ่น',
      faqs: [
        {
          q: 'มีจักรยานให้ใช้หรือไม่?',
          a: 'มีจักรยานให้ผู้เข้าพักใช้ฟรี เพื่อปั่นชมวิวรอบชุมชนและจุดชมวิวหลังเขา',
        },
        {
          q: 'ที่พักเหมาะกับครอบครัวไหม?',
          a: 'เหมาะมาก โดยเฉพาะผู้ที่อยากให้เด็กได้สัมผัสวิถีชุมชนและธรรมชาติของเกาะลิบง',
        },
        {
          q: 'ไกลจากท่าเรือไหม?',
          a: 'อยู่ในชุมชนบาตู สามารถเดินทางต่อจากท่าเรือด้วยรถหรือมอเตอร์ไซค์รับจ้างได้สะดวก',
        },
        {
          q: 'มีอาหารให้บริการหรือไม่?',
          a: 'มีอาหารพื้นบ้านตามมื้อ สามารถแจ้งความต้องการอาหารพิเศษล่วงหน้าได้',
        },
      ],
      rooms: [
        {
          name: 'ห้องมาตรฐานวิวเนินเขา',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 8,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'ห้องครอบครัว',
          bed: 'เตียงใหญ่ 1 เตียงและเตียงเดี่ยว 1 เตียง',
          photos: 6,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'ห้องมุมสวน',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 7,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
      ],
    },
    {
      name: 'ดูหยง ซีวิว บังกะโล',
      opened: 'เปิดบริการปี 2020',
      openedYear: '2020',
      roomCount: '18 หลัง',
      phone: '075-345-678',
      address: 'แหลมจุโหย เกาะลิบง อำเภอกันตัง จังหวัดตรัง 92110',
      mapUrl: 'https://maps.google.com/?q=Laem+Juhoi+Koh+Libong',
      score: '9.1',
      scoreLabel: 'ยอดเยี่ยม',
      reviewCount: '176',
      scoreBreakdown: [
        { label: 'ความสะอาด', value: 9.2 },
        { label: 'สิ่งอำนวยความสะดวก', value: 9.0 },
        { label: 'ทำเลที่ตั้ง', value: 9.4 },
        { label: 'บริการ', value: 8.9 },
      ],
      reviewSnippets: [
        'บังกะโลวิวทะเลพาโนรามา พระอาทิตย์ตกสวยมาก',
        'สระว่ายน้ำสะอาด เหมาะฮันนีมูนและครอบครัว',
      ],
      guestReviews: [
        {
          name: 'คุณบีม',
          tripType: 'คู่รัก',
          text: 'ระเบียงบังกะโลเห็นทะเลชัดเจน พระอาทิตย์ตกที่แหลมจุโหยสวยจนอยากกลับมาอีก',
          date: 'มี.ค. 2026',
        },
        {
          name: 'คุณสายฝน',
          tripType: 'ครอบครัว',
          text: 'สระว่ายน้ำสะอาด เด็ก ๆ ชอบมาก ห้องกว้างและวิวดีเกินคาด',
          date: 'ก.พ. 2026',
        },
        {
          name: 'คุณต้น',
          tripType: 'เพื่อน',
          text: 'ทำเลดีสำหรับถ่ายรูป ใกล้จุดชมพระอาทิตย์ตก พนักงานช่วยแนะนำเส้นทางดี',
          date: 'ม.ค. 2026',
        },
      ],
      highlights: [
        { icon: 'map-pin', label: 'วิวทะเลพาโนรามา' },
        { icon: 'wifi', label: 'Wi-Fi ฟรีในห้อง' },
        { icon: 'droplets', label: 'สระว่ายน้ำ' },
        { icon: 'waves', label: 'ติดทะเล' },
      ],
      surroundings: [
        { icon: 'mountain', category: 'ท่องเที่ยว', name: 'แหลมจุโหยจุดชมพระอาทิตย์ตก', dist: '200 ม.' },
        { icon: 'waves', category: 'ธรรมชาติ', name: 'หญ้าทะเลพะยูน', dist: '1.8 กม.' },
        { icon: 'shopping-bag', category: 'ช้อปปิ้ง', name: 'ร้านของฝากท้องถิ่น', dist: '3.2 กม.' },
        { icon: 'landmark', category: 'การเดินทาง', name: 'ท่าเรือเกาะลิบง', dist: '5.0 กม.' },
        { icon: 'utensils', category: 'ร้านอาหาร', name: 'ร้านอาหารวิวทะเล', dist: '900 ม.' },
        { icon: 'trees', category: 'กิจกรรม', name: 'เส้นทางเดินชายฝั่ง', dist: '100 ม.' },
      ],
      facilities: [
        { icon: 'droplets', label: 'สระว่ายน้ำกลางแจ้ง' },
        { icon: 'wifi', label: 'Wi-Fi ในพื้นที่ส่วนกลาง' },
        { icon: 'car', label: 'ที่จอดรถ — ฟรี' },
        { icon: 'cigarette-off', label: 'ปลอดบุหรี่' },
        { icon: 'waves', label: 'ระเบียงวิวทะเล' },
        { icon: 'shield', label: 'เจ้าหน้าที่รักษาความปลอดภัย' },
        { icon: 'video', label: 'กล้องวงจรปิด' },
      ],
      facilityGroups: [
        {
          title: 'อินเทอร์เน็ต',
          items: ['Wi-Fi ฟรีในบังกะโล', 'Wi-Fi ในพื้นที่ส่วนกลาง'],
        },
        {
          title: 'ที่จอดรถ',
          items: ['ที่จอดรถ — ฟรี'],
        },
        {
          title: 'พื้นที่ส่วนกลาง',
          items: ['สระว่ายน้ำกลางแจ้ง', 'ระเบียงชมวิว', 'ลานพักผ่อนริมทะเล'],
        },
        {
          title: 'ความปลอดภัย',
          items: ['กล้องวงจรปิด', 'เจ้าหน้าที่รักษาความปลอดภัย'],
        },
        {
          title: 'บริการ',
          items: ['บริการต้อนรับ', 'แนะนำจุดชมพระอาทิตย์ตก', 'ผ้าเช็ดตัวสระว่ายน้ำ'],
        },
        {
          title: 'ห้องพัก',
          items: ['ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ', 'ห้ามสูบบุหรี่', 'ระเบียงวิวทะเล'],
        },
      ],
      policies: {
        checkIn: '14:00 – 21:00',
        checkOut: 'ก่อน 12:00',
        children: 'ยินดีต้อนรับเด็ก (สระว่ายน้ำควรมีผู้ใหญ่ดูแล)',
        breakfast: 'สามารถสั่งอาหารเช้าเพิ่มได้ตามเมนูของที่พัก',
        pets: 'ไม่อนุญาตให้นำสัตว์เลี้ยงเข้าพัก',
        deposit: 'เก็บมัดจำเมื่อเช็คอิน และคืนเมื่อเช็คเอาต์หากไม่มีค่าเสียหาย',
      },
      about:
        'ดูหยง ซีวิว บังกะโล ตั้งใกล้แหลมจุโหย จุดชมพระอาทิตย์ตกชื่อดังของเกาะลิบง มีบังกะโลวิวทะเลพาโนรามาและสระว่ายน้ำกลางแจ้ง เหมาะกับคู่รัก ครอบครัว และผู้ที่อยากได้วิวทะเลแบบเต็มตา',
      faqs: [
        {
          q: 'เห็นพระอาทิตย์ตกจากที่พักได้ไหม?',
          a: 'หลายหลังเห็นวิวทะเลได้จากระเบียง และเดินไปจุดชมพระอาทิตย์ตกแหลมจุโหยได้ในระยะใกล้',
        },
        {
          q: 'มีสระว่ายน้ำหรือไม่?',
          a: 'มีสระว่ายน้ำกลางแจ้งสำหรับผู้เข้าพัก',
        },
        {
          q: 'ห้องพักเป็นแบบไหน?',
          a: 'เป็นบังกะโลแยกหลัง มีระเบียงส่วนตัว ห้องน้ำในตัว และวิวทะเลในหลายยูนิต',
        },
        {
          q: 'เหมาะกับฮันนีมูนไหม?',
          a: 'เหมาะมาก ด้วยวิวทะเล บรรยากาศโรแมนติก และตำแหน่งใกล้จุดชมพระอาทิตย์ตก',
        },
      ],
      rooms: [
        {
          name: 'บังกะโลซีวิว',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 14,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'บังกะโลพรีเมียมวิวทะเล',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 11,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'บังกะโลครอบครัว',
          bed: 'เตียงใหญ่ 1 เตียงและเตียงเดี่ยว 1 เตียง',
          photos: 9,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
      ],
    },
    {
      name: 'เลตรัง รีสอร์ท',
      opened: 'เปิดบริการปี 2016',
      openedYear: '2016',
      roomCount: '36 ห้อง',
      phone: '075-456-789',
      address: 'หาดทุ่งหญ้าคา เกาะลิบง อำเภอกันตัง จังหวัดตรัง 92110',
      mapUrl: 'https://maps.google.com/?q=Koh+Libong+Resort+Spa',
      score: '9.3',
      scoreLabel: 'ยอดเยี่ยม',
      reviewCount: '302',
      scoreBreakdown: [
        { label: 'ความสะอาด', value: 9.4 },
        { label: 'สิ่งอำนวยความสะดวก', value: 9.5 },
        { label: 'ทำเลที่ตั้ง', value: 9.1 },
        { label: 'บริการ', value: 9.3 },
      ],
      reviewSnippets: [
        'รีสอร์ทพรีเมียม สปาดีมาก สระสวย',
        'อาหารเช้าครบ บริการประทับใจ คุ้มค่าวันหยุดพิเศษ',
      ],
      guestReviews: [
        {
          name: 'คุณแอน',
          tripType: 'คู่รัก',
          text: 'สปานวดแผนไทยผ่อนคลายมาก ห้องพักสะอาดหรู และสระว่ายน้ำบรรยากาศดี',
          date: 'มี.ค. 2026',
        },
        {
          name: 'คุณพีท',
          tripType: 'ครอบครัว',
          text: 'บริการประทับใจตลอดทริป อาหารเช้าบุฟเฟ่ต์หลากหลาย เด็ก ๆ ชอบสระมาก',
          date: 'ก.พ. 2026',
        },
        {
          name: 'คุณเจน',
          tripType: 'ธุรกิจ',
          text: 'เงียบสงบเหมาะพักผ่อนหลังงาน Wi-Fi เสถียร และพนักงานตอบสนองรวดเร็ว',
          date: 'ม.ค. 2026',
        },
      ],
      highlights: [
        { icon: 'map-pin', label: 'ทำเลเงียบสงบ' },
        { icon: 'wifi', label: 'Wi-Fi ฟรีในห้อง' },
        { icon: 'sparkles', label: 'สปาครบครัน' },
        { icon: 'droplets', label: 'สระว่ายน้ำ' },
      ],
      surroundings: [
        { icon: 'waves', category: 'ชายหาด', name: 'หาดทุ่งหญ้าคา', dist: '120 ม.' },
        { icon: 'shopping-bag', category: 'ช้อปปิ้ง', name: 'ร้านสะดวกซื้อบนเกาะ', dist: '2.4 กม.' },
        { icon: 'landmark', category: 'การเดินทาง', name: 'ท่าเรือบ้านบาตู', dist: '3.8 กม.' },
        { icon: 'mountain', category: 'ท่องเที่ยว', name: 'จุดชมวิวอ่าวลิบง', dist: '1.6 กม.' },
        { icon: 'utensils', category: 'ร้านอาหาร', name: 'ร้านอาหารในรีสอร์ท', dist: 'ในที่พัก' },
        { icon: 'trees', category: 'กิจกรรม', name: 'เส้นทางเดินชายหาด', dist: '80 ม.' },
      ],
      facilities: [
        { icon: 'sparkles', label: 'สปาและนวดแผนไทย' },
        { icon: 'droplets', label: 'สระว่ายน้ำกลางแจ้ง' },
        { icon: 'coffee', label: 'อาหารเช้าบุฟเฟ่ต์' },
        { icon: 'wifi', label: 'Wi-Fi ในพื้นที่ส่วนกลาง' },
        { icon: 'car', label: 'ที่จอดรถส่วนตัว — ฟรี' },
        { icon: 'cigarette-off', label: 'ปลอดบุหรี่' },
        { icon: 'key-round', label: 'เข้าห้องด้วยคีย์การ์ด' },
        { icon: 'shield', label: 'เจ้าหน้าที่รักษาความปลอดภัย 24 ชม.' },
      ],
      facilityGroups: [
        {
          title: 'อินเทอร์เน็ต',
          items: ['Wi-Fi ฟรีในห้องพัก', 'Wi-Fi ความเร็วสูงในพื้นที่ส่วนกลาง'],
        },
        {
          title: 'ที่จอดรถ',
          items: ['ที่จอดรถส่วนตัว — ฟรี', 'ที่จอดรถมีหลังคา'],
        },
        {
          title: 'พื้นที่ส่วนกลาง',
          items: ['สระว่ายน้ำกลางแจ้ง', 'ล็อบบี้', 'ร้านอาหารในรีสอร์ท', 'สวนพักผ่อน'],
        },
        {
          title: 'ความปลอดภัย',
          items: ['เจ้าหน้าที่รักษาความปลอดภัย 24 ชม.', 'กล้องวงจรปิด', 'เข้าห้องด้วยคีย์การ์ด'],
        },
        {
          title: 'บริการ',
          items: ['สปาและนวดแผนไทย', 'อาหารเช้าบุฟเฟ่ต์', 'รูมเซอร์วิส', 'บริการคอนเซียร์จ'],
        },
        {
          title: 'ห้องพัก',
          items: ['ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ', 'ห้ามสูบบุหรี่', 'ชุดเครื่องนอนพรีเมียม'],
        },
      ],
      policies: {
        checkIn: '15:00 – 23:00',
        checkOut: 'ก่อน 12:00',
        children: 'ยินดีต้อนรับเด็กและมีเตียงเสริมให้บริการ (อาจมีค่าใช้จ่ายเพิ่ม)',
        breakfast: 'อาหารเช้าบุฟเฟ่ต์ รวมในหลายแพ็กเกจห้องพัก',
        pets: 'ไม่อนุญาตให้นำสัตว์เลี้ยงเข้าพัก',
        deposit: 'เก็บมัดจำด้วยบัตรเครดิตหรือเงินสดเมื่อเช็คอิน',
      },
      about:
        'เลตรัง รีสอร์ท แอนด์ สปา เป็นที่พักระดับพรีเมียมบนเกาะลิบง ใกล้หาดทุ่งหญ้าคา มีสปา สระว่ายน้ำ และอาหารเช้าบุฟเฟ่ต์ครบครัน เหมาะสำหรับวันหยุดพิเศษ ฮันนีมูน และการพักผ่อนแบบครบวงจร',
      faqs: [
        {
          q: 'มีสปาในที่พักหรือไม่?',
          a: 'มีสปาและบริการนวดแผนไทย สามารถจองคิวล่วงหน้าหรือที่หน้าเคาน์เตอร์ได้',
        },
        {
          q: 'อาหารเช้าเป็นแบบไหน?',
          a: 'เป็นอาหารเช้าบุฟเฟ่ต์ มีทั้งเมนูไทยและนานาชาติในหลายแพ็กเกจ',
        },
        {
          q: 'ที่พักเงียบสงบไหม?',
          a: 'ตั้งอยู่ในทำเลเงียบสงบใกล้หาดทุ่งหญ้าคา เหมาะกับการพักผ่อน',
        },
        {
          q: 'มีที่จอดรถหรือไม่?',
          a: 'มีที่จอดรถส่วนตัวฟรี และบางส่วนมีหลังคา',
        },
      ],
      rooms: [
        {
          name: 'ห้องดีลักซ์สามคน',
          bed: 'เตียงใหญ่ 1 เตียงและเตียงเดี่ยว 1 เตียง',
          photos: 13,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'ห้องสุพีเรียร์พรีเมียร์',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 8,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
        {
          name: 'ห้องสวีทวิวสวน',
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: 15,
          amenities: ['Wi-Fi ฟรี', 'ห้ามสูบบุหรี่', 'ห้องน้ำส่วนตัว', 'ตู้เย็น', 'ฝักบัวอาบน้ำ'],
        },
      ],
    },
  ];

  function buildMemberHotelDetail(item) {
    const amenities = (item.amenities && item.amenities.length)
      ? item.amenities
      : ['จากสมาชิกเกาะลิบง.com'];
    const phone = item.phone || '';
    const line = item.line_id || '';
    const year = String(item.date || '').slice(0, 4) || String(new Date().getFullYear());
    const address = item.location || 'เกาะลิบง จังหวัดตรัง';
    const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
    const contactBits = [];
    if (phone) contactBits.push(`โทร ${phone}`);
    if (line) contactBits.push(`LINE ${line}`);
    if (item.author) contactBits.push(`โดย ${item.author}`);

    return {
      isMember: true,
      name: item.title,
      opened: 'ลงประกาศโดยสมาชิก',
      openedYear: year,
      roomCount: 'สอบถามผู้ประกอบการ',
      phone: phone || (line ? `LINE ${line}` : 'ติดต่อผ่านหน้าติดต่อเว็บไซต์'),
      address,
      mapUrl,
      score: '',
      scoreLabel: '',
      reviewCount: '0',
      scoreBreakdown: [],
      reviewSnippets: [],
      guestReviews: [],
      highlights: amenities.slice(0, 4).map((label) => ({
        icon: amenityIcon(label),
        label,
      })),
      surroundings: [],
      facilities: amenities.map((label) => ({
        icon: amenityIcon(label),
        label,
      })),
      facilityGroups: [
        {
          title: 'ข้อมูลจากสมาชิก',
          items: amenities,
        },
        ...(contactBits.length
          ? [{ title: 'ติดต่อ', items: contactBits }]
          : []),
      ],
      policies: {},
      about: item.description || item.text || 'รายละเอียดเพิ่มเติมสอบถามผู้ประกอบการโดยตรง',
      faqs: [],
      rooms: [
        {
          name: item.title || 'ห้องพัก / บริการ',
          bed: item.price ? `ราคา ${item.price}` : 'สอบถามราคา',
          photos: (item.gallery || item._gallery || []).length || 1,
          amenities,
        },
      ],
    };
  }

  function isMemberHotel(item) {
    if (!item) return false;
    if (item.source === 'member') return true;
    const id = String(item.id || item._key || '');
    return id.startsWith('live-hotel-');
  }

  function getDetail(item) {
    if (isMemberHotel(item)) {
      return buildMemberHotelDetail(item);
    }
    const idx = Number(item._index) || 0;
    return HOTEL_DETAILS[idx % HOTEL_DETAILS.length];
  }

  function getGallery(item) {
    const imgs = [...(item._gallery || item.gallery || [])].filter(Boolean);
    if (!imgs.length && (item._image || item.image)) imgs.push(item._image || item.image);
    return imgs;
  }

  function galleryHtml(item) {
    const imgs = getGallery(item);
    const total = Math.max(imgs.length, 1);
    const display = [...imgs];
    while (display.length < 5) display.push(display[display.length - 1] || '');
    const main = display[0];
    const side = display.slice(1, 5);
    const title = item.title || '';
    const sideHtml = side
      .map((src, i) => {
        const isLast = i === side.length - 1;
        return `<button type="button" class="hd-gallery__cell${isLast ? ' hd-gallery__cell--more' : ''}" data-hd-gallery-index="${i + 1}" aria-label="ดูรูปที่ ${i + 2}">
        <img src="${esc(src)}" alt="" loading="lazy" decoding="async">
        ${isLast ? `<span class="hd-gallery__more">${lucide('images', 'icon')}<span>ดูรูปทั้งหมด ${total} รูป</span></span>` : ''}
      </button>`;
      })
      .join('');

    return `<div class="hd-gallery" data-hd-images="${esc(JSON.stringify(imgs))}" data-hd-title="${esc(title)}">
      <button type="button" class="hd-gallery__main" data-hd-gallery-index="0" aria-label="ดูรูปที่ 1">
        <img src="${esc(main)}" alt="${esc(title)}" loading="lazy" decoding="async">
      </button>
      <div class="hd-gallery__side">${sideHtml}</div>
    </div>`;
  }

  function listTwoCol(items, cls) {
    return `<ul class="${cls}">${items
      .map((it) => {
        let text = it.label || '';
        if (it.name) {
          text = it.category
            ? `${it.category}: ${it.name} (${it.dist || ''})`
            : `${it.name} (${it.dist || ''})`;
        }
        return `
      <li class="${cls}-item">
        <span class="${cls}-icon">${lucide(it.icon || 'check')}</span>
        <span class="${cls}-text">${esc(text)}</span>
      </li>`;
      })
      .join('')}</ul>`;
  }

  function roomCardHtml(room, hotel, contact, roomIndex = 0) {
    const gallery = hotel._gallery || hotel.gallery || [];
    const img =
      room.image ||
      gallery[roomIndex % Math.max(gallery.length, 1)] ||
      gallery[0] ||
      hotel._image ||
      hotel.image ||
      '';
    const amenities = (room.amenities || hotel.amenities || []).slice(0, 5);
    const amenityHtml = amenities
      .map(
        (a) => `
      <span class="hd-room__amenity">${lucide(amenityIcon(a))} ${esc(a)}</span>`
      )
      .join('');
    const href = contact || 'contact.html';
    return `<article class="hd-room">
      <div class="hd-room__media">
        <img src="${esc(img)}" alt="${esc(room.name)}" loading="lazy" decoding="async">
        <span class="hd-room__photos">${lucide('camera', 'icon')} ${esc(String(room.photos || 8))}</span>
      </div>
      <div class="hd-room__body">
        <h4 class="hd-room__title">${esc(room.name)}</h4>
        <p class="hd-room__bed">${lucide('bed-double', 'icon')} ${esc(room.bed || 'เตียงใหญ่ 1 เตียง')}</p>
        <div class="hd-room__amenities">${amenityHtml}</div>
        <a class="hd-room__detail" href="${esc(href)}">รายละเอียดห้องพัก</a>
      </div>
      <div class="hd-room__action">
        <a class="hd-room__btn" href="${esc(href)}">ดูห้องว่าง</a>
      </div>
    </article>`;
  }

  function reviewsSectionHtml(detail) {
    const bars = (detail.scoreBreakdown || [])
      .map((b) => {
        const pct = Math.max(0, Math.min(100, (Number(b.value) / 10) * 100));
        return `<div class="hd-review-bar">
          <div class="hd-review-bar__label">${esc(b.label)}</div>
          <div class="hd-review-bar__track"><span class="hd-review-bar__fill" style="width:${pct}%"></span></div>
          <div class="hd-review-bar__value">${esc(String(b.value))}</div>
        </div>`;
      })
      .join('');

    const cards = (detail.guestReviews || [])
      .slice(0, 3)
      .map(
        (r) => `<article class="hd-guest-review">
        <div class="hd-guest-review__head">
          <strong class="hd-guest-review__name">${esc(r.name)}</strong>
          <span class="hd-guest-review__type">${esc(r.tripType)}</span>
        </div>
        <p class="hd-guest-review__text">${esc(r.text)}</p>
        <time class="hd-guest-review__date">${esc(r.date)}</time>
      </article>`
      )
      .join('');

    return `<section class="hd-section hd-section--reviews">
      <h3 class="hd-section__title">รีวิวจากผู้เข้าพัก</h3>
      <div class="hd-reviews-summary">
        <div class="hd-reviews-summary__score">
          <div class="hd-reviews-summary__stars" aria-hidden="true">${starRow(5)}</div>
          <strong>${esc(detail.score)}</strong>
          <span>/10</span>
          <div class="hd-rating__label">${esc(detail.scoreLabel)}</div>
          <div class="hd-rating__count">${esc(detail.reviewCount)} รีวิว</div>
        </div>
        <div class="hd-review-bars">${bars}</div>
      </div>
      <div class="hd-guest-reviews">${cards}</div>
    </section>`;
  }

  function facilityGroupsHtml(detail) {
    const groups = (detail.facilityGroups || [])
      .map((g) => {
        const items = (g.items || [])
          .map(
            (label) => `<li class="hd-fac-group__item">
            ${lucide(amenityIcon(label))} <span>${esc(label)}</span>
          </li>`
          )
          .join('');
        return `<div class="hd-fac-group">
          <h4 class="hd-fac-group__title">${esc(g.title)}</h4>
          <ul class="hd-fac-group__list">${items}</ul>
        </div>`;
      })
      .join('');

    return `<section class="hd-section" id="hd-facilities">
      <h3 class="hd-section__title">บริการและสิ่งอำนวยความสะดวก</h3>
      <div class="hd-fac-groups">${groups}</div>
    </section>`;
  }

  function policiesHtml(detail) {
    const p = detail.policies || {};
    return `<section class="hd-section hd-section--policies">
      <h3 class="hd-section__title">นโยบายที่พัก</h3>
      <dl class="hd-policies">
        <div class="hd-policies__row">
          <dt>${lucide('clock', 'icon')} เช็คอิน</dt>
          <dd>${esc(p.checkIn || '-')}</dd>
        </div>
        <div class="hd-policies__row">
          <dt>${lucide('clock', 'icon')} เช็คเอาต์</dt>
          <dd>${esc(p.checkOut || '-')}</dd>
        </div>
        <div class="hd-policies__row">
          <dt>${lucide('baby', 'icon')} เด็กและเตียงเสริม</dt>
          <dd>${esc(p.children || '-')}</dd>
        </div>
        <div class="hd-policies__row">
          <dt>${lucide('coffee', 'icon')} อาหารเช้า</dt>
          <dd>${esc(p.breakfast || '-')}</dd>
        </div>
        <div class="hd-policies__row">
          <dt>${lucide('paw-print', 'icon')} สัตว์เลี้ยง</dt>
          <dd>${esc(p.pets || '-')}</dd>
        </div>
        <div class="hd-policies__row">
          <dt>${lucide('key-round', 'icon')} มัดจำ</dt>
          <dd>${esc(p.deposit || '-')}</dd>
        </div>
      </dl>
    </section>`;
  }

  function aboutHtml(detail, item) {
    return `<section class="hd-section hd-section--about">
      <h3 class="hd-section__title">รายละเอียดที่พัก</h3>
      <p class="hd-about__text">${esc(detail.about || item.desc || item.description || '')}</p>
      <ul class="hd-about__meta">
        <li>${lucide('bed-double', 'icon')} จำนวนห้อง: ${esc(detail.roomCount || '-')}</li>
        <li>${lucide('clock', 'icon')} เปิดบริการปี ${esc(detail.openedYear || '-')}</li>
        <li>${lucide('phone', 'icon')} โทร: ${esc(detail.phone || '-')}</li>
      </ul>
    </section>`;
  }

  function faqsHtml(detail) {
    const items = (detail.faqs || [])
      .map(
        (f) => `<details class="hd-faq">
        <summary class="hd-faq__q">${lucide('circle-help', 'icon')} ${esc(f.q)}</summary>
        <p class="hd-faq__a">${esc(f.a)}</p>
      </details>`
      )
      .join('');
    if (!items) return '';
    return `<section class="hd-section hd-section--faq">
      <h3 class="hd-section__title">คำถามที่พบบ่อย</h3>
      <div class="hd-faq-list">${items}</div>
    </section>`;
  }

  function recommendHotelsHtml(related, contact, articleUrlFn) {
    if (!related?.length) return '';
    const cards = related
      .map((h) => {
        const detail = getDetail(h);
        const room = detail.rooms?.[0] || {
          name: h.title,
          bed: 'เตียงใหญ่ 1 เตียง',
          photos: (h._gallery || h.gallery || []).length || 8,
          amenities: h.amenities || [],
        };
        const img = h._image || h.image || (h._gallery && h._gallery[0]) || '';
        const amenities = (room.amenities || h.amenities || []).slice(0, 5);
        const amenityHtml = amenities
          .map(
            (a) => `
        <span class="hd-room__amenity">${lucide(amenityIcon(a))} ${esc(a)}</span>`
          )
          .join('');
        const url =
          typeof articleUrlFn === 'function'
            ? articleUrlFn('hotel', h._key, h.title || '')
            : `article.html?section=hotel&id=${encodeURIComponent(h._key)}&title=${encodeURIComponent(h.title || '')}`;
        return `<article class="hd-room">
        <a class="hd-room__media" href="${esc(url)}">
          <img src="${esc(img)}" alt="${esc(h.title)}" loading="lazy" decoding="async">
          <span class="hd-room__photos">${lucide('camera', 'icon')} ${esc(String(room.photos || 8))}</span>
        </a>
        <div class="hd-room__body">
          <h4 class="hd-room__title"><a href="${esc(url)}">${esc(h.title)}</a></h4>
          <p class="hd-room__bed">${lucide('map-pin', 'icon')} ${esc(h.location || '')}</p>
          <div class="hd-room__amenities">${amenityHtml}</div>
          <a class="hd-room__detail" href="${esc(url)}">รายละเอียดที่พัก</a>
        </div>
        <div class="hd-room__action">
          <a class="hd-room__btn" href="${esc(contact || url)}">ดูห้องว่าง</a>
          ${h.price ? `<span class="hd-room__price">เริ่ม ฿${esc(h.price)}</span>` : ''}
        </div>
      </article>`;
      })
      .join('');

    return `<section class="hd-section hd-section--rooms">
      <h3 class="hd-section__title">แนะนำที่พักใกล้เคียง</h3>
      <div class="hd-room-list">${cards}</div>
    </section>`;
  }

  function buildHotelDetailHtml(item, opts = {}) {
    const detail = getDetail(item);
    const contact = opts.contactUrl || 'contact.html';
    const related = opts.relatedHotels || [];
    const railHtml = opts.railHtml || '';
    const articleUrlFn = opts.articleUrl;
    const isMember = !!detail.isMember;

    const highlightHtml = (detail.highlights || []).length
      ? `<div class="hd-highlights">${(detail.highlights || [])
          .map(
            (h) => `
      <div class="hd-highlight">
        <span class="hd-highlight__icon">${lucide(h.icon)}</span>
        <span class="hd-highlight__label">${esc(h.label)}</span>
      </div>`
          )
          .join('')}</div>`
      : '';

    const roomsHtml = (detail.rooms || [])
      .map((room, i) => roomCardHtml(room, item, contact, i))
      .join('');
    const quickFacilities = (detail.facilities || []).slice(0, 8);

    const surroundingsHtml = (detail.surroundings || []).length
      ? `<section class="hd-section">
          <h3 class="hd-section__title">บริเวณโดยรอบ</h3>
          ${listTwoCol(detail.surroundings || [], 'hd-poi')}
          <a class="hd-section__link" href="${esc(detail.mapUrl || '#')}" target="_blank" rel="noopener noreferrer">ดูบนแผนที่</a>
        </section>`
      : '';

    const facilitiesQuickHtml = quickFacilities.length
      ? `<section class="hd-section">
          <h3 class="hd-section__title">สิ่งอำนวยความสะดวก</h3>
          ${listTwoCol(quickFacilities, 'hd-fac')}
          <a class="hd-section__link" href="#hd-facilities">สิ่งอำนวยความสะดวกครบครัน</a>
        </section>`
      : '';

    const reviewsHtml = !isMember && (detail.guestReviews || []).length
      ? reviewsSectionHtml(detail)
      : '';
    const policiesBlock = !isMember ? policiesHtml(detail) : '';
    const faqsBlock = faqsHtml(detail);

    const main = `
      <article class="hd-main">
        <header class="hd-head">
          <div class="hd-head__left">
            <div class="hd-head__title-row">
              <h1 class="hd-head__title" id="contentModalTitle">${esc(item.title)}</h1>
              ${starRow(item.stars)}
              ${detail.opened ? `<span class="hd-head__badge">${esc(detail.opened)}</span>` : ''}
            </div>
            <p class="hd-head__addr">
              ${lucide('map-pin', 'icon')}
              <span>${esc(detail.address || item.location || '')}</span>
              <a class="hd-head__map" href="${esc(detail.mapUrl || '#')}" target="_blank" rel="noopener noreferrer">แสดงบนแผนที่</a>
            </p>
          </div>
          <div class="hd-head__actions">
            <button type="button" class="hd-head__btn" id="hdSaveBtn">${lucide('heart', 'icon')} บันทึก</button>
            <button type="button" class="hd-head__btn" id="hdShareBtn">${lucide('share-2', 'icon')} แชร์</button>
          </div>
        </header>

        ${galleryHtml(item)}
        ${highlightHtml}

        ${surroundingsHtml}
        ${facilitiesQuickHtml}

        <section class="hd-section hd-section--rooms">
          <h3 class="hd-section__title">${isMember ? 'รายละเอียดบริการ' : 'เลือกห้องพักของคุณ'}</h3>
          <div class="hd-room-list">${roomsHtml}</div>
        </section>

        ${reviewsHtml}
        ${facilityGroupsHtml(detail)}
        ${policiesBlock}
        ${aboutHtml(detail, item)}
        ${faqsBlock}
        ${recommendHotelsHtml(related, contact, articleUrlFn)}
      </article>`;

    return `<div class="content-modal__layout hotel-detail-layout">${main}${railHtml}</div>`;
  }

  function ensureGalleryLightbox() {
    let lb = document.getElementById('hdGalleryLightbox');
    if (lb) return lb;
    lb = document.createElement('div');
    lb.className = 'gallery-lightbox';
    lb.id = 'hdGalleryLightbox';
    lb.hidden = true;
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.setAttribute('aria-label', 'ดูรูปภาพที่พัก');
    lb.innerHTML = `
      <button type="button" class="gallery-lightbox__close" aria-label="ปิด">${lucide('x', 'icon')}</button>
      <button type="button" class="gallery-lightbox__prev" aria-label="ก่อนหน้า">${lucide('chevron-left', 'icon')}</button>
      <figure class="gallery-lightbox__figure">
        <img class="gallery-lightbox__img" src="" alt="" />
        <figcaption class="gallery-lightbox__caption"></figcaption>
      </figure>
      <button type="button" class="gallery-lightbox__next" aria-label="ถัดไป">${lucide('chevron-right', 'icon')}</button>`;
    document.body.appendChild(lb);
    refreshLucide(lb);
    return lb;
  }

  function initHotelGalleryLightbox(root) {
    const gallery = root.querySelector('.hd-gallery');
    if (!gallery) return;

    let images = [];
    try {
      images = JSON.parse(gallery.dataset.hdImages || '[]');
    } catch {
      images = [];
    }
    if (!images.length) {
      images = [...gallery.querySelectorAll('img')]
        .map((img) => img.getAttribute('src'))
        .filter(Boolean);
    }
    if (!images.length) return;

    const title = gallery.dataset.hdTitle || '';
    const lb = ensureGalleryLightbox();
    const lbImg = lb.querySelector('.gallery-lightbox__img');
    const lbCaption = lb.querySelector('.gallery-lightbox__caption');
    let index = 0;

    const show = (i) => {
      index = ((i % images.length) + images.length) % images.length;
      lbImg.src = images[index];
      lbImg.alt = title ? `${title} — รูปที่ ${index + 1}` : `รูปที่ ${index + 1}`;
      lbCaption.textContent = `${index + 1} / ${images.length}${title ? ` · ${title}` : ''}`;
    };

    const open = (i) => {
      show(i);
      lb.hidden = false;
      document.body.style.overflow = 'hidden';
      lb.querySelector('.gallery-lightbox__close')?.focus();
    };

    const close = () => {
      lb.hidden = true;
      document.body.style.overflow = '';
      lbImg.removeAttribute('src');
    };

    const next = () => show(index + 1);
    const prev = () => show(index - 1);

    gallery.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-hd-gallery-index]');
      if (!btn || !gallery.contains(btn)) return;
      const isMore = btn.classList.contains('hd-gallery__cell--more');
      const i = Number(btn.getAttribute('data-hd-gallery-index') || 0);
      open(isMore ? 0 : Number.isFinite(i) ? i : 0);
    });

    lb.querySelector('.gallery-lightbox__close')?.addEventListener('click', close);
    lb.querySelector('.gallery-lightbox__next')?.addEventListener('click', next);
    lb.querySelector('.gallery-lightbox__prev')?.addEventListener('click', prev);
    lb.addEventListener('click', (e) => {
      if (e.target === lb) close();
    });

    document.addEventListener('keydown', (e) => {
      if (lb.hidden) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowRight') next();
      if (e.key === 'ArrowLeft') prev();
    });
  }

  function initHotelDetailPage() {
    const root = document.querySelector('.hotel-detail-layout');
    if (!root) return;

    document.getElementById('hdShareBtn')?.addEventListener('click', async () => {
      try {
        if (navigator.share) {
          await navigator.share({ title: document.title, url: location.href });
          return;
        }
      } catch {
        /* ignore */
      }
      try {
        await navigator.clipboard.writeText(location.href);
        const btn = document.getElementById('hdShareBtn');
        if (btn) {
          const old = btn.innerHTML;
          btn.textContent = 'คัดลอกลิงก์แล้ว';
          setTimeout(() => {
            btn.innerHTML = old;
            refreshLucide(btn);
          }, 1600);
        }
      } catch {
        /* ignore */
      }
    });

    document.getElementById('hdSaveBtn')?.addEventListener('click', () => {
      const btn = document.getElementById('hdSaveBtn');
      if (!btn) return;
      btn.classList.toggle('is-saved');
      btn.innerHTML = btn.classList.contains('is-saved')
        ? `${lucide('heart', 'icon')} บันทึกแล้ว`
        : `${lucide('heart', 'icon')} บันทึก`;
      refreshLucide(btn);
    });

    initHotelGalleryLightbox(root);
    refreshLucide(root);
  }

  window.KL_HOTEL_DETAIL = {
    buildHotelDetailHtml,
    initHotelDetailPage,
  };
})();
