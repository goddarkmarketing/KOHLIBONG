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

function resolveSiteBase() {
  const raw = (document.querySelector('meta[name="site-base"]')?.content || '').trim();
  if (raw && raw !== 'auto') {
    return raw.replace(/\/$/, '');
  }
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

function escHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

let revealObserver;

function initReveal(root) {
  const targets = root
    ? [...root.querySelectorAll('.reveal:not(.is-in)')]
    : $$('.reveal:not(.is-in)');

  if (!targets.length) return;

  if (!('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-in'));
    return;
  }

  if (!revealObserver) {
    revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-in');
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );
  }

  targets.forEach((el) => revealObserver.observe(el));
}

function authVariant(el) {
  if (el.hasAttribute('data-site-auth-footer')) return 'footer';
  return 'topbar';
}

function guestAuthHtml(variant) {
  if (variant === 'footer') {
    return `
      <div class="site-auth-panel site-auth-panel--footer site-auth-panel--guest">
        <p class="site-auth-panel__hint">เข้าร่วมชุมชนนักท่องเที่ยวและผู้ประกอบการเกาะลิบง</p>
        <div class="site-auth-panel__actions site-auth-panel__actions--guest">
          <a href="${siteUrl('member/login.php')}" class="btn btn--login btn--sm site-auth-panel__btn"><i data-lucide="log-in" class="icon"></i> LOGIN</a>
          <a href="${siteUrl('member/register.php')}" class="btn btn--register btn--sm site-auth-panel__btn"><i data-lucide="user-plus" class="icon"></i> REGISTER</a>
        </div>
      </div>`;
  }

  return `
    <a href="${siteUrl('member/login.php')}" class="btn btn--login"><i data-lucide="log-in" class="icon"></i> LOGIN</a>
    <a href="${siteUrl('member/register.php')}" class="btn btn--register"><i data-lucide="user-plus" class="icon"></i> REGISTER NOW</a>`;
}

function memberRoleLabel(data) {
  if (data.role === 'admin') return 'ผู้ดูแลระบบ';
  if (data.member_type === 'business') return 'ผู้ประกอบการ';
  return 'สมาชิกรีวิว';
}

function loggedInAuthHtml(data, variant) {
  const dashIcon = data.role === 'admin' ? 'shield' : 'layout-dashboard';
  const dashLabel = data.role === 'admin' ? 'หน้าแอดมิน' : 'แดชบอร์ด';

  if (variant === 'footer') {
    return `
      <div class="site-auth-panel site-auth-panel--footer">
        <div class="site-auth-panel__user">
          <span class="site-auth-panel__avatar" aria-hidden="true"><i data-lucide="user" class="icon"></i></span>
          <div class="site-auth-panel__meta">
            <span class="site-auth-panel__status">เข้าสู่ระบบแล้ว</span>
            <strong class="site-auth-panel__name" title="${escHtml(data.name)}">${escHtml(data.name)}</strong>
            <span class="site-auth-panel__role">${escHtml(memberRoleLabel(data))}</span>
          </div>
        </div>
        <div class="site-auth-panel__actions">
          <a href="${escHtml(data.dashboard_url)}" class="btn btn--register btn--sm site-auth-panel__btn site-auth-panel__btn--primary">
            <i data-lucide="${dashIcon}" class="icon"></i> ${escHtml(dashLabel)}
          </a>
          <a href="${escHtml(data.logout_url)}" class="site-auth-panel__btn site-auth-panel__btn--logout">
            <i data-lucide="log-out" class="icon"></i> ออกจากระบบ
          </a>
        </div>
      </div>`;
  }

  return `
    <span class="site-auth__name" title="${escHtml(data.name)}">${escHtml(data.name)}</span>
    <a href="${escHtml(data.dashboard_url)}" class="btn btn--register"><i data-lucide="${dashIcon}" class="icon"></i> ${escHtml(dashLabel)}</a>
    <a href="${escHtml(data.logout_url)}" class="btn btn--login"><i data-lucide="log-out" class="icon"></i> ออกจากระบบ</a>`;
}

async function initSiteAuth() {
  const containers = $$('[data-site-auth]');
  if (!containers.length) return;

  containers.forEach((el) => {
    el.innerHTML = guestAuthHtml(authVariant(el));
  });
  initLucide();

  try {
    const res = await fetch(siteUrl('api/me.php'), { credentials: 'same-origin' });
    if (!res.ok) return;

    const data = await res.json();
    if (!data?.logged_in) return;

    containers.forEach((el) => {
      el.innerHTML = loggedInAuthHtml(data, authVariant(el));
    });

    const topbarMember = $('.topbar__member');
    if (topbarMember) {
      topbarMember.textContent = `สวัสดี, ${data.name}`;
    }

    const reviewCta = $('#reviewAuthCta');
    if (reviewCta) {
      if (data.role === 'admin') {
        reviewCta.href = data.dashboard_url;
        reviewCta.textContent = 'หน้าแอดมิน';
      } else if (data.can_post && data.action_url) {
        reviewCta.href = data.action_url;
        reviewCta.textContent = data.action_label;
        reviewCta.classList.remove('btn--purple');
        reviewCta.classList.add('btn--green');
      } else {
        reviewCta.href = data.dashboard_url;
        reviewCta.textContent = 'แดชบอร์ดสมาชิก';
      }
    }

    initLucide();
  } catch (_) {
    /* ใช้ปุ่ม guest ตามเดิม */
  }
}

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

  const contentRegistry = {};

  function registerContentSection(section, items, fallbackImgs, enrich) {
    contentRegistry[section] = items.map((item, k) => {
      const base = enrich ? enrich(item, k, fallbackImgs) : { ...item };
      const image = base.image || fallbackImgs[k % fallbackImgs.length];
      return {
        ...base,
        _key: String(base.id ?? k),
        _image: image,
        _gallery: base.gallery || null,
      };
    });
  }

  function cardClickAttrs(section, item, k, extraClass = '') {
    const key = String(item.id ?? k);
    const cls = ['card', 'card--clickable', 'reveal', 'is-in', extraClass].filter(Boolean).join(' ');
    return `class="${cls}" data-content-section="${section}" data-content-id="${key}" tabindex="0" role="button"`;
  }

  function cardHintHtml() {
    return '<span class="card__hint">ดูรายละเอียด →</span>';
  }

  function getContentItem(section, id) {
    const list = contentRegistry[section];
    if (!list) return null;
    return list.find((it) => it._key === String(id)) || null;
  }

  function buildContentModalHtml(section, item) {
    const img = item._image || item.image || '';
    let body = '';

    if (section === 'activity' || section === 'restaurant') {
      if (item.tag) body += `<span class="content-modal__tag">${escHtml(item.tag)}</span>`;
      body += `<h2 class="content-modal__title" id="contentModalTitle">${escHtml(item.title)}</h2>`;
      if (item.subtitle) body += `<p class="content-modal__desc">${escHtml(item.subtitle)}</p>`;
      if (item.description) body += `<p class="content-modal__desc">${escHtml(item.description)}</p>`;
    } else if (section === 'tour') {
      body += `<h2 class="content-modal__title" id="contentModalTitle">${escHtml(item.title)}</h2>`;
      if (item.subtitle) body += `<p class="content-modal__meta"><span class="content-modal__meta-item">${escHtml(item.subtitle)}</span></p>`;
      if (item.rating) {
        body += `<div class="content-modal__meta">${starStr(5)} <b>${escHtml(item.rating)}</b>/5 · ${escHtml(item.review_count || '0')} รีวิว</div>`;
      }
      if (item.badge) body += `<span class="content-modal__tag">${escHtml(item.badge)}</span>`;
      if (item.description) body += `<p class="content-modal__desc">${escHtml(item.description)}</p>`;
      if (item.price) {
        body += `<div class="content-modal__price">${item.price_old ? `<span class="content-modal__price-old">THB ${escHtml(item.price_old)}</span>` : ''}<span class="content-modal__price-new">THB ${escHtml(item.price)}</span></div>`;
      }
    } else if (section === 'boat') {
      body += `<h2 class="content-modal__title" id="contentModalTitle">${escHtml(item.title)}</h2>`;
      if (item.description) body += `<p class="content-modal__desc">${escHtml(item.description)}</p>`;
      if (item.price) body += `<div class="content-modal__price"><span class="content-modal__price-new">ราคา ${escHtml(item.price)} บาท/ต่อลำ</span></div>`;
    } else if (section === 'hotel') {
      body += `<h2 class="content-modal__title" id="contentModalTitle">${escHtml(item.title)}</h2>`;
      if (item.stars) body += `<div class="content-modal__meta">${starStr(item.stars)}</div>`;
      if (item.location) body += `<p class="content-modal__meta"><span class="content-modal__meta-item">📍 ${escHtml(item.location)}</span></p>`;
      if (item.amenities?.length) {
        body += `<div class="content-modal__amenities">${item.amenities.map((a) => `<span>${escHtml(a)}</span>`).join('')}</div>`;
      }
      if (item.description) body += `<p class="content-modal__desc">${escHtml(item.description)}</p>`;
      if (item._gallery?.length > 1) {
        body += `<div class="content-modal__gallery">${item._gallery.map((src) => `<img src="${escHtml(src)}" alt="" loading="lazy" decoding="async">`).join('')}</div>`;
      }
      if (item.price) body += `<div class="content-modal__price"><span class="content-modal__price-new">฿ ${escHtml(item.price)}</span></div>`;
    } else if (section === 'hotel_mini') {
      if (item.tag) body += `<span class="content-modal__tag">${escHtml(item.tag)}</span>`;
      body += `<h2 class="content-modal__title" id="contentModalTitle">${escHtml(item.title)}</h2>`;
      const meta = [item.subtitle, item.review_count ? `${item.review_count} อ่าน` : ''].filter(Boolean);
      if (meta.length) body += `<p class="content-modal__meta">${meta.map((m) => `<span class="content-modal__meta-item">${escHtml(m)}</span>`).join('')}</p>`;
      if (item.description) body += `<p class="content-modal__desc">${escHtml(item.description)}</p>`;
      else if (item.subtitle) body += `<p class="content-modal__desc">บทความแนะนำที่พักบนเกาะลิบง — อัปเดตเมื่อ ${escHtml(item.subtitle)}</p>`;
    }

    body += `<a href="#contact" class="btn btn--primary content-modal__cta">ติดต่อสอบถาม</a>`;

    return `<div class="content-modal__hero"><img src="${escHtml(img)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async"></div><div class="content-modal__body">${body}</div>`;
  }

  function initContentModal() {
    const modal = $('#contentModal');
    const bodyEl = $('#contentModalBody');
    if (!modal || !bodyEl) return;

    function close() {
      modal.hidden = true;
      document.body.style.overflow = '';
    }

    function open(section, id) {
      const item = getContentItem(section, id);
      if (!item) return;
      bodyEl.innerHTML = buildContentModalHtml(section, item);
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
      window.lucide?.createIcons?.();
      $('.content-modal__close', modal)?.focus();
    }

    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-gallery-id]')) return;

      const card = e.target.closest('[data-content-section]');
      if (!card) return;

      const mini = card.classList.contains('mini-slider__card');
      if (mini) e.preventDefault();

      if (card.closest('#galleryLightbox')) return;

      const section = card.dataset.contentSection;
      const id = card.dataset.contentId;
      if (!section || id === undefined) return;

      open(section, id);
    });

    document.addEventListener('keydown', (e) => {
      const card = e.target.closest('[data-content-section]');
      if (card && (e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault();
        open(card.dataset.contentSection, card.dataset.contentId);
        return;
      }
      if (!modal.hidden && e.key === 'Escape') close();
    });

    modal.querySelectorAll('[data-content-modal-close]').forEach((el) => {
      el.addEventListener('click', close);
    });

    bodyEl.addEventListener('click', (e) => {
      if (e.target.closest('.content-modal__cta')) close();
    });
  }

  function render(target, html) {
    const el = $(target);
    if (el) el.innerHTML = html;
  }

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

  function renderSiteHotelMini(items, fallbackImgs) {
    const track = $('#hotelMiniTrack');
    if (!track) return false;
    if (!items?.length) { track.innerHTML = ''; return true; }

    registerContentSection('hotel_mini', items, fallbackImgs);

    track.innerHTML = items.map((item, k) => {
      const cover = item.image || fallbackImgs[k % fallbackImgs.length];
      const date = item.subtitle || '';
      const views = item.review_count || '0';
      const tag = item.tag || 'ที่พัก';
      const key = String(item.id ?? k);
      return `
    <a href="#" class="mini-slider__card mini-slider__card--clickable" data-content-section="hotel_mini" data-content-id="${key}" role="button">
      <div class="mini-slider__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
        <span class="mini-slider__tag">${escHtml(tag)}</span>
      </div>
      <div class="mini-slider__card-body">
        <h4 class="mini-slider__card-title">${escHtml(item.title)}</h4>
        <div class="mini-slider__card-meta">
          <time>${escHtml(date)}</time>
          <span class="mini-slider__dot" aria-hidden="true"></span>
          <span>${escHtml(views)} อ่าน</span>
        </div>
      </div>
    </a>`;
    }).join('');

    return true;
  }

  // 6. Reviews — ดึงจาก API ถ้ามี (XAMPP) ไม่งั้นใช้ข้อมูลตัวอย่าง
  function renderReviews(list) {
    const grid = $('#reviewGrid');
    if (!grid) return;

    grid.innerHTML = list.map((r, k) => `
    <article class="review-card reveal is-in">
      ${starStr(r.rating || 5)}
      <p class="review-card__text">"${escHtml(r.text)}"</p>
      <img class="review-card__avatar" src="${escHtml(r.cover || IMG.reviews[k % IMG.reviews.length])}" alt="${escHtml(r.name)}" loading="lazy" decoding="async">
      <div class="review-card__name">${escHtml(r.name)}</div>
      <div class="review-card__sub">${escHtml(r.sub)}</div>
    </article>`).join('');

    initReveal(grid);
    window.lucide?.createIcons?.();
  }

  const fallbackReviews = Array.from({ length: 8 }, (_, k) => ({
    text: 'จองง่ายมากครับ ทักไลน์ตอบไว ไปถึงท่าเรือมีคนคอยรับ เรือสะอาด ปลอดภัย ลูก ๆ สนุกมาก ดำน้ำดูปะการังที่เกาะสวยจริง ขอจองอีกแน่นอนปีหน้า',
    name: 'อเล็กซ์ 15 ท่าน',
    sub: 'พักบ่อคืนโฮมสเตย์',
    rating: 5,
    cover: IMG.reviews[k],
  }));

  renderReviews(fallbackReviews);

  async function loadLiveReviews() {
    const sources = [siteUrl('api/reviews.php'), siteUrl('data/reviews.json')];

    for (const url of sources) {
      try {
        const res = await fetch(url);
        if (!res.ok) continue;

        const data = await res.json();
        if (!data?.ok || !data.reviews?.length) continue;

        const live = data.reviews.map((r) => ({
          text: r.text || r.title || '',
          name: r.guest_name || r.author || 'สมาชิก',
          sub: [r.booking_place, r.booking_date].filter(Boolean).join(' · '),
          rating: r.rating || 5,
          cover: r.cover || null,
        }));

        renderReviews(live);
        return;
      } catch (_) {
        /* ลองแหล่งถัดไป */
      }
    }
  }

  loadLiveReviews();

  function renderLiveListingGrid(selector, items, fallbackImgs, badge, options = {}) {
    const grid = $(selector);
    if (!grid || !items?.length) return false;

    const { cardClass = '', showDesc = false } = options;
    grid.innerHTML = items.map((item, k) => {
      const cover = item.cover || fallbackImgs[k % fallbackImgs.length];
      const price = item.price
        ? (String(item.price).toUpperCase().includes('THB') ? item.price : `THB ${item.price}`)
        : '';
      const meta = [item.location, price].filter(Boolean).join(' · ');

      return `
    <article class="card reveal is-in${cardClass ? ` ${cardClass}` : ''}">
      <div class="card__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--right">${escHtml(badge)}</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        ${meta ? `<p class="card__meta">${escHtml(meta)}</p>` : ''}
        ${showDesc && item.text ? `<p class="card__desc">${escHtml(item.text.slice(0, 140))}${item.text.length > 140 ? '…' : ''}</p>` : ''}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    window.lucide?.createIcons?.();
    return true;
  }

  function renderLiveTours(items, fallbackImgs) {
    const grid = $('#tourGrid');
    if (!grid || !items?.length) return false;

    grid.innerHTML = items.map((item, k) => {
      const cover = item.cover || fallbackImgs[k % fallbackImgs.length];
      const price = item.price
        ? (String(item.price).toUpperCase().includes('THB') ? item.price : `THB ${item.price}`)
        : '';
      const route = [item.location, item.author].filter(Boolean).join(' · ');

      return `
    <article class="card reveal is-in">
      <div class="card__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--left">ทะเลตรัง</span>
        <span class="card__badge card__badge--green">สมาชิก</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        ${route ? `<p class="card__route">${escHtml(route)}</p>` : ''}
        ${price ? `<div class="card__price"><span class="card__price-new">${escHtml(price)}</span></div>` : ''}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    window.lucide?.createIcons?.();
    return true;
  }

  async function loadLiveListings() {
    const sources = [siteUrl('api/listings.php'), siteUrl('data/listings.json')];
    const hotelFallback = IMG.hotelGalleries.map((gallery) => gallery[0]);

    for (const url of sources) {
      try {
        const res = await fetch(url);
        if (!res.ok) continue;

        const data = await res.json();
        if (!data?.ok) continue;

        let loaded = false;
        if (data.hotels?.length) {
          loaded = renderLiveListingGrid('#hotelGrid', data.hotels, hotelFallback, 'ที่พัก', {
            cardClass: 'hotel-card',
            showDesc: true,
          }) || loaded;
        }
        if (data.restaurants?.length) {
          loaded = renderLiveListingGrid('#restaurantGrid', data.restaurants, IMG.restaurants, 'แนะนำ') || loaded;
        }
        if (data.tours?.length) {
          loaded = renderLiveTours(data.tours, IMG.tours) || loaded;
        }
        if (loaded) return;
      } catch (_) {
        /* ลองแหล่งถัดไป */
      }
    }
  }

  function renderSiteActivities(items, fallbackImgs) {
    const grid = $('#activityGrid');
    if (!grid) return false;
    if (!items?.length) { grid.innerHTML = ''; return true; }

    registerContentSection('activity', items, fallbackImgs);

    grid.innerHTML = items.map((item, k) => {
      const cover = item.image || fallbackImgs[k % fallbackImgs.length];
      const tag = item.tag || 'ไฮไลต์';
      return `
    <article ${cardClickAttrs('activity', item, k)}>
      <div class="card__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--left">${escHtml(tag)}</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        ${item.subtitle ? `<p class="card__meta">${escHtml(item.subtitle)}</p>` : ''}
        ${cardHintHtml()}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    return true;
  }

  function renderSiteTours(items, fallbackImgs) {
    const grid = $('#tourGrid');
    if (!grid) return false;
    if (!items?.length) { grid.innerHTML = ''; return true; }

    registerContentSection('tour', items, fallbackImgs);

    grid.innerHTML = items.map((item, k) => {
      const cover = item.image || fallbackImgs[k % fallbackImgs.length];
      const badgeType = item.badge_type || 'left';
      const badge = item.badge || '';
      const old = item.price_old || '';
      const now = item.price || '';
      return `
    <article ${cardClickAttrs('tour', item, k)}>
      <div class="card__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--left">ทะเลตรัง</span>
        ${badge ? `<span class="card__badge card__badge--${escHtml(badgeType)}">${escHtml(badge)}</span>` : ''}
      </div>
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        ${item.subtitle ? `<p class="card__route">${escHtml(item.subtitle)}</p>` : ''}
        ${item.rating ? `<div class="card__rating">${starStr(5)} <b>${escHtml(item.rating)}</b> /5 · ${escHtml(item.review_count || '0')} รีวิว</div>` : ''}
        ${now ? `<div class="card__price">${old ? `<span class="card__price-old">THB ${escHtml(old)}</span>` : ''}<span class="card__price-new">THB ${escHtml(now)}</span></div>` : ''}
        ${cardHintHtml()}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    window.lucide?.createIcons?.();
    return true;
  }

  function renderSiteBoats(items, fallbackImgs) {
    const grid = $('#boatGrid');
    if (!grid) return false;
    if (!items?.length) { grid.innerHTML = ''; return true; }

    registerContentSection('boat', items, fallbackImgs);

    grid.innerHTML = items.map((item, k) => {
      const cover = item.image || fallbackImgs[k % fallbackImgs.length];
      return `
    <article ${cardClickAttrs('boat', item, k, 'boat-card')}>
      <div class="card__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
      </div>
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        ${item.description ? `<p class="card__desc">${escHtml(item.description)}</p>` : ''}
        ${item.price ? `<div class="card__price"><span class="card__price-new">ราคา ${escHtml(item.price)} บาท/ต่อลำ</span></div>` : ''}
        ${cardHintHtml()}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    return true;
  }

  function renderSiteHotels(items, fallbackGalleries) {
    const grid = $('#hotelGrid');
    if (!grid) return false;
    if (!items?.length) { grid.innerHTML = ''; return true; }

    registerContentSection('hotel', items, fallbackGalleries.map((g) => g[0]), (item, k, fb) => {
      const gallery = (item.gallery?.length ? item.gallery : (item.image ? [item.image] : null))
        || fallbackGalleries[k % fallbackGalleries.length]
        || [];
      return { ...item, gallery, image: item.image || gallery[0] || fb[k % fb.length] };
    });

    grid.innerHTML = items.map((item, k) => {
      const gallery = (item.gallery?.length ? item.gallery : (item.image ? [item.image] : null))
        || fallbackGalleries[k % fallbackGalleries.length]
        || [];
      const stars = item.stars || 4;
      const amenities = item.amenities || [];
      return `
    <article ${cardClickAttrs('hotel', item, k, 'hotel-card')}>
      ${renderHotelGallery('hotel-cms-' + (item.id || k), gallery, item.title)}
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        <div class="card__rating">${starStr(stars)}</div>
        ${item.location ? `<p class="card__loc"><i data-lucide="map-pin" class="icon"></i> ${escHtml(item.location)}</p>` : ''}
        ${amenities.length ? `<div class="amenities">${amenities.map((a) => `<span>${escHtml(a)}</span>`).join('')}</div>` : ''}
        ${item.description ? `<p class="card__desc">${escHtml(item.description)}</p>` : ''}
        ${item.price ? `<div class="card__price"><span class="card__price-new">฿ ${escHtml(item.price)}</span></div>` : ''}
        ${cardHintHtml()}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    window.lucide?.createIcons?.();
    return true;
  }

  function renderSiteRestaurants(items, fallbackImgs) {
    const grid = $('#restaurantGrid');
    if (!grid) return false;
    if (!items?.length) { grid.innerHTML = ''; return true; }

    registerContentSection('restaurant', items, fallbackImgs);

    grid.innerHTML = items.map((item, k) => {
      const cover = item.image || fallbackImgs[k % fallbackImgs.length];
      const badge = item.tag || 'แนะนำ';
      return `
    <article ${cardClickAttrs('restaurant', item, k)}>
      <div class="card__media">
        <img src="${escHtml(cover)}" alt="${escHtml(item.title)}" loading="lazy" decoding="async">
        <span class="card__badge card__badge--right">${escHtml(badge)}</span>
      </div>
      <div class="card__body">
        <h4 class="card__title">${escHtml(item.title)}</h4>
        ${item.subtitle ? `<p class="card__meta">${escHtml(item.subtitle)}</p>` : ''}
        ${cardHintHtml()}
      </div>
    </article>`;
    }).join('');

    initReveal(grid);
    return true;
  }

  async function loadSiteContent() {
    const sources = [siteUrl('api/site-content.php'), siteUrl('data/site-content.json')];

    for (const url of sources) {
      try {
        const res = await fetch(url);
        if (!res.ok) continue;

        const data = await res.json();
        if (!data?.ok) continue;

        renderSiteActivities(data.activity || [], IMG.activities);
        renderSiteTours(data.tour || [], IMG.tours);
        renderSiteBoats(data.boat || [], IMG.boats);
        renderSiteHotels(data.hotel || [], IMG.hotelGalleries);
        renderSiteHotelMini(data.hotel_mini || [], IMG.miniSlides);
        renderSiteRestaurants(data.restaurant || [], IMG.restaurants);
        return true;
      } catch (_) {
        /* ลองแหล่งถัดไป */
      }
    }

    return false;
  }

  function renderFallbackGrids() {
    renderSiteActivities(
      activities.map((a) => ({ title: a.t, tag: a.tag, subtitle: a.m })),
      IMG.activities,
    );
    renderSiteTours(
      tours.map((t) => ({
        title: t.t,
        subtitle: t.route,
        badge: t.badge,
        badge_type: t.badgeType,
        rating: t.rate,
        review_count: t.count,
        price: t.now,
        price_old: t.old,
      })),
      IMG.tours,
    );
    renderSiteBoats(
      boats.map((b) => ({ title: b.t, description: b.desc, price: b.price })),
      IMG.boats,
    );
    renderSiteHotels(
      hotels.map((h, k) => ({
        id: k,
        title: h.t,
        location: h.loc,
        stars: h.stars,
        amenities: h.am,
        description: h.desc,
        price: h.price,
        gallery: IMG.hotelGalleries[k],
      })),
      IMG.hotelGalleries,
    );
    renderSiteRestaurants(
      restaurants.map((r) => ({ title: r.t, subtitle: r.m, tag: 'แนะนำ' })),
      IMG.restaurants,
    );
    renderSiteHotelMini(
      hotelMiniSlides.map((s) => ({
        title: s.t,
        subtitle: s.date,
        review_count: s.views,
        tag: 'ที่พัก',
      })),
      IMG.miniSlides,
    );
  }

  (async () => {
    const ok = await loadSiteContent();
    if (!ok) renderFallbackGrids();
  })();

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
      e.stopPropagation();
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

  initContentModal();

  /* ---------- SCROLL REVEAL ---------- */
  initReveal();

  initSiteAuth();
  initLucide();
