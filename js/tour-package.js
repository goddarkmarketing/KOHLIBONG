/* ============================================================
   เกาะลิบง.com — หน้ารายละเอียดแพ็กเกจทัวร์ (แบบ program.html)
   ============================================================ */

(function () {
  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function resolveSiteBase() {
    const raw = (document.querySelector('meta[name="site-base"]')?.content || '').trim();
    if (raw && raw !== 'auto') return raw.replace(/\/$/, '');
    const path = location.pathname || '/';
    if (path.startsWith('/เกาะลิบง.com')) return '/เกาะลิบง.com';
    if (path.startsWith('/KOHLIBONG')) return '/KOHLIBONG';
    return '';
  }

  const SITE_BASE = resolveSiteBase();

  function siteUrl(path) {
    const clean = String(path || '').replace(/^\//, '');
    return SITE_BASE ? `${SITE_BASE}/${clean}` : clean;
  }

  function parsePrice(val) {
    const n = Number(String(val ?? '').replace(/[^\d.]/g, ''));
    return Number.isFinite(n) ? n : 0;
  }

  /** Lucide icons — https://github.com/lucide-icons/lucide */
  function lucide(name, cls = 'icon') {
    return `<i data-lucide="${esc(name)}" class="${cls}" aria-hidden="true"></i>`;
  }

  function refreshLucide(root) {
    if (!window.lucide?.createIcons) return;
    const opts = { attrs: { 'stroke-width': 2 } };
    if (root) opts.root = root;
    window.lucide.createIcons(opts);
  }

  function infoHead(eyebrowIcon, eyebrow, title, mod = '') {
    const cls = mod ? ` pkg-info-head--${mod}` : '';
    return `<header class="pkg-info-head${cls}">
      <h3>${esc(title)}</h3>
      <span class="pkg-info-eyebrow"><span class="pkg-info-eyebrow__pill">${lucide(eyebrowIcon, 'icon pkg-info-eyebrow__icon')} ${esc(eyebrow)}</span></span>
    </header>`;
  }

  function formatThb(n) {
    const num = Number(n);
    if (!Number.isFinite(num) || num <= 0) return '';
    return new Intl.NumberFormat('th-TH').format(num);
  }

  function buildHeroHtml(item, pkg) {
    const badge = item.badge
      ? `<span class="pkg-hero__badge pkg-hero__badge--${esc(item.badge_type || 'default')}">${esc(item.badge)}</span>`
      : '';
    const rating = item.rating
      ? `<span class="pkg-hero__chip">${lucide('star', 'icon')} ${esc(item.rating)}/5 · ${esc(item.review_count || '0')} รีวิว</span>`
      : '';
    const duration = pkg.duration
      ? `<span class="pkg-hero__chip">${lucide('clock', 'icon')} ${esc(pkg.duration)}</span>`
      : '';
    const priceOldRaw = item.price_old || item.old;
    const priceOld = priceOldRaw
      ? `<span class="pkg-hero__price-old">฿${esc(String(priceOldRaw).replace(/[^\d,]/g, ''))}</span>`
      : '';
    const priceNow = pkg.basePrice
      ? `<strong class="pkg-hero__price-now">฿${formatThb(pkg.basePrice)}</strong>`
      : '';
    return `
      <header class="pkg-hero">
        <div class="pkg-hero__content">
          <p class="pkg-hero__eyebrow">${lucide('compass', 'icon')} แพ็กเกจทัวร์เกาะลิบง</p>
          <h1 class="pkg-hero__title">${esc(item.title || pkg.name)}</h1>
          ${pkg.route ? `<p class="pkg-hero__route">${lucide('map-pin', 'icon pkg-hero__route-icon')} ${esc(pkg.route)}</p>` : ''}
          <div class="pkg-hero__chips">${badge}${rating}${duration}</div>
        </div>
        <div class="pkg-hero__price-card">
          <span class="pkg-hero__price-label">ราคาเริ่มต้น</span>
          ${priceOld}
          ${priceNow}
          <span class="pkg-hero__price-unit">บาท / ท่าน</span>
        </div>
      </header>`;
  }

  const INCLUSION_MAP = {
    boat: { label: 'เรือนำเที่ยว', icon: 'ship' },
    food: { label: 'อาหารบุฟเฟ่ต์', icon: 'utensils' },
    guide: { label: 'พนักงานนำเที่ยว', icon: 'users' },
    snorkel: { label: 'อุปกรณ์ดำน้ำตื้น', icon: 'life-buoy' },
    park: { label: 'ค่าเข้าอุทยาน', icon: 'ticket' },
    insurance: { label: 'ประกันอุบัติเหตุ', icon: 'shield-check' },
  };

  const DEFAULT_INCLUSIONS = [
    'เรือนำเที่ยวทะเลตรัง',
    'อาหารกลางวัน / บุฟเฟ่ต์',
    'ไกด์ / สตาฟดูแล',
    'อุปกรณ์ดำน้ำ หน้ากาก เสื้อชูชีพ',
    'ค่าธรรมเนียมอุทยาน (คนไทย)',
    'น้ำดื่ม · น้ำแข็ง',
    'ประกันอุบัติเหตุ',
  ];

  const TOUR_PACKAGE_DETAILS = [
    {
      packageCode: 'KL-T001',
      licenseNo: '43/00540',
      duration: 'ประมาณ 8 ชั่วโมง',
      galleryCaption: 'เกาะมุก · ถ้ำมรกต · เกาะกระดาน · เกาะแหวน · เกาะเชือก',
      season: '1 ต.ค. – 31 พ.ค. · ออกเดินทางทุกวัน ตั้งแต่ 2 ท่านขึ้นไป',
      childPrice: 2900,
      priceMeetingNote: 'เจอกันที่ท่าเรือปากเมง · ราคาตามจำนวนท่าน',
      itinerary: [
        { time: '07:30', title: 'ท่าเรือปากเมง', text: 'ลูกค้าเจอกันที่ท่าเรือปากเมง อำเภอสิเกา ก่อนออกเดินทาง (ราคานี้เจอที่ท่าเรือ)' },
        { time: '09:30', title: 'ถ้ำมรกต', text: 'ลงเรือเดินทางสู่เกาะมุก สัมผัสความงามของถ้ำมรกต Unseen in Thailand' },
        { time: '11:30', title: 'เกาะกระดาน', text: 'เล่นน้ำ ถ่ายภาพ ชมบรรยากาศบนเกาะกระดาน พร้อมรับประทานอาหารเที่ยง' },
        { time: '13:30', title: 'อ่าวไผ่', text: 'ดำน้ำตื้นชมแนวปะการังที่สมบูรณ์ จุดถ่ายรูปใต้น้ำ' },
        { time: '15:30', title: 'เกาะแหวน · เกาะเชือก', text: 'ดำน้ำชมปะการังและฝูงปลานานาชนิด' },
        { time: '16:30', title: 'กลับท่าเรือ', text: 'เดินทางกลับท่าเรือปากเมง อาบน้ำเปลี่ยนเสื้อผ้า จบโปรแกรม' },
      ],
      inclusions: DEFAULT_INCLUSIONS,
      priceNotes: [
        'เด็กเล็ก 1–2.99 ปี ฟรี',
        'เด็ก 3–11.99 ปี ราคาพิเศษ',
        'ผู้ใหญ่ 12 ปีขึ้นไป ราคาตามแพ็กเกจ',
        'ไม่รวม: รถรับจากเมือง / สนามบิน',
        'ไม่รวม: ค่าธรรมเนียมชาวต่างชาติ',
      ],
      warning: 'โปรแกรมอาจเปลี่ยนแปลงตามสภาพอากาศ ระดับน้ำขึ้นลง และคลื่นลม แจ้งเลื่อนวันเที่ยวล่วงหน้า 7 วัน ไม่คืนเงินทุกกรณี',
    },
    {
      packageCode: 'KL-T002',
      licenseNo: '43/00540',
      duration: 'ประมาณ 7 ชั่วโมง',
      galleryCaption: 'เกาะกระดาน · จุดดำน้ำ · ชมพระอาทิตย์ตก',
      season: 'ออกเดินทางทุกวัน · แนะนำช่วง พ.ย. – เม.ย.',
      childPrice: 3300,
      itinerary: [
        { time: '08:00', title: 'ท่าเรือปากเมง', text: 'เช็กอินและเตรียมอุปกรณ์ดำน้ำ' },
        { time: '09:30', title: 'เกาะกระดาน', text: 'พักผ่อนหาดทรายขาว ว่ายน้ำเล่นน้ำทะเลใส' },
        { time: '11:30', title: 'จุดดำน้ำ', text: 'ดำน้ำตื้นชมปะการังหลากสีและฝูงปลา' },
        { time: '13:00', title: 'อาหารกลางวัน', text: 'รับประทานอาหารกลางวันบนเรือ' },
        { time: '15:00', title: 'จุดชมวิว', text: 'แวะจุดถ่ายรูปและชมวิวทะเลอันดามัน' },
        { time: '17:00', title: 'กลับท่าเรือ', text: 'เดินทางกลับท่าเรือปากเมง จบโปรแกรม' },
      ],
      inclusions: DEFAULT_INCLUSIONS,
      priceNotes: ['เด็ก 3–11.99 ปี ราคาพิเศษ', 'ไม่รวม: รถรับส่งในเมือง'],
      warning: 'โปรแกรมอาจปรับตามสภาพอากาศและกระแสน้ำ แจ้งล่วงหน้า 7 วันหากต้องการเลื่อนวันเดินทาง',
    },
    {
      packageCode: 'KL-T003',
      licenseNo: '43/00540',
      duration: 'ประมาณ 8 ชั่วโมง',
      galleryCaption: 'เกาะลิบง · เกาะม้า · เกาะแหวน · ดำน้ำลึก',
      season: 'ออกเดินทางทุกวัน · เหมาะสำหรับนักดำน้ำ',
      childPrice: 4100,
      itinerary: [
        { time: '07:30', title: 'ท่าเรือเกาะลิบง', text: 'พบกันที่ท่าเรือบ้านพร้าว เกาะลิบง' },
        { time: '09:00', title: 'เกาะม้า', text: 'ดำน้ำลึกชมแนวปะการังและฝูงปลาใหญ่' },
        { time: '11:30', title: 'เกาะแหวน', text: 'ดำน้ำต่อเนื่องที่จุดที่สองของทริป' },
        { time: '13:00', title: 'อาหารกลางวัน', text: 'รับประทานอาหารบนเรือ' },
        { time: '15:00', title: 'จุดดำน้ำพิเศษ', text: 'ดำน้ำลึกตามจุดที่ไกด์แนะนำ' },
        { time: '16:30', title: 'กลับท่าเรือ', text: 'เดินทางกลับเกาะลิบง จบโปรแกรม' },
      ],
      inclusions: [...DEFAULT_INCLUSIONS, 'อุปกรณ์ดำน้ำลึก (ตามเงื่อนไข)'],
      priceNotes: ['ต้องมีใบรับรองดำน้ำหรือประสบการณ์ดำน้ำ', 'ไม่รวม: ค่าเช่าอุปกรณ์เพิ่มเติม'],
      warning: 'โปรแกรมดำน้ำลึกขึ้นกับสภาพทะเลและประสบการณ์ของผู้ร่วมทริป',
    },
    {
      packageCode: 'KL-T004',
      licenseNo: '43/00540',
      duration: 'ประมาณ 7 ชั่วโมง',
      galleryCaption: 'เกาะมุก · ถ้ำมรกต · จุดดำน้ำ',
      season: 'ออกเดินทางทุกวัน · ไฮไลต์ถ้ำมรกต',
      childPrice: 2400,
      itinerary: [
        { time: '08:00', title: 'ท่าเรือปากเมง', text: 'เตรียมตัวออกเดินทางสู่เกาะมุก' },
        { time: '09:30', title: 'ถ้ำมรกต', text: 'ว่ายน้ำผ่านถ้ำยาวเข้าสู่หาดในถ้ำ ชม Unseen Thailand' },
        { time: '12:00', title: 'อาหารกลางวัน', text: 'รับประทานอาหารกลางวันบนเรือ' },
        { time: '13:30', title: 'จุดดำน้ำ', text: 'ดำน้ำตื้นชมปะการังรอบเกาะมุก' },
        { time: '15:30', title: 'ถ่ายรูปวิว', text: 'แวะจุดถ่ายรูปวิวทะเลสุดประทับใจ' },
        { time: '16:30', title: 'กลับท่าเรือ', text: 'เดินทางกลับท่าเรือปากเมง' },
      ],
      inclusions: DEFAULT_INCLUSIONS,
      priceNotes: ['เด็กเล็ก 1–2.99 ปี ฟรี', 'ไม่รวม: ค่าธรรมเนียมชาวต่างชาติ'],
      warning: 'การเข้าถ้ำมรกตขึ้นกับระดับน้ำและสภาพอากาศ',
    },
    {
      packageCode: 'KL-T005',
      licenseNo: '43/00540',
      duration: 'ประมาณ 5 ชั่วโมง',
      galleryCaption: 'แหลมจุโหย · หญ้าทะเล · ชมพะยูน',
      season: 'แนะนำช่วง พ.ย. – เม.ย. · ออกเดินทางตามรอบ',
      childPrice: 2900,
      itinerary: [
        { time: '08:30', title: 'แหลมจุโหย', text: 'เริ่มต้นที่แหลมจุโหย เกาะลิบง ฟังบรรยายจากไกด์ท้องถิ่น' },
        { time: '09:30', title: 'ป่าชายเลนหญ้าทะเล', text: 'เดินชมป่าชายเลนและทุ่งหญ้าทะเล' },
        { time: '11:00', title: 'จุดชมพะยูน', text: 'รอชมพะยูน (Dugong) ในธรรมชาติ เงียบสงบ ไม่รบกวนสัตว์' },
        { time: '12:30', title: 'อาหารกลางวัน', text: 'รับประทานอาหารพื้นบ้าน' },
        { time: '14:00', title: 'กลับจุดเริ่มต้น', text: 'เดินทางกลับและจบโปรแกรม' },
      ],
      inclusions: ['ไกด์ท้องถิ่น', 'อาหารกลางวัน', 'น้ำดื่ม', 'ประกันอุบัติเหตุ', 'ค่าเข้าพื้นที่อนุรักษ์'],
      priceNotes: ['โปรแกรมเน้นธรรมชาติ ห้ามรบกวนพะยูน', 'ไม่รวม: รถรับส่ง'],
      warning: 'การพบพะยูนขึ้นกับสภาพธรรมชาติ ไม่สามารถการันตีได้ 100%',
    },
    {
      packageCode: 'KL-T006',
      licenseNo: '43/00540',
      duration: 'ประมาณ 4 ชั่วโมง (ยามค่ำคืน)',
      galleryCaption: 'ท่าเรือบ้านพร้าว · จุดตกหมึก',
      season: 'ออกเรือยามเย็น · แนะนำช่วงมีนาคม – ตุลาคม',
      childPrice: 1500,
      itinerary: [
        { time: '17:30', title: 'ท่าเรือบ้านพร้าว', text: 'พบกันที่ท่าเรือ เกาะลิบง ก่อนออกเรือ' },
        { time: '18:30', title: 'ออกเรือ', text: 'เดินทางสู่จุดตกหมึกที่เหมาะสม' },
        { time: '19:30', title: 'เริ่มตกหมึก', text: 'ลงเบ็ดตกหมึกตามเทคนิคท้องถิ่น' },
        { time: '21:00', title: 'กลับท่าเรือ', text: 'เดินทางกลับท่าเรือบ้านพร้าว จบโปรแกรม' },
      ],
      inclusions: ['เรือและลูกเรือ', 'อุปกรณ์ตกหมึก', 'ไฟส่องสว่าง', 'น้ำดื่ม', 'ประกันอุบัติเหตุ'],
      priceNotes: ['เหมาะสำหรับผู้ใหญ่และเด็กโต', 'ไม่รวม: อาหาร'],
      warning: 'โปรแกรมขึ้นกับสภาพอากาศและคลื่นลม อาจเลื่อนออกเรือได้',
    },
    {
      packageCode: 'KL-T007',
      licenseNo: '43/00540',
      duration: 'ประมาณ 3 ชั่วโมง',
      galleryCaption: 'คลองลิบง · ป่าโกงกาง · ชมนก',
      season: 'ออกเดินทางทุกวัน · 2 รอบ เช้า / บ่าย',
      childPrice: 1100,
      itinerary: [
        { time: '09:00', title: 'คลองลิบง', text: 'ขึ้นเรือล่องคลองจากจุดนัดพบ' },
        { time: '09:45', title: 'ป่าชายเลน', text: 'ชมป่าโกงกางและระบบนิเวศชายเลน' },
        { time: '10:30', title: 'ชมนก', text: 'ดูนกหลากชนิดตามฤดูกาล' },
        { time: '11:30', title: 'กลับท่า', text: 'ล่องกลับจุดเริ่มต้น จบโปรแกรม' },
      ],
      inclusions: ['เรือล่องคลอง', 'ไกด์ท้องถิ่น', 'เสื้อชูชีพ', 'น้ำดื่ม', 'ประกันอุบัติเหตุ'],
      priceNotes: ['เหมาะทุกวัย', 'แนะนำนำหมวกและยากันยุง'],
      warning: 'โปรแกรมอาจปรับเส้นทางตามระดับน้ำในคลอง',
    },
    {
      packageCode: 'KL-T008',
      licenseNo: '43/00540',
      duration: 'เต็มวัน (กำหนดเอง)',
      galleryCaption: 'เรือส่วนตัว · จัดเส้นทางได้เอง · บริการ VIP',
      season: 'จองล่วงหน้า 3–7 วัน · รองรับ 2–15 ท่าน',
      childPrice: 5100,
      itinerary: [
        { time: '07:00', title: 'จุดนัดพบ', text: 'รับลูกค้าตามจุดที่ตกลง (ท่าเรือ / ที่พัก)' },
        { time: '09:00', title: 'ออกเรือ', text: 'เริ่มทริปตามเส้นทางที่ลูกค้าเลือก' },
        { time: '12:00', title: 'พักอาหาร', text: 'รับประทานอาหารตามแพ็กเกจที่เลือก' },
        { time: '15:00', title: 'กิจกรรมเพิ่มเติม', text: 'ดำน้ำ ตกปลา หรือแวะเกาะตามต้องการ' },
        { time: '17:00', title: 'กลับท่า', text: 'เดินทางกลับและจบโปรแกรม' },
      ],
      inclusions: ['เรือส่วนตัว', 'ลูกเรือและไกด์', 'อาหารและเครื่องดื่ม', 'อุปกรณ์ดำน้ำ', 'ประกันอุบัติเหตุ', 'ยืดหยุ่นเส้นทาง'],
      priceNotes: ['ราคาตามจำนวนท่านและเส้นทาง', 'ไม่รวม: ค่าธรรมเนียมพิเศษนอกโปรแกรม'],
      warning: 'กรุณาแจ้งจุดหมายและจำนวนผู้ร่วมทริปล่วงหน้าเพื่อจัดเรือที่เหมาะสม',
    },
  ];

  const LINE_URL = 'https://line.me/R/ti/p/@talaytrang';

  function resolveInclusion(item) {
    if (INCLUSION_MAP[item]) {
      const m = INCLUSION_MAP[item];
      return { label: m.label, icon: m.icon };
    }
    const text = String(item ?? '');
    const rules = [
      [/เรือ|ล่องเรือ/, 'ship'],
      [/อาหาร|บุฟเฟ|กาแฟ|ของว่าง|น้ำดื่ม|น้ำแข็ง/, 'utensils'],
      [/ไกด์|สตาฟ|มัคคุเทศ|พนักงาน/, 'users'],
      [/ดำน้ำ|หน้ากาก|เสื้อชู|อุปกรณ์/, 'life-buoy'],
      [/อุทยาน|ธรรมเนียม|พื้นที่/, 'ticket'],
      [/ประกัน/, 'shield-check'],
      [/น้ำดื่ม|น้ำแข็ง/, 'droplets'],
    ];
    for (const [re, icon] of rules) {
      if (re.test(text)) return { label: text, icon };
    }
    return { label: text, icon: 'info' };
  }

  function getTourDetail(item) {
    const idx = Number(item._index) || 0;
    const base = TOUR_PACKAGE_DETAILS[idx] || TOUR_PACKAGE_DETAILS[0];
    const adultPrice = parsePrice(item.price) || parsePrice(item.now);
    return {
      ...base,
      name: item.title || '',
      route: item.subtitle || base.galleryCaption || '',
      basePrice: adultPrice,
      childPrice: base.childPrice || Math.round(adultPrice * 0.74),
      packageCode: base.packageCode || `KL-T${String(idx + 1).padStart(3, '0')}`,
    };
  }

  function getGallery(item) {
    const imgs = [...(item._gallery || item.gallery || [])];
    if (!imgs.length && item._image) imgs.push(item._image);
    while (imgs.length < 6) imgs.push(imgs[imgs.length - 1] || item._image || '');
    return imgs.slice(0, 6);
  }

  function renderInclusionTile(item) {
    const inc = resolveInclusion(item);
    return `<li class="pkg-inc-tile">
      <span class="pkg-inc-icon">${lucide(inc.icon)}</span>
      <span class="pkg-inc-label">${esc(inc.label)}</span>
    </li>`;
  }

  function renderItinerarySteps(itinerary) {
    return itinerary.map((item, i) => {
      const showLine = i < itinerary.length - 1;
      const layer = (() => {
        const h = parseInt(String(item.time || '12:00').split(':')[0], 10);
        if (h < 9) return 1;
        if (h < 12) return 2;
        if (h < 15) return 3;
        return 4;
      })();
      return `
        <li class="pkg-step pkg-step--layer-${layer}">
          <div class="pkg-step-rail" aria-hidden="true">
            <span class="pkg-step-dot">${String(i + 1).padStart(2, '0')}</span>
            ${showLine ? '<span class="pkg-step-line"></span>' : ''}
          </div>
          <article class="pkg-step-card">
            <div class="pkg-step-meta"><time class="pkg-step-time">${esc(item.time)}</time></div>
            <h4 class="pkg-step-title">${lucide('map-pin', 'icon pkg-step-title__icon')} ${esc(item.title)}</h4>
            <p class="pkg-step-desc">${esc(item.text)}</p>
          </article>
        </li>`;
    }).join('');
  }

  function buildTourPackageHtml(item) {
    const pkg = getTourDetail(item);
    const gallery = getGallery(item);
    const caption = pkg.galleryCaption || pkg.route || item.subtitle || item.title;
    const code = `รหัส ${pkg.packageCode}`;
    const topRow = gallery.slice(0, 3).map((src) => `<div class="pkg-gallery-cell"><img src="${esc(src)}" alt="" loading="lazy" decoding="async"></div>`).join('');
    const botRow = gallery.slice(3, 6).map((src) => `<div class="pkg-gallery-cell"><img src="${esc(src)}" alt="" loading="lazy" decoding="async"></div>`).join('');
    const inclusions = (pkg.inclusions && pkg.inclusions.length) ? pkg.inclusions : DEFAULT_INCLUSIONS;
    const inclusionHtml = `<ul class="pkg-inc-grid">${inclusions.map(renderInclusionTile).join('')}</ul>`;
    const priceNotes = pkg.priceNotes || [];
    const noteHtml = priceNotes.map((n) => {
      const excluded = /ไม่รวม/.test(String(n).trim());
      return `<li class="pkg-note-item${excluded ? ' is-excluded' : ''}">
        <span class="${excluded ? 'pkg-note-exclude' : 'pkg-note-check'}">${excluded ? lucide('x') : lucide('check')}</span>
        <span>${esc(n)}</span>
      </li>`;
    }).join('');

    return `
      <div class="pkg-detail">
        <div class="container">
          <a href="${siteUrl('index.html#tours')}" class="pkg-back">
            ${lucide('arrow-left', 'icon pkg-back__icon')}
            กลับสู่โปรแกรมทั้งหมด
          </a>

          ${buildHeroHtml(item, pkg)}

          <div class="pkg-gallery-wrap">
            <div class="pkg-gallery">
              <div class="pkg-gallery-row">${topRow}</div>
              <div class="pkg-gallery-band"><span>${esc(caption)}</span></div>
              <div class="pkg-gallery-row">${botRow}</div>
            </div>
          </div>

          <div class="pkg-meta-bar">
            <span class="pkg-license">${lucide('badge-check', 'icon')} ใบอนุญาตนำเที่ยวที่ ${esc(pkg.licenseNo || '43/00540')}</span>
            <span class="pkg-code">${lucide('hash', 'icon pkg-code__icon')} ${esc(code)}</span>
          </div>

          <div class="pkg-body">
            <div class="pkg-col-itinerary">
              <div class="pkg-itinerary-panel pkg-panel">
                ${infoHead('route', 'ตารางเดินทาง', 'โปรแกรมทัวร์', 'ocean')}
                <ol class="pkg-steps">${renderItinerarySteps(pkg.itinerary || [])}</ol>
              </div>
              <div class="pkg-action">
                <section class="pkg-purchase pkg-panel" id="pkg-book">
                  ${infoHead('clipboard-list', 'พร้อมจองแล้ว', 'กรอกรายละเอียด', 'accent')}
                  <form id="pkg-book-form" class="pkg-book-form" novalidate>
                    <div class="pkg-book-date-row">
                      <div class="field" data-pkg-field="date">
                        <label for="pkg-book-date">${lucide('calendar', 'icon field-label__icon')} วันที่เริ่มเดินทาง <span class="req">*</span></label>
                        <input class="input" type="date" id="pkg-book-date" name="date" required>
                        <div class="field-error"></div>
                      </div>
                      <div class="field">
                        <label for="pkg-book-date-end">${lucide('calendar-check', 'icon field-label__icon')} วันสิ้นสุด</label>
                        <input class="input input-readonly" type="date" id="pkg-book-date-end" name="date_end" readonly tabindex="-1" aria-readonly="true">
                      </div>
                    </div>
                    <div class="pkg-book-qty-row">
                      <div class="field" data-pkg-field="adults">
                        <label>${lucide('users', 'icon field-label__icon')} ผู้ใหญ่ <span class="req">*</span> <span class="field-hint">12 ปีขึ้นไป</span></label>
                        <div class="qty-stepper">
                          <button type="button" class="qty-btn" data-qty="-" aria-label="ลดจำนวนผู้ใหญ่">${lucide('minus', 'icon')}</button>
                          <span class="qty-value">
                            <input class="qty-input" type="number" name="adults" value="2" min="1" max="80" inputmode="numeric" aria-label="จำนวนผู้ใหญ่">
                            <span class="qty-unit">ท่าน</span>
                          </span>
                          <button type="button" class="qty-btn" data-qty="+" aria-label="เพิ่มจำนวนผู้ใหญ่">${lucide('plus', 'icon')}</button>
                        </div>
                        <div class="field-error"></div>
                      </div>
                      <div class="field" data-pkg-field="children">
                        <label>${lucide('baby', 'icon field-label__icon')} เด็ก <span class="field-hint">3–11 ปี</span></label>
                        <div class="qty-stepper">
                          <button type="button" class="qty-btn" data-qty="-" aria-label="ลดจำนวนเด็ก">${lucide('minus', 'icon')}</button>
                          <span class="qty-value">
                            <input class="qty-input" type="number" name="children" value="0" min="0" max="80" inputmode="numeric" aria-label="จำนวนเด็ก">
                            <span class="qty-unit">ท่าน</span>
                          </span>
                          <button type="button" class="qty-btn" data-qty="+" aria-label="เพิ่มจำนวนเด็ก">${lucide('plus', 'icon')}</button>
                        </div>
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="field" data-pkg-field="name">
                      <label for="pkg-book-name">${lucide('user', 'icon field-label__icon')} ชื่อ-นามสกุล <span class="req">*</span></label>
                      <input class="input" type="text" id="pkg-book-name" name="name" placeholder="เช่น คุณสมชาย ใจดี" autocomplete="name" required>
                      <div class="field-error"></div>
                    </div>
                    <div class="field" data-pkg-field="phone">
                      <label for="pkg-book-phone">${lucide('phone', 'icon field-label__icon')} เบอร์โทร <span class="req">*</span></label>
                      <input class="input" type="tel" id="pkg-book-phone" name="phone" placeholder="08X-XXX-XXXX" autocomplete="tel" inputmode="tel" required>
                      <div class="field-error"></div>
                    </div>
                    <div class="field">
                      <label for="pkg-book-note">${lucide('message-square', 'icon field-label__icon')} หมายเหตุ <span class="field-hint">(ถ้ามี)</span></label>
                      <textarea class="textarea" id="pkg-book-note" name="note" rows="2" placeholder="เช่น ต้องการรับในเมือง, มีเด็กเล็ก ฯลฯ"></textarea>
                    </div>
                    <div class="pkg-book-total" aria-live="polite">
                      <div class="pkg-book-total-lines" id="pkg-book-lines"></div>
                      <div class="pkg-book-total-sum">
                        <span>${lucide('receipt', 'icon')} ยอดรวมโดยประมาณ</span>
                        <strong id="pkg-book-total">฿0</strong>
                      </div>
                    </div>
                    <div class="pkg-book-actions">
                      <button type="button" class="btn btn-line btn-lg" id="pkg-book-line">${lucide('message-circle', 'icon')} ส่งจองผ่าน LINE</button>
                      <button type="button" class="btn btn-outline btn-lg" id="pkg-book-copy">${lucide('copy', 'icon')} คัดลอกสรุปไว้ส่งเอง</button>
                    </div>
                    <p class="pkg-book-note">${lucide('info', 'icon pkg-book-note__icon')} * ยอดคำนวณจากราคาต่อท่าน · แอดมินยืนยันราคาสุดท้ายทาง LINE</p>
                  </form>
                </section>
              </div>
            </div>

            <div class="pkg-col-info">
              <aside class="pkg-info-panel">
                <section class="pkg-info-block pkg-info-block--season">
                  ${infoHead('calendar-days', 'ช่วงเวลา', 'พร้อมออกเดินทาง', 'season')}
                  <div class="pkg-season-card">
                    <span class="pkg-season-icon">${lucide('clock')}</span>
                    <p>${esc(pkg.season)}</p>
                  </div>
                </section>
                <section class="pkg-info-block pkg-info-block--inclusions">
                  ${infoHead('package', 'ครบในที่เดียว', 'สิ่งที่รวมในแพ็กเกจ', 'green')}
                  ${inclusionHtml}
                </section>
                <section class="pkg-info-block pkg-info-block--notes">
                  ${infoHead('file-text', 'เงื่อนไขราคา', 'หมายเหตุ', 'amber')}
                  <ul class="pkg-note-list">${noteHtml}</ul>
                </section>
              </aside>
            </div>
          </div>

          <div class="pkg-warning">
            <span class="pkg-warning-icon" aria-hidden="true">${lucide('alert-triangle')}</span>
            <p>${esc(pkg.warning)}</p>
          </div>
        </div>
      </div>`;
  }

  function initTourPackagePage(item) {
    const pkg = getTourDetail(item);
    const fmt = new Intl.NumberFormat('th-TH');
    const form = document.getElementById('pkg-book-form');
    if (!form) return;

    const dateInput = form.querySelector('input[name="date"]');
    const endDateInput = document.getElementById('pkg-book-date-end');
    if (dateInput) dateInput.min = new Date().toISOString().split('T')[0];

    function updateEndDate() {
      if (!endDateInput || !dateInput?.value) {
        if (endDateInput) endDateInput.value = dateInput?.value || '';
        return;
      }
      endDateInput.value = dateInput.value;
    }
    dateInput?.addEventListener('change', updateEndDate);
    dateInput?.addEventListener('input', updateEndDate);
    updateEndDate();

    form.querySelectorAll('.qty-stepper').forEach((stepper) => {
      stepper.addEventListener('click', (e) => {
        const btn = e.target.closest('.qty-btn');
        if (!btn) return;
        const input = stepper.querySelector('.qty-input');
        const dir = btn.dataset.qty === '+' ? 1 : -1;
        const min = Number(input.min) || 0;
        const max = Number(input.max) || 80;
        const cur = Number(input.value) || min;
        const next = Math.max(min, Math.min(max, cur + dir));
        if (next !== cur) {
          input.value = next;
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    });

    function setPkgError(field, msg) {
      const wrap = form.querySelector(`[data-pkg-field="${field}"]`);
      if (!wrap) return;
      wrap.classList.toggle('has-error', !!msg);
      const err = wrap.querySelector('.field-error');
      if (err) err.textContent = msg || '';
    }

    function getPkgState() {
      const fd = new FormData(form);
      const adults = Number(fd.get('adults') || 0);
      const children = Number(fd.get('children') || 0);
      return {
        name: String(fd.get('name') || '').trim(),
        phone: String(fd.get('phone') || '').trim(),
        date: String(fd.get('date') || ''),
        dateEnd: String(fd.get('date_end') || ''),
        adults,
        children,
        people: adults + children,
        note: String(fd.get('note') || '').trim(),
      };
    }

    function formatThaiDate(d) {
      if (!d) return '-';
      try {
        const dt = new Date(`${d}T12:00:00`);
        const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return `${dt.getDate()} ${months[dt.getMonth()]} ${dt.getFullYear() + 543}`;
      } catch {
        return d;
      }
    }

    function calcPkgTotal(state) {
      const adultPrice = pkg.basePrice;
      const childPriceUnit = pkg.childPrice;
      const adultTotal = state.adults * adultPrice;
      const childTotal = state.children * childPriceUnit;
      return {
        adultTotal,
        childTotal,
        total: adultTotal + childTotal,
        adultPrice,
        childPriceUnit,
        isBookOnly: adultPrice <= 0,
      };
    }

    function renderPkgTotal() {
      const state = getPkgState();
      const calc = calcPkgTotal(state);
      const lines = [];
      if (state.adults > 0) {
        const adultLabel = calc.isBookOnly
          ? `ผู้ใหญ่ × ${state.adults}`
          : `ผู้ใหญ่ ${fmt.format(calc.adultPrice)} × ${state.adults}`;
        const adultSum = calc.isBookOnly ? 'จอง' : `฿${fmt.format(calc.adultTotal)}`;
        lines.push(`<div class="pkg-book-price-line"><span>${adultLabel}</span><span>${adultSum}</span></div>`);
      }
      if (state.children > 0) {
        const childLabel = calc.isBookOnly
          ? `เด็ก × ${state.children}`
          : `เด็ก ${fmt.format(calc.childPriceUnit)} × ${state.children}`;
        const childSum = calc.isBookOnly ? 'จอง' : `฿${fmt.format(calc.childTotal)}`;
        lines.push(`<div class="pkg-book-price-line"><span>${childLabel}</span><span>${childSum}</span></div>`);
      }
      const linesEl = document.getElementById('pkg-book-lines');
      const totalEl = document.getElementById('pkg-book-total');
      if (linesEl) linesEl.innerHTML = lines.join('');
      if (totalEl) totalEl.textContent = calc.isBookOnly ? 'จอง' : (`฿${fmt.format(calc.total)}`);
      return { state, calc };
    }

    form.addEventListener('input', renderPkgTotal);
    form.addEventListener('change', renderPkgTotal);
    renderPkgTotal();

    function validatePkgForm(state) {
      let ok = true;
      if (!state.date) { setPkgError('date', 'กรุณาเลือกวันที่เดินทาง'); ok = false; }
      else setPkgError('date', '');
      if (!state.adults || state.adults < 1) { setPkgError('adults', 'ระบุผู้ใหญ่อย่างน้อย 1 ท่าน'); ok = false; }
      else setPkgError('adults', '');
      if (state.children < 0) { setPkgError('children', 'จำนวนเด็กไม่ถูกต้อง'); ok = false; }
      else setPkgError('children', '');
      if (!state.name) { setPkgError('name', 'กรุณากรอกชื่อ'); ok = false; }
      else setPkgError('name', '');
      const phoneClean = state.phone.replace(/\D/g, '');
      if (!state.phone || phoneClean.length < 9) {
        setPkgError('phone', state.phone ? 'เบอร์โทรไม่ถูกต้อง' : 'กรุณากรอกเบอร์โทร');
        ok = false;
      } else setPkgError('phone', '');
      return ok;
    }

    function buildPkgLineMessage(state, calc) {
      const L = [
        'สวัสดีครับ/ค่ะ สนใจจองแพ็กเกจทัวร์',
        '',
        '— แพ็กเกจ —',
        pkg.name,
        `รหัส ${pkg.packageCode}`,
        pkg.route ? `เส้นทาง: ${pkg.route}` : '',
        pkg.duration ? `ระยะเวลา: ${pkg.duration}` : '',
        '',
        '— ข้อมูลผู้จอง —',
        `ชื่อ: ${state.name}`,
        `เบอร์โทร: ${state.phone}`,
        `วันเริ่มเดินทาง: ${formatThaiDate(state.date)}`,
        `ผู้ใหญ่: ${state.adults} ท่าน`,
      ];
      if (state.children > 0) L.push(`เด็ก: ${state.children} ท่าน`);
      L.push(`รวม: ${state.people} ท่าน`);
      if (state.note) {
        L.push('');
        L.push(`หมายเหตุ: ${state.note}`);
      }
      L.push('');
      L.push('— สรุปราคา —');
      if (calc.isBookOnly) {
        L.push('>>> ราคา: จอง (แอดมินยืนยัน) <<<');
      } else {
        if (state.adults > 0) L.push(`ผู้ใหญ่: ฿${fmt.format(calc.adultPrice)} × ${state.adults} = ฿${fmt.format(calc.adultTotal)}`);
        if (state.children > 0) L.push(`เด็ก: ฿${fmt.format(calc.childPriceUnit)} × ${state.children} = ฿${fmt.format(calc.childTotal)}`);
        L.push(`รวม: ฿${fmt.format(calc.total)}`);
      }
      L.push('');
      L.push('— เกาะลิบง.com —');
      return L.filter((line, i, arr) => !(line === '' && arr[i + 1] === '')).join('\n');
    }

    document.getElementById('pkg-book-line')?.addEventListener('click', async () => {
      const { state, calc } = renderPkgTotal();
      if (!validatePkgForm(state)) return;
      const msg = buildPkgLineMessage(state, calc);
      try { await navigator.clipboard.writeText(msg); } catch { /* ignore */ }
      window.open(LINE_URL, '_blank', 'noopener');
    });

    document.getElementById('pkg-book-copy')?.addEventListener('click', async () => {
      const { state, calc } = renderPkgTotal();
      if (!validatePkgForm(state)) return;
      const text = buildPkgLineMessage(state, calc);
      try {
        await navigator.clipboard.writeText(text);
        const btn = document.getElementById('pkg-book-copy');
        if (btn) {
          const orig = btn.innerHTML;
          btn.textContent = 'คัดลอกแล้ว ✓';
          setTimeout(() => {
            btn.innerHTML = orig;
            refreshLucide(btn);
          }, 2000);
        }
      } catch {
        window.prompt('คัดลอกข้อความด้านล่าง:', text);
      }
    });

    if (location.hash === '#pkg-book') {
      setTimeout(() => {
        document.getElementById('pkg-book')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 120);
    }

    refreshLucide(document.querySelector('.pkg-detail'));
  }

  window.KL_TOUR_PKG = {
    buildTourPackageHtml,
    initTourPackagePage,
    getTourDetail,
  };
})();
