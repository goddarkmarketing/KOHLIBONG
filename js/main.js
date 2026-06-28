/* ============================================================
   เกาะลิบง.com — interactions + content rendering
   ============================================================ */

function initLucide() {
  if (!window.lucide?.createIcons) return;
  lucide.createIcons({
    attrs: { 'stroke-width': 2 },
  });
}

const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => [...c.querySelectorAll(s)];

/* รูปภาพจาก https://xn--72c1af2cbv3ee4v.com/ (Talay Trang) + Pexels */
const TT = 'https://xn--72c1af2cbv3ee4v.com';
const W = { card: 640, thumb: 180, hero: 1400, gallery: 600, mini: 420, avatar: 120, van: 640, footer: 1200 };
const px = (id, w = W.card) =>
  `https://images.pexels.com/photos/${id}/pexels-photo-${id}.jpeg?auto=compress&cs=tinysrgb&w=${w}`;
const upload = (file) => `${TT}/assets/uploads/${file}`;
const thumb = (url) => (url.includes('pexels.com') ? url.replace(/w=\d+/, `w=${W.thumb}`) : url);

const IMG = {
  hero: [
    upload('20260602-131052-5ab9ec8c.png'),
    upload('20260602-131107-bcb7fc4b.png'),
    upload('20260602-131058-7e606846.png'),
    px(14573822, W.hero),
    px(1647064, W.hero),
  ],
  activities: [
    px(6691933, W.card),
    px(36405819, W.card),
    px(1032650, W.card),
    px(2037926, W.card),
    px(15763636, W.card),
    px(4171737, W.card),
    px(100582, W.card),
    px(271624, W.card),
  ],
  tours: [
    upload('20260612-141642-be55380f.png'),
    upload('20260612-143741-9af064af.png'),
    px(14573822, W.card),
    px(1450363, W.card),
    px(15763636, W.card),
    px(1647064, W.card),
    px(36405819, W.card),
    px(17942107, W.card),
  ],
  boats: [
    upload('20260602-181538-54158826.png'),
    upload('20260602-181649-b834b3a1.png'),
    upload('20260602-181740-ef2b0dd3.png'),
    px(14573822, W.card),
  ],
  hotelGalleries: [
    [px(261102, W.gallery), px(29974430, W.gallery), px(1450363, W.gallery), px(13419316, W.gallery), px(31029704, W.gallery), px(271624, W.gallery), px(5740342, W.gallery), px(18297054, W.gallery)],
    [px(271624, W.gallery), px(1457842, W.gallery), px(2037926, W.gallery), px(1267320, W.gallery), px(31029704, W.gallery), px(1591376, W.gallery), px(6698714, W.gallery), px(457881, W.gallery)],
    [px(1450363, W.gallery), px(2486168, W.gallery), px(1032650, W.gallery), px(271624, W.gallery), px(13419316, W.gallery), px(29974430, W.gallery), px(18297054, W.gallery), px(28581876, W.gallery)],
    [px(5740342, W.gallery), px(338504, W.gallery), px(261181, W.gallery), px(261102, W.gallery), px(31029704, W.gallery), px(1287146, W.gallery), px(14573822, W.gallery), px(457881, W.gallery)],
  ],
  miniSlides: [
    px(271624, W.mini),
    px(261102, W.mini),
    px(1032650, W.mini),
    px(2037926, W.mini),
    px(29974430, W.mini),
    px(1591376, W.mini),
    px(5740342, W.mini),
    px(1450363, W.mini),
  ],
  reviews: [
    px(6698714, W.avatar),
    px(237741, W.avatar),
    px(90427, W.avatar),
    px(1450363, W.avatar),
    px(31029704, W.avatar),
    px(13680386, W.avatar),
    px(6173672, W.avatar),
    px(769289, W.avatar),
  ],
  restaurants: [
    px(566566, W.card),
    px(2673353, W.card),
    px(302899, W.card),
    px(566345, W.card),
    px(1438671, W.card),
    px(2098085, W.card),
    px(725991, W.card),
    px(1581384, W.card),
  ],
  van: px(15804640, W.van),
  footer: px(14573822, W.footer),
};

function loadBg(el) {
  const url = el.dataset.bg;
  if (!url || el.dataset.loaded) return;
  el.style.backgroundImage = `url('${url}')`;
  el.dataset.loaded = '1';
}

function preloadImage(url) {
  const img = new Image();
  img.src = url;
}

  /* ---------- HERO CAROUSEL ---------- */
  (function heroCarousel() {
    const slides = $$('.hero__slide');
    const dotsWrap = $('#heroDots');
    if (!slides.length) return;

    let i = 0;
    let timer;

    function ensureSlideBg(n) {
      const slide = slides[n];
      if (slide) loadBg(slide);
    }

    slides.forEach((slide, idx) => {
      if (idx === 1) preloadImage(slide.dataset.bg);
      const b = document.createElement('button');
      b.setAttribute('aria-label', 'สไลด์ ' + (idx + 1));
      if (idx === 0) b.classList.add('is-active');
      b.addEventListener('click', () => go(idx));
      dotsWrap.appendChild(b);
    });
    const dots = $$('button', dotsWrap);

    function go(n) {
      slides[i].classList.remove('is-active');
      dots[i].classList.remove('is-active');
      i = (n + slides.length) % slides.length;
      ensureSlideBg(i);
      preloadImage(slides[(i + 1) % slides.length]?.dataset.bg);
      slides[i].classList.add('is-active');
      dots[i].classList.add('is-active');
      restart();
    }
    const next = () => go(i + 1);
    const prev = () => go(i - 1);
    function restart() {
      clearInterval(timer);
      timer = setInterval(next, 5000);
    }

    $('#heroNext').addEventListener('click', next);
    $('#heroPrev').addEventListener('click', prev);
    ensureSlideBg(0);
    restart();
  })();

  /* ---------- MOBILE NAV ---------- */
  (function nav() {
    const header = $('#nav');
    const toggle = $('#navToggle');
    const menu = $('#navMenu');
    if (!toggle || !menu) return;

    const setOpen = (open) => {
      header.classList.toggle('is-open', open);
      menu.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'ปิดเมนู' : 'เปิดเมนู');
      document.body.classList.toggle('nav-open', open);
    };

    toggle.addEventListener('click', () => setOpen(!header.classList.contains('is-open')));
    $$('a', menu).forEach((a) => a.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 768) setOpen(false);
    });
  })();

  /* ---------- TO TOP ---------- */
  (function toTop() {
    const btn = $('#toTop');
    window.addEventListener('scroll', () => {
      btn.classList.toggle('is-visible', window.scrollY > 600);
    });
  })();

  /* ---------- LAZY MAP + BACKGROUNDS ---------- */
  (function lazyEmbeds() {
    const map = $('#contactMap');
    if (map?.dataset.src) {
      const loadMap = () => {
        if (!map.src) map.src = map.dataset.src;
      };
      if ('IntersectionObserver' in window) {
        new IntersectionObserver((entries, io) => {
          if (entries[0].isIntersecting) {
            loadMap();
            io.disconnect();
          }
        }, { rootMargin: '240px' }).observe(map);
      } else {
        loadMap();
      }
    }

    $$('[data-bg]').forEach((el) => {
      if (el.classList.contains('hero__slide')) return;
      if ('IntersectionObserver' in window) {
        new IntersectionObserver((entries, io) => {
          if (entries[0].isIntersecting) {
            loadBg(el);
            io.disconnect();
          }
        }, { rootMargin: '200px' }).observe(el);
      } else {
        loadBg(el);
      }
    });
  })();

  /* ============================================================
     CONTENT DATA
     ============================================================ */
  const activities = [
    { t: 'ดูพะยูนกลางทะเลอันดามัน สัญลักษณ์ของเกาะลิบง', tag: 'ไฮไลต์', m: 'เที่ยวเกาะ · 03 มิ.ย. 2026 · 402 อ่าน' },
    { t: 'ล่องเรือชมป่าชายเลนและหญ้าทะเลผืนใหญ่', tag: 'ธรรมชาติ', m: 'เที่ยวเกาะ · 26 พ.ค. 2026 · 296 อ่าน' },
    { t: 'ชมพระอาทิตย์ตกที่แหลมจุโหย จุดชมวิวสุดโรแมนติก', tag: 'วิวสวย', m: 'เที่ยวเกาะ · 18 พ.ค. 2026 · 886 อ่าน' },
    { t: 'สัมผัสวิถีชุมชนมุสลิมและอาหารพื้นบ้านรสเด็ด', tag: 'วัฒนธรรม', m: 'เที่ยวเกาะ · 30 มิ.ย. 2026 · 530 อ่าน' },
    { t: 'ดำน้ำดูปะการังน้ำตื้นรอบเกาะ น้ำใสมองเห็นพื้นทราย', tag: 'ดำน้ำ', m: 'เที่ยวเกาะ · 04 มิ.ย. 2026 · 898 อ่าน' },
    { t: 'ตกหมึกยามค่ำคืน กิจกรรมสุดฮิตของนักท่องเที่ยว', tag: 'กิจกรรม', m: 'เที่ยวเกาะ · 02 มิ.ย. 2026 · 463 อ่าน' },
    { t: 'ปั่นจักรยานเที่ยวรอบเกาะ สูดอากาศบริสุทธิ์', tag: 'ผจญภัย', m: 'เที่ยวเกาะ · 29 พ.ค. 2026 · 529 อ่าน' },
    { t: 'พักโฮมสเตย์ริมทะเล สัมผัสชีวิตชาวเล', tag: 'ที่พัก', m: 'เที่ยวเกาะ · 29 พ.ค. 2026 · 2,190 อ่าน' },
  ];

  const tours = [
    { t: 'โปรแกรม 4 เกาะ ทะเลตรัง', route: 'เกาะมุก → ถ้ำมรกต → เกาะกระดาน → ...', badge: 'ขายดี', badgeType: 'left', rate: '4.9', count: '3.2พัน', old: '4,200', now: '3,500' },
    { t: 'โปรแกรมเกาะกระดาน ดำน้ำดูปะการัง', route: 'เกาะกระดาน → จุดดำน้ำ → ชมพระอาทิตย์', badge: 'ประหยัด 18%', badgeType: 'sale', rate: '4.8', count: '1.8พัน', old: '5,500', now: '4,500' },
    { t: 'โปรแกรมดำน้ำลึกเกาะลิบง', route: 'เกาะลิบง → เกาะม้า → เกาะแหวน', badge: 'ประหยัด 15%', badgeType: 'sale', rate: '4.9', count: '1.4พัน', old: '6,500', now: '5,500' },
    { t: 'โปรแกรมเกาะมุก / ถ้ำมรกต', route: 'เกาะมุก → ถ้ำมรกต → จุดดำน้ำ', badge: 'ไฮไลต์', badgeType: 'right', rate: '4.9', count: '2.5พัน', old: '3,800', now: '3,200' },
    { t: 'โปรแกรมชมพะยูนเกาะลิบง', route: 'แหลมจุโหย → หญ้าทะเล → ดูพะยูน', badge: 'ขายดี', badgeType: 'left', rate: '5.0', count: '980', old: '4,500', now: '3,900' },
    { t: 'โปรแกรมตกหมึกยามค่ำคืน', route: 'ท่าเรือบ้านพร้าว → จุดตกหมึก', badge: 'ประหยัด 20%', badgeType: 'sale', rate: '4.7', count: '640', old: '2,500', now: '1,990' },
    { t: 'โปรแกรมล่องเรือชมป่าชายเลน', route: 'คลองลิบง → ป่าโกงกาง → ชมนก', badge: 'ธรรมชาติ', badgeType: 'right', rate: '4.8', count: '410', old: '1,800', now: '1,500' },
    { t: 'โปรแกรมเหมาลำส่วนตัว 1 วัน', route: 'จัดเส้นทางได้เอง → เรือส่วนตัว', badge: 'พรีเมียม', badgeType: 'left', rate: '5.0', count: '255', old: '8,000', now: '6,900' },
  ];

  const boats = Array.from({ length: 4 }, (_, k) => ({
    t: 'จองตั๋วเรือไปเกาะลิบง ตกหมึก',
    desc: 'จองตั๋วเรือไปเกาะลิบง ตกหมึก น่าเที่ยว เรือออกทุกชั่วโมง บริการรับ-ส่งถึงท่าเรือ',
    price: '1,800',
  }));

  const hotels = [
    { t: 'ลิบง บีช รีสอร์ท', loc: 'หาดบ้านพร้าว, เกาะลิบง', stars: 4, am: ['Wi-Fi ฟรี', 'ติดทะเล', 'อาหารเช้า'], desc: 'ที่พักริมหาดบรรยากาศเงียบสงบ มองเห็นวิวทะเลอันดามัน เดินถึงชายหาดได้ทันที', price: '4,740' },
    { t: 'หลังเขา โฮมสเตย์ ลิบง', loc: 'บ้านบาตูปูเต๊ะ, เกาะลิบง', stars: 4, am: ['Wi-Fi ฟรี', 'จักรยานฟรี', 'จุดชมวิว'], desc: 'โฮมสเตย์วิถีชุมชน สัมผัสชีวิตชาวเลแท้ ๆ พร้อมอาหารพื้นบ้านรสเด็ดทุกมื้อ', price: '1,290' },
    { t: 'ดูหยง ซีวิว บังกะโล', loc: 'แหลมจุโหย, เกาะลิบง', stars: 5, am: ['Wi-Fi ฟรี', 'สระว่ายน้ำ', 'ติดทะเล'], desc: 'บังกะโลริมทะเลพร้อมระเบียงส่วนตัว ชมพระอาทิตย์ตกได้จากห้องพัก', price: '3,200' },
    { t: 'เลตรัง รีสอร์ท แอนด์ สปา', loc: 'หาดทุ่งหญ้าคา, เกาะลิบง', stars: 5, am: ['สปา', 'สระว่ายน้ำ', 'อาหารเช้า'], desc: 'รีสอร์ทระดับพรีเมียม พร้อมสปาและสระว่ายน้ำ บริการครบครันเพื่อการพักผ่อน', price: '5,900' },
  ];

  const hotelGalleries = {};

  function getHotelImages(urls, title) {
    return urls.map((src, i) => ({
      src,
      thumb: thumb(src),
      alt: `${title} — ภาพที่ ${i + 1}`,
    }));
  }

  function renderHotelGallery(id, urls, title) {
    const images = getHotelImages(urls, title);
    hotelGalleries[id] = images;

    const thumbs = images.map((im, i) => {
      const more = i === 7 ? ' hotel-card__thumb--more' : '';
      const label = i === 7 ? '<span class="hotel-card__thumb-label">ดูทั้งหมด</span>' : '';
      return `<button type="button" class="hotel-card__thumb${more}" data-gallery-id="${id}" data-index="${i}" aria-label="${i === 7 ? 'ดูทั้งหมด ' + title : im.alt}">
        <img src="${im.thumb}" alt="" loading="lazy" decoding="async">${label}
      </button>`;
    }).join('');

    return `
      <div class="hotel-card__gallery">
        <button type="button" class="hotel-card__hero" data-gallery-id="${id}" data-index="0" aria-label="ดูภาพ ${title}">
          <img src="${images[0].src}" alt="${title}" loading="lazy" decoding="async">
        </button>
        <div class="hotel-card__thumbs">${thumbs}</div>
      </div>`;
  }

  const reviews = Array.from({ length: 8 }, (_, k) => ({
    text: '“จองง่ายมากครับ ทักไลน์ตอบไว ไปถึงท่าเรือมีคนคอยรับ เรือสะอาด ปลอดภัย ลูก ๆ สนุกมาก ดำน้ำดูปะการังที่เกาะสวยจริง ขอจองอีกแน่นอนปีหน้า”',
    name: 'อเล็กซ์ 15 ท่าน',
    sub: 'พักบ่อคืนโฮมสเตย์',
  }));

  const restaurants = [
    { t: 'ครัวเล ลิบง ซีฟู้ด อาหารทะเลสดจากเรือประมงพื้นบ้าน', m: 'ร้านอาหาร · 03 มิ.ย. 2026 · 402 อ่าน' },
    { t: 'ร้านป้าแดง ข้าวยำปักษ์ใต้ น้ำบูดูสูตรต้นตำรับ', m: 'ร้านอาหาร · 26 พ.ค. 2026 · 296 อ่าน' },
    { t: 'ลานเล คาเฟ่ ริมทะเล กาแฟสดและของหวาน', m: 'ร้านอาหาร · 18 พ.ค. 2026 · 886 อ่าน' },
    { t: 'ครัวบ้านพร้าว ปูม้านึ่ง กุ้งเผา ราคาชาวบ้าน', m: 'ร้านอาหาร · 20 มิ.ย. 2026 · 4,567 อ่าน' },
    { t: 'ร้านลุงหมึก ปลาหมึกย่าง สดใหม่ทุกวัน', m: 'ร้านอาหาร · 04 มิ.ย. 2026 · 612 อ่าน' },
    { t: 'ครัวยายแป้น แกงส้มปลากะพง รสจัดจ้าน', m: 'ร้านอาหาร · 02 มิ.ย. 2026 · 380 อ่าน' },
    { t: 'ซีฟู้ดบ้านบาตู ออส่วน หอยนางรมสด', m: 'ร้านอาหาร · 29 พ.ค. 2026 · 529 อ่าน' },
    { t: 'ครัวลิบงวิว อาหารใต้ พร้อมวิวทะเลพาโนรามา', m: 'ร้านอาหาร · 29 พ.ค. 2026 · 2,190 อ่าน' },
  ];

  /* ============================================================
     RENDER
     ============================================================ */
  const starStr = (n) => {
    let html = '<span class="stars">';
    for (let i = 0; i < n; i++) {
      html += '<span class="star-icon star-icon--filled"><i data-lucide="star" class="icon"></i></span>';
    }
    for (let i = n; i < 5; i++) {
      html += '<span class="star-icon"><i data-lucide="star" class="icon"></i></span>';
    }
    return html + '</span>';
  };

  function render(target, html) {
    const el = $(target);
    if (el) el.innerHTML = html;
  }

  // 2. Activities (blog style)
  render('#activityGrid', activities.map((a, k) => `
    <article class="card reveal">
      <div class="card__media">
        <img src="${IMG.activities[k]}" alt="${a.t}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--left">${a.tag}</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${a.t}</h4>
        <p class="card__meta">${a.m}</p>
      </div>
    </article>`).join(''));

  // 3. Tours
  render('#tourGrid', tours.map((t, k) => `
    <article class="card reveal">
      <div class="card__media">
        <img src="${IMG.tours[k]}" alt="${t.t}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--left">ทะเลตรัง</span>
        <span class="card__badge card__badge--${t.badgeType}">${t.badge}</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${t.t}</h4>
        <p class="card__route">${t.route}</p>
        <div class="card__rating">${starStr(5)} <b>${t.rate}</b> /5 · ${t.count} รีวิว</div>
        <div class="card__price">
          <span class="card__price-old">THB ${t.old}</span>
          <span class="card__price-new">THB ${t.now}</span>
        </div>
      </div>
    </article>`).join(''));

  // 4. Boats
  render('#boatGrid', boats.map((b, k) => `
    <article class="card boat-card reveal">
      <div class="card__media">
        <img src="${IMG.boats[k]}" alt="${b.t}" loading="lazy" decoding="async">
      </div>
      <div class="card__body">
        <h4 class="card__title">${b.t}</h4>
        <p class="card__desc">${b.desc}</p>
        <div class="card__price"><span class="card__price-new">ราคา ${b.price} บาท/ต่อลำ</span></div>
      </div>
    </article>`).join(''));

  // 5. Hotels
  render('#hotelGrid', hotels.map((h, k) => `
    <article class="card hotel-card reveal">
      ${renderHotelGallery('hotel-' + k, IMG.hotelGalleries[k], h.t)}
      <div class="card__body">
        <h4 class="card__title">${h.t}</h4>
        <div class="card__rating">${starStr(h.stars)}</div>
        <p class="card__loc"><i data-lucide="map-pin" class="icon"></i> ${h.loc}</p>
        <div class="amenities">${h.am.map((a) => `<span>${a}</span>`).join('')}</div>
        <p class="card__desc">${h.desc}</p>
        <div class="card__price"><span class="card__price-new">฿ ${h.price}</span></div>
        <a href="#" class="btn btn--blue btn--sm">ดูที่พัก</a>
      </div>
    </article>`).join(''));

  const hotelMiniSlides = [
    { t: 'รีวิวโฮมสเตย์ริมทะเลเกาะลิบง บรรยากาศดี ราคาประหยัด', date: '03 มิ.ย. 2026', views: '402' },
    { t: '10 ที่พักเกาะลิบง วิวทะเลสวย ใกล้ชายหาด', date: '26 พ.ค. 2026', views: '296' },
    { t: 'พักบังกะโลเกาะลิบง ชมพระอาทิตย์ตกที่แหลมจุโหย', date: '18 พ.ค. 2026', views: '886' },
    { t: 'โฮมสเตย์วิถีชุมชนมุสลิม อาหารพื้นบ้านอร่อย', date: '20 มิ.ย. 2026', views: '567' },
    { t: 'รีสอร์ทริมหาดบ้านพร้าว เงียบสงบ เหมาะพักผ่อน', date: '04 มิ.ย. 2026', views: '612' },
    { t: 'ที่พักเกาะลิบง ใกล้ท่าเรือ เดินทางสะดวก', date: '02 มิ.ย. 2026', views: '380' },
    { t: 'รีวิวที่พักครอบครัวบนเกาะลิบง ปลอดภัย สะอาด', date: '29 พ.ค. 2026', views: '529' },
    { t: 'เช็กลิสต์จองที่พักเกาะลิบง ก่อนไปเที่ยว', date: '29 พ.ค. 2026', views: '219' },
  ];

  render('#hotelMiniTrack', hotelMiniSlides.map((s, k) => `
    <a href="#" class="mini-slider__card">
      <div class="mini-slider__media">
        <img src="${IMG.miniSlides[k]}" alt="${s.t}" loading="lazy" decoding="async">
        <span class="mini-slider__tag">ที่พัก</span>
      </div>
      <div class="mini-slider__card-body">
        <h4 class="mini-slider__card-title">${s.t}</h4>
        <div class="mini-slider__card-meta">
          <time>${s.date}</time>
          <span class="mini-slider__dot" aria-hidden="true"></span>
          <span>${s.views} อ่าน</span>
        </div>
      </div>
    </a>`).join(''));

  // 6. Reviews
  render('#reviewGrid', reviews.map((r, k) => `
    <article class="review-card reveal">
      ${starStr(5)}
      <p class="review-card__text">${r.text}</p>
      <img class="review-card__avatar" src="${IMG.reviews[k]}" alt="${r.name}" loading="lazy" decoding="async">
      <div class="review-card__name">${r.name}</div>
      <div class="review-card__sub">${r.sub}</div>
    </article>`).join(''));

  // 7. Restaurants
  render('#restaurantGrid', restaurants.map((r, k) => `
    <article class="card reveal">
      <div class="card__media">
        <img src="${IMG.restaurants[k]}" alt="${r.t}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--right">แนะนำ</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${r.t}</h4>
        <p class="card__meta">${r.m}</p>
      </div>
    </article>`).join(''));

  /* ---------- HOTEL MINI SLIDER ---------- */
  (function hotelMiniSlider() {
    const viewport = $('#hotelMiniViewport');
    const slider = $('#hotelMiniSliderWrap');
    if (!viewport || !slider) return;

    const step = () => Math.max(280, viewport.clientWidth * 0.72);

    $('.mini-slider__arrow--prev', slider)?.addEventListener('click', () => {
      viewport.scrollBy({ left: -step(), behavior: 'smooth' });
    });
    $('.mini-slider__arrow--next', slider)?.addEventListener('click', () => {
      viewport.scrollBy({ left: step(), behavior: 'smooth' });
    });
  })();

  /* ---------- HOTEL GALLERY LIGHTBOX ---------- */
  (function hotelGalleryLightbox() {
    const lb = $('#galleryLightbox');
    if (!lb) return;

    const lbImg = $('.gallery-lightbox__img', lb);
    const lbCaption = $('.gallery-lightbox__caption', lb);
    let currentId = null;
    let currentIndex = 0;

    function update() {
      const images = hotelGalleries[currentId];
      if (!images) return;
      const im = images[currentIndex];
      lbImg.src = im.src;
      lbImg.alt = im.alt;
      lbCaption.textContent = `${currentIndex + 1} / ${images.length} — ${im.alt}`;
    }

    function open(id, index) {
      if (!hotelGalleries[id]) return;
      currentId = id;
      currentIndex = index;
      update();
      lb.hidden = false;
      document.body.style.overflow = 'hidden';
    }

    function close() {
      lb.hidden = true;
      document.body.style.overflow = '';
    }

    function next() {
      const images = hotelGalleries[currentId];
      currentIndex = (currentIndex + 1) % images.length;
      update();
    }

    function prev() {
      const images = hotelGalleries[currentId];
      currentIndex = (currentIndex - 1 + images.length) % images.length;
      update();
    }

    $('#hotelGrid')?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-gallery-id]');
      if (!btn) return;
      e.preventDefault();
      open(btn.dataset.galleryId, Number(btn.dataset.index));
    });

    $('.gallery-lightbox__close', lb)?.addEventListener('click', close);
    $('.gallery-lightbox__next', lb)?.addEventListener('click', next);
    $('.gallery-lightbox__prev', lb)?.addEventListener('click', prev);
    lb.addEventListener('click', (e) => {
      if (e.target === lb) close();
    });
    document.addEventListener('keydown', (e) => {
      if (lb.hidden) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowRight') next();
      if (e.key === 'ArrowLeft') prev();
    });
  })();

  /* ---------- SCROLL REVEAL ---------- */
  (function reveal() {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add('is-in');
            io.unobserve(e.target);
          }
        });
      },
      { threshold: 0.12 }
    );
    $$('.reveal').forEach((el) => io.observe(el));
  })();

  initLucide();
