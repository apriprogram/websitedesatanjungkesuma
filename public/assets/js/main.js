// Hero slider functionality
if (typeof currentSlideIndex === 'undefined') {
    var currentSlideIndex = 0;
}

const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.hero-dot');

// Ensure functions are global but only defined once if possible, or simple let them redefine
window.showSlide = function(index) {
    if (!slides.length) return;
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    if (slides[index]) slides[index].classList.add('active');
    if (dots[index]) dots[index].classList.add('active');
}

window.changeSlide = function(direction) {
    if (!slides.length) return;
    currentSlideIndex += direction;

    if (currentSlideIndex >= slides.length) {
        currentSlideIndex = 0;
    } else if (currentSlideIndex < 0) {
        currentSlideIndex = slides.length - 1;
    }

    showSlide(currentSlideIndex);
}

window.currentSlide = function(index) {
    if (!slides.length) return;
    currentSlideIndex = index - 1;
    showSlide(currentSlideIndex);
}

// Auto slide every 5 seconds (prevent multiple intervals)
if (slides.length > 1 && !window.heroSliderInterval) {
    window.heroSliderInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
}

function buildCompactPages(totalPages, currentPage, windowSize = 1, maxVisible = 5) {
    const pages = [];
    if (totalPages <= maxVisible + 2) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
        return pages;
    }

    const addRange = (start, end) => {
        for (let i = start; i <= end; i++) pages.push(i);
    };

    pages.push(1);
    const startWindow = Math.max(2, currentPage - windowSize);
    const endWindow = Math.min(totalPages - 1, currentPage + windowSize);

    if (startWindow > 2) pages.push('dots');
    addRange(startWindow, endWindow);
    if (endWindow < totalPages - 1) pages.push('dots');

    pages.push(totalPages);
    return pages;
}

const newsGrid = document.getElementById('newsGrid');
const newsPagination = document.getElementById('newsPagination');

if (newsGrid && newsPagination) {
    const fallbackNews = [{
        icon: 'fas fa-hands-helping',
        date: '20 Agustus 2024',
        title: 'Gotong Royong Bersihkan Lingkungan Desa',
        desc: 'Kegiatan gotong royong membersihkan lingkungan desa untuk menjaga kebersihan dan keindahan.',
        url: '#',
        views: 0,
        author: 'Admin Desa',
        category: 'Berita Desa'
    }];
    const newsData = (Array.isArray(window.__NEWS_DATA__) && window.__NEWS_DATA__.length)
        ? window.__NEWS_DATA__
        : fallbackNews;

    let currentNewsPage = 1;
    const newsPerPage = 6;
    let totalNewsPages = Math.max(1, Math.ceil(newsData.length / newsPerPage));

    function createNewsCard(news) {
        const link = news.url || '#';
        const hasImage = !!news.image;
        const desc = news.desc || 'Kabar terbaru desa.';
        const date = news.date || 'Segera terbit';
        const author = news.author || 'Admin Desa';
        const title = news.title || 'Berita Desa';
        const thumb = hasImage
            ? `<img src="${news.image}" alt="${title}" loading="lazy">`
            : `<div class="news-card__thumb-placeholder"><i class="fas fa-image" aria-hidden="true"></i></div>`;
        return `
                <article class="news-card news-card--simple">
                    <a class="news-card__thumb" href="${link}" aria-label="Baca ${title}">${thumb}</a>
                    <div class="news-card__info">
                        <div class="news-card__meta-row news-card__meta-row--top">
                            <span class="news-meta-value">${date}</span>
                            <span class="news-meta-value news-meta-value--muted">${author}</span>
                        </div>
                        <h3 class="news-card__title">${title}</h3>
                        <p class="news-card__desc">${desc}</p>
                        <a href="${link}" class="news-card__link" aria-label="Baca ${title}">Lihat Selengkapnya <span aria-hidden="true">&rarr;</span></a>
                    </div>
                </article>
            `;
    }

    function createPaginationButtons() {
        let buttons = '';

        buttons +=
            `<button class="pagination-btn" onclick="changeNewsPage(${currentNewsPage - 1})" ${currentNewsPage === 1 ? 'disabled' : ''}>&lsaquo;</button>`;

        const pages = buildCompactPages(totalNewsPages, currentNewsPage, 1, 5);
        pages.forEach((item) => {
            if (item === 'dots') {
                buttons += `<span class="pagination-btn pagination-btn--dots" aria-hidden="true">&hellip;</span>`;
                return;
            }
            buttons += `<button class="pagination-btn ${item === currentNewsPage ? 'active' : ''}" onclick="changeNewsPage(${item})">${item}</button>`;
        });

        buttons +=
            `<button class="pagination-btn" onclick="changeNewsPage(${currentNewsPage + 1})" ${currentNewsPage === totalNewsPages ? 'disabled' : ''}>&rsaquo;</button>`;

        return buttons;
    }

    function changeNewsPage(page) {
        if (page < 1 || page > totalNewsPages) return;

        currentNewsPage = page;
        displayNews();
    }

    function displayNews() {
        if (!newsData.length) {
            newsGrid.innerHTML = '<div class="news-empty">Belum ada berita diterbitkan.</div>';
            newsPagination.innerHTML = '';
            return;
        }

        totalNewsPages = Math.max(1, Math.ceil(newsData.length / newsPerPage));
        const startIndex = (currentNewsPage - 1) * newsPerPage;
        const endIndex = startIndex + newsPerPage;
        const currentNews = newsData.slice(startIndex, endIndex);

        newsGrid.innerHTML = currentNews.map(createNewsCard).join('');
        newsPagination.innerHTML = createPaginationButtons();
    }

    window.changeNewsPage = changeNewsPage;

    displayNews();
}

// Galeri Video pagination (4 video per halaman, 2 kolom => 2 baris)
(function initGaleriPagination() {
  const list = document.getElementById('galeriList');
  const pager = document.getElementById('galeriPagination');
  if (!list || !pager) return;

  const items = Array.from(list.querySelectorAll('.galeri-card'));
  const itemsPerPage = 4;
  let currentPage = 1;
  const totalPages = Math.max(1, Math.ceil(items.length / itemsPerPage));

  function renderPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    // show only the items in the current page
    items.forEach((el, idx) => {
      const start = (currentPage - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      el.style.display = (idx >= start && idx < end) ? '' : 'none';
    });
    renderPagination();
  }

  function renderPagination() {
    let html = '';
    html += `<button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="window.__galeriChange && __galeriChange(${currentPage - 1})">&lt;</button>`;
    const pages = buildCompactPages(totalPages, currentPage, 1, 5);
    pages.forEach((p) => {
      if (p === 'dots') {
        html += `<span class="pagination-btn pagination-btn--dots" aria-hidden="true">&hellip;</span>`;
        return;
      }
      html += `<button class="pagination-btn ${p === currentPage ? 'active' : ''}" onclick="window.__galeriChange && __galeriChange(${p})">${p}</button>`;
    });
    html += `<button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="window.__galeriChange && __galeriChange(${currentPage + 1})">&gt;</button>`;
    pager.innerHTML = html;
  }

  // expose small handler for inline onclicks
  window.__galeriChange = function(page) {
    renderPage(page);
  };

  // initial render
  renderPage(1);
})();

// Infografis auto slider (kanan galeri)
(function initInfografisSlider() {
  const slider = document.getElementById('infografisSlider');
  const dotsWrap = document.getElementById('infografisDots');
  if (!slider || !dotsWrap) return;

  const slides = Array.from(slider.querySelectorAll('.infografis-slide'));
  let idx = 0;

  // build dots
  dotsWrap.innerHTML = slides.map((_, i) => `<span class="infografis-dot${i===0?' active':''}" data-i="${i}"></span>`).join('');
  const dots = Array.from(dotsWrap.querySelectorAll('.infografis-dot'));

  function show(i) {
    slides.forEach((el, k) => el.classList.toggle('active', k === i));
    dots.forEach((el, k) => el.classList.toggle('active', k === i));
    idx = i;
    // keep current index on the element for other handlers
    slider.dataset.index = String(i);
  }

  // click dots
  dots.forEach((d) => d.addEventListener('click', () => {
    const i = Number(d.getAttribute('data-i'));
    show(i);
  }));

  // auto slide
  setInterval(() => {
    const next = (idx + 1) % slides.length;
    show(next);
  }, 4000);

  // clicking the slider opens the modal with the active image
  slider.addEventListener('click', () => {
    const activeImg = slider.querySelector('.infografis-slide.active img');
    const modal = document.getElementById('infografisModal');
    const modalImg = document.getElementById('infografisModalImg');
    if (activeImg && modal && modalImg) {
      modalImg.src = activeImg.src;
      modal.classList.add('open');
    }
  });
})();

// Infografis modal viewer
(function initInfografisModal() {
  const modal = document.getElementById('infografisModal');
  const modalImg = document.getElementById('infografisModalImg');
  const closeBtn = document.getElementById('infografisModalClose');
  if (!modal || !modalImg) return;

  const slidesImgs = document.querySelectorAll('.infografis-slide img');
  slidesImgs.forEach((img) => {
    img.style.cursor = 'zoom-in';
    img.addEventListener('click', () => {
      modalImg.src = img.src;
      modal.classList.add('open');
    });
  });

  function close() { modal.classList.remove('open'); }
  if (closeBtn) closeBtn.addEventListener('click', close);

  modal.addEventListener('click', (e) => {
    // close when clicking backdrop area
    if (e.target === modal) close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('open')) close();
  });
})();

// Video modal for gallery
(function initVideoModal() {
  const videoModal = document.getElementById('videoModal');
  const videoFrame = document.getElementById('videoModalFrame');
  const videoClose = document.getElementById('videoModalClose');
  if (!videoModal || !videoFrame) return;

  // add click-capture overlays to each gallery video
  document.querySelectorAll('.galeri-card .video-wrapper').forEach((wrap) => {
    // ensure wrapper has relative positioning (already set in CSS)
    const iframe = wrap.querySelector('iframe');
    if (!iframe) return;
    const overlay = document.createElement('div');
    overlay.className = 'video-click-capture';
    overlay.setAttribute('aria-label', 'Tonton video');
    wrap.appendChild(overlay);

    overlay.addEventListener('click', () => {
      const src = iframe.getAttribute('src') || '';
      if (!src) return;
      let url = src;
      // ensure controls=1 for full YouTube UI
      if (url.includes('controls=0')) {
        url = url.replace('controls=0', 'controls=1');
      } else if (!url.includes('controls=')) {
        url += (url.includes('?') ? '&' : '?') + 'controls=1';
      }
      // enable autoplay and fullscreen controls
      url += (url.includes('?') ? '&' : '?') + 'autoplay=1&fs=1';
      // optionally enable JS API if needed in future
      if (!url.includes('enablejsapi=')) {
        url += '&enablejsapi=1';
      }
      videoFrame.src = url;
      videoModal.classList.add('open');
    });
  });

  function closeVideo() {
    videoModal.classList.remove('open');
    // stop playback
    videoFrame.src = '';
  }

  if (videoClose) videoClose.addEventListener('click', closeVideo);
  videoModal.addEventListener('click', (e) => { if (e.target === videoModal) closeVideo(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && videoModal.classList.contains('open')) closeVideo(); });
})();

(function initAnnouncementModule() {
    if (window.skipAnnouncementModule) {
        return;
    }
    // Announcement data
const backendAnnouncements = window.__ANNOUNCEMENT_DATA__ || {};
const announcementData = {
    desa: Array.isArray(backendAnnouncements.desa) ? backendAnnouncements.desa : [],
    daerah: Array.isArray(backendAnnouncements.daerah) ? backendAnnouncements.daerah : [],
    pusat: Array.isArray(backendAnnouncements.pusat) ? backendAnnouncements.pusat : [],
};

const getAnnouncements = (type) => {
    if (type === 'all') {
        const d = announcementData.desa || [];
        const r = announcementData.daerah || [];
        const p = announcementData.pusat || [];
        // Combine all and sort desc by raw_date/date
        const all = [...d, ...r, ...p];
        return all.sort((a, b) => {
            const dateA = a.raw_date || a.date || '';
            const dateB = b.raw_date || b.date || '';
            // simple string comparison if ISO, otherwise needs parsing. 
            // Assuming raw_date is ISO (YYYY-MM-DD...)
            if (dateA > dateB) return -1;
            if (dateA < dateB) return 1;
            return 0;
        });
    }
    const arr = announcementData[type];
    return Array.isArray(arr) ? arr : [];
};

// Simple local cache for daerah announcements (LampungProv)
const ANN_CACHE_KEY = 'lampungprov_anns_v1';
const ANN_CACHE_TTL = 5 * 60 * 1000; // 5 minutes

function getCachedProvAnnouncements() {
    try {
        const raw = localStorage.getItem(ANN_CACHE_KEY);
        if (!raw) return null;
        const obj = JSON.parse(raw);
        if (!obj || !Array.isArray(obj.items)) return null;
        if (Date.now() - (obj.time || 0) > ANN_CACHE_TTL) return null;
        return obj.items;
    } catch (_) { return null; }
}

function setCachedProvAnnouncements(items) {
    try {
        localStorage.setItem(ANN_CACHE_KEY, JSON.stringify({ time: Date.now(), items }));
    } catch (_) { /* ignore */ }
}

// Announcement functionality
let currentAnnouncementType = 'all'; // Default to 'all' match the announcement page
let currentAnnouncementPage = 1;
let announcementQuery = '';
let filteredAnnouncements = [];
const announcementsPerPage = 4;

function showAnnouncement(evt, type) {
    if (!['all', 'desa','daerah','pusat'].includes(type)) {
        type = 'all';
    }
    currentAnnouncementType = type;
    currentAnnouncementPage = 1;

    setAnnouncementTabActive(type, evt);

    displayAnnouncements();

    // Lazy fetch remote for daerah if empty
    if (type === 'daerah' && !getAnnouncements('daerah').length) {
        fetchLampungProvAnnouncements();
    }
}

function splitDateForDisplay(dateStr) {
    const str = String(dateStr || '');
    const todayMatch = str.match(/^\s*today\s*(.*)$/i);
    if (todayMatch) {
        const timeMatch = str.match(/(\d{1,2}[:\.]\d{2})/);
        const time = timeMatch ? timeMatch[1].replace('.', ':') : '';
        return { top: 'Today', bottom: time };
    }
    const parts = str.split(/\s+/).filter(Boolean);
    if (parts.length >= 3) {
        return { top: `${parts[0]} ${parts[1]}`, bottom: parts[2] };
    }
    return { top: str, bottom: '' };
}

// Try to extract Month, Day, Year parts for nicer display
function extractDateParts(dateStr) {
    const str = String(dateStr || '').trim();
    const shortMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const indoMonths = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    // Pattern like: Jun 03 2025
    let m = str.match(/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{1,2})\s+(\d{4})$/i);
    if (m) {
        const idx = shortMonths.findIndex(s => s.toLowerCase() === m[1].toLowerCase());
        const monthFull = idx >= 0 ? indoMonths[idx] : m[1];
        return {
            month: monthFull,
            day: m[2],
            year: m[3]
        };
    }
    // Pattern like: 2025-06-03 or 2025/06/03
    m = str.match(/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/);
    if (m) {
        const mi = Math.min(11, Math.max(0, parseInt(m[2], 10) - 1));
        const monthFull = indoMonths[mi] || m[2];
        return { month: monthFull, day: String(parseInt(m[3],10)), year: m[1] };
    }
    return null;
}

function extractTodayParts(dateStr) {
    const str = String(dateStr || '');
    const m = str.match(/^\s*today\s*(.*)$/i);
    if (!m) return null;
    const now = new Date();
    const indoMonths = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const timeMatch = (m[1] || '').match(/(\d{1,2}[:\.]\d{2})/);
    const time = timeMatch ? timeMatch[1].replace('.', ':') : '';
    return { day: String(now.getDate()), month: indoMonths[now.getMonth()], year: String(now.getFullYear()), time };
}

function createAnnouncementItem(announcement) {
    if (!announcement) return '';
    
    // Debugging data if needed
    // console.log('Rendering announcement:', announcement);

    // Prefer raw ISO date for parsing if available, otherwise use formatted date string
    const dateSource = announcement.raw_date || announcement.date; 
    const parts = extractDateParts(dateSource) || extractTodayParts(dateSource);
    
    const title = announcement.title || '';
    const desc = announcement.desc || '';
    const link = announcement.url || '#';
    const img = announcement.image || 'img/Logo/speaker.png';
    
    // Format date simple: 02 Dec 2025
    // If parts exist, use them. If not, use the pre-formatted date string from PHP which is "Senin, 12 Jan 2026"
    const dateStr = parts ? `${parts.day} ${parts.month} ${parts.year}` : (announcement.date || '');

    return `
    <a class="announcement-item" href="${link}">
        <div class="announcement-media">
            <img src="${img}" alt="${title}" loading="lazy">
        </div>
        <div class="announcement-body">
            <h3 class="announcement-title">${title}</h3>
            <div class="announcement-desc">${desc}</div>
        </div>
        <div class="announcement-date-col">
            <div class="announcement-date-text">
                <span>${dateStr}</span>
                <i class="fas fa-chevron-right announcement-chevron"></i>
            </div>
        </div>
    </a>`;
}

function createAnnouncementPaginationButtons() {
    return buildAnnPagination();
}

// Safer pagination builder using HTML entities for arrows
function buildAnnPagination() {
    const totalAnnouncements = (filteredAnnouncements && filteredAnnouncements.length) || getAnnouncements(currentAnnouncementType).length;
    const totalPages = Math.max(1, Math.ceil(totalAnnouncements / announcementsPerPage));
    let buttons = '';

    buttons += `<button class="pagination-btn" onclick="changeAnnouncementPage(${currentAnnouncementPage - 1})" ${currentAnnouncementPage === 1 ? 'disabled' : ''}>&lsaquo;</button>`;

    const pages = buildCompactPages(totalPages, currentAnnouncementPage, 1, 5);
    pages.forEach((p) => {
        if (p === 'dots') {
            buttons += `<span class="pagination-btn pagination-ellipsis" aria-hidden="true">&hellip;</span>`;
            return;
        }
        buttons += `<button class="pagination-btn ${p === currentAnnouncementPage ? 'active' : ''}" onclick="changeAnnouncementPage(${p})">${p}</button>`;
    });

    buttons += `<button class="pagination-btn" onclick="changeAnnouncementPage(${currentAnnouncementPage + 1})" ${currentAnnouncementPage === totalPages ? 'disabled' : ''}>&rsaquo;</button>`;

    return buttons;
}

function setAnnouncementTabActive(type, evt) {
    const tabs = document.querySelectorAll('.announcement-tab');
    tabs.forEach((tab) => {
        const t = tab.getAttribute('data-type') || '';
        const match = t === type;
        tab.classList.toggle('active', match);
    });
    if (!tabs.length && evt && evt.target) {
        evt.target.classList.add('active');
    }
}

function displayAnnouncements() {
    const announcementList = document.getElementById('announcementList');
    const announcementPagination = document.getElementById('announcementPagination');

    // Jika elemen tidak ada (misal bukan di halaman home), hentikan agar tidak error
    if (!announcementList || !announcementPagination) return;
    
    try {
        const src = getAnnouncements(currentAnnouncementType);
        const q = (announcementQuery || '').toLowerCase();
        filteredAnnouncements = q
            ? src.filter(a => (a.title + ' ' + a.desc + ' ' + a.date).toLowerCase().includes(q))
            : src.slice();

        const total = filteredAnnouncements.length;
        const totalPages = Math.max(1, Math.ceil(total / announcementsPerPage));
        if (currentAnnouncementPage > totalPages) currentAnnouncementPage = 1;

        const startIndex = (currentAnnouncementPage - 1) * announcementsPerPage;
        const endIndex = startIndex + announcementsPerPage;
        const currentAnnouncements = filteredAnnouncements.slice(startIndex, endIndex);

        if (!currentAnnouncements.length) {
            announcementList.innerHTML = '<div class="announcement-empty">Belum ada pengumuman.</div>';
        } else {
            announcementList.innerHTML = currentAnnouncements.map(item => {
                try { 
                    return createAnnouncementItem(item); 
                } catch(e) { 
                    console.error('Error rendering item', e, item); 
                    return ''; 
                }
            }).join('');
        }
        announcementPagination.innerHTML = buildAnnPagination();
    } catch(err) {
        console.error('Error displaying announcements:', err);
        announcementList.innerHTML = `<div style="text-align:center;color:red;padding:20px;">Gagal memuat pengumuman: ${err.message}</div>`;
    }
}

function changeAnnouncementPage(page) {
    if (page < 1) return;
    currentAnnouncementPage = page;
    displayAnnouncements();
}

// Initialize announcements list and load remote updates (only if section exists)
const annListEl = document.getElementById('announcementList');
if (annListEl) {
  setAnnouncementTabActive(currentAnnouncementType);
  showAnnouncement(null, currentAnnouncementType);
}

// expose for inline handlers
window.showAnnouncement = showAnnouncement;
window.changeAnnouncementPage = changeAnnouncementPage;

// Muat pengumuman Pemerintah Daerah dari LampungProv (WordPress API)
// Catatan: jika situs membatasi CORS, panggil lewat proxy server Anda.
const allowLampungProvFetch = window.location && /lampungprov\.go\.id$/.test(window.location.hostname);
if (allowLampungProvFetch) {
    document.addEventListener('DOMContentLoaded', () => {
        fetchLampungProvAnnouncements();
    });
}

// Announcement search bind
const annSearch = document.getElementById('announcementSearch');
if (annSearch) {
    annSearch.addEventListener('input', (e) => {
        announcementQuery = e.target.value || '';
        currentAnnouncementPage = 1;
        displayAnnouncements();
    });
}

})();

// Info Graphic Slider functionality (safe no-op when removed)
let currentInfoSlideIndex = 0;
const infoSlides = document.querySelectorAll('.info-graphic-slide');
const infoDots = document.querySelectorAll('.info-graphic-dot');

function showInfoSlide(index) {
    if (!infoSlides.length) return;
    infoSlides.forEach(slide => slide.classList.remove('active'));
    infoDots.forEach(dot => dot.classList.remove('active'));
    if (infoSlides[index]) infoSlides[index].classList.add('active');
    if (infoDots[index]) infoDots[index].classList.add('active');
}

function changeInfoSlide(direction) {
    if (!infoSlides.length) return;
    currentInfoSlideIndex += direction;
    if (currentInfoSlideIndex >= infoSlides.length) currentInfoSlideIndex = 0;
    else if (currentInfoSlideIndex < 0) currentInfoSlideIndex = infoSlides.length - 1;
    showInfoSlide(currentInfoSlideIndex);
}

function currentInfoSlide(index) {
    if (!infoSlides.length) return;
    currentInfoSlideIndex = index - 1;
    showInfoSlide(currentInfoSlideIndex);
}

if (infoSlides.length) {
    setInterval(() => { changeInfoSlide(1); }, 4000);
}

// Bottom Banner Slider (auto + arrows)
let bottomBannerIndex = 0;
let bottomBannerTimer = null;

function renderBottomBanner() {
    const slides = document.querySelectorAll('#bottomSlides .bottom-slide');
    const wrapper = document.getElementById('bottomSlides');
    if (!slides.length || !wrapper) return;
    const total = slides.length;
    bottomBannerIndex = (bottomBannerIndex + total) % total;
    wrapper.style.transform = `translateX(-${bottomBannerIndex * 100}%)`;
}

function changeBottomBannerSlide(delta) {
    const slides = document.querySelectorAll('#bottomSlides .bottom-slide');
    if (!slides.length) return;
    bottomBannerIndex += delta;
    const total = slides.length;
    if (bottomBannerIndex < 0) bottomBannerIndex = total - 1;
    if (bottomBannerIndex >= total) bottomBannerIndex = 0;
    renderBottomBanner();
    restartBottomBannerAuto();
}

// dots removed as requested

function startBottomBannerAuto() {
    stopBottomBannerAuto();
    bottomBannerTimer = setInterval(() => {
        bottomBannerIndex++;
        renderBottomBanner();
    }, 5000);
}

function stopBottomBannerAuto() {
    if (bottomBannerTimer) {
        clearInterval(bottomBannerTimer);
        bottomBannerTimer = null;
    }
}

function restartBottomBannerAuto() { stopBottomBannerAuto(); startBottomBannerAuto(); }

document.addEventListener('DOMContentLoaded', () => {
    const bottom = document.getElementById('bottomBanner');
    if (bottom) {
        startBottomBannerAuto();
        bottom.addEventListener('mouseenter', stopBottomBannerAuto);
        bottom.addEventListener('mouseleave', startBottomBannerAuto);
        // Keep simple: no keyboard/swipe
    }
    // Back to top button + WhatsApp & A11y floats
    const waWidget = document.getElementById('whatsappWidget');
    const waBtn = document.getElementById('whatsappBtn');
    const waPanel = document.getElementById('whatsappPanel');
    const waClose = document.querySelector('.whatsapp-panel-close');
    const waLink = document.getElementById('whatsappLink');
    const waNumberText = document.getElementById('whatsappNumberText');
    const a11yWidget = document.getElementById('a11yWidget');
    const a11yBtn = document.getElementById('a11yBtn');
    const a11yPanel = document.getElementById('a11yPanel');
    const a11yClose = document.querySelector('.a11y-panel-close');
    const a11yInc = document.getElementById('a11yInc');
    const a11yDec = document.getElementById('a11yDec');
    const a11yReset = document.getElementById('a11yReset');
    const a11yResetAll = document.getElementById('a11yResetAll');
    const a11yScaleText = document.getElementById('a11yScaleText');
    // feature tiles
    const featMap = [
        { id: 'feat-contrast', cls: 'a11y-contrast-boost', key: 'a11y_contrast' },
        { id: 'feat-highlight-links', cls: 'a11y-highlight-links', key: 'a11y_links' },
        { id: 'feat-big-text', cls: 'a11y-big-text', key: 'a11y_bigtext' },
        { id: 'feat-text-spacing', cls: 'a11y-text-spacing', key: 'a11y_spacing' },
        { id: 'feat-reduce-motion', cls: 'a11y-reduced-motion', key: 'a11y_motion' },
        { id: 'feat-hide-images', cls: 'a11y-hide-images', key: 'a11y_hideimg' },
        { id: 'feat-dyslexia', cls: 'dyslexia-friendly', key: 'a11y_dyslexia' },
        { id: 'feat-cursor', cls: 'a11y-big-cursor', key: 'a11y_cursor' },
        { id: 'feat-line-height', cls: 'a11y-line-height', key: 'a11y_lineheight' },
        { id: 'feat-text-align', cls: 'a11y-left-align', key: 'a11y_align' },
        // saturation handled separately
    ];
    const backBtn = document.getElementById('backToTop');
    if (backBtn) {
        const toggleBackBtn = () => {
            if (window.scrollY > 300) backBtn.classList.add('show');
            else backBtn.classList.remove('show');
        };
        window.addEventListener('scroll', toggleBackBtn, { passive: true });
        toggleBackBtn();
        backBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    // WhatsApp setup and interactions
    if (waWidget && waBtn && waPanel) {
        // Always show WhatsApp button
        waBtn.classList.add('show');
        const rawNumber = (waWidget.dataset.waNumber || '').replace(/[^\d]/g, '');
        const message = encodeURIComponent(waWidget.dataset.waMessage || '');
        if (waLink) {
            waLink.href = rawNumber ? `https://wa.me/${rawNumber}${message ? `?text=${message}` : ''}` : 'https://wa.me';
        }
        if (waNumberText && rawNumber) {
            waNumberText.textContent = `+${rawNumber}`;
        }
        const openPanel = () => { waPanel.classList.add('open'); waPanel.setAttribute('aria-hidden', 'false'); };
        const closePanel = () => { waPanel.classList.remove('open'); waPanel.setAttribute('aria-hidden', 'true'); };
        /* CONFLICT FIX: Logic moved to floating-widgets.blade.php
        waBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (waPanel.classList.contains('open')) closePanel(); else openPanel();
        });
        if (waClose) waClose.addEventListener('click', (e) => { e.stopPropagation(); closePanel(); });
        document.addEventListener('click', (e) => {
            if (!waPanel.classList.contains('open')) return;
            const target = e.target;
            if (waPanel.contains(target)) return;
            if (waBtn.contains(target)) return;
            closePanel();
        });
        */
    }
    // Accessibility setup and interactions
    if (a11yWidget && a11yBtn && a11yPanel) {
        // Always show Accessibility button
        a11yBtn.classList.add('show');

        const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
        const loadScale = () => {
            const s = parseFloat(localStorage.getItem('a11yScale') || '1');
            return isFinite(s) ? clamp(s, 0.8, 1.6) : 1;
        };
        const saveScale = (s) => { localStorage.setItem('a11yScale', String(s)); };
        const applyScale = (s) => {
            document.documentElement.style.fontSize = Math.round(s * 100) + '%';
            if (a11yScaleText) a11yScaleText.textContent = Math.round(s * 100) + '%';
        };
        let scale = loadScale();
        applyScale(scale);

        // THEME MODES (mutually exclusive): invert, dark contrast, light contrast
        const themeKey = 'a11y_theme';
        const themeTiles = [
            { id: 'feat-invert', cls: 'a11y-invert', val: 'invert' },
            { id: 'feat-contrast-dark', cls: 'a11y-contrast-dark', val: 'dark' },
            { id: 'feat-contrast-light', cls: 'a11y-contrast-light', val: 'light' },
        ];
        const applyTheme = (val) => {
            themeTiles.forEach(({ cls, id, val: v }) => {
                const el = document.getElementById(id);
                const on = (val === v);
                document.body.classList.toggle(cls, on);
                if (el) el.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        };
        let currentTheme = localStorage.getItem(themeKey) || '';
        applyTheme(currentTheme);
        themeTiles.forEach(({ id, val }) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('click', () => {
                currentTheme = (currentTheme === val) ? '' : val;
                localStorage.setItem(themeKey, currentTheme);
                applyTheme(currentTheme);
            });
        });

        // SIMPLE TOGGLES from featMap (except ones replaced)
        const simpleToggles = featMap.filter(f => !['a11y-contrast-boost'].includes(f.cls));
        simpleToggles.forEach(({ id, cls, key }) => {
            const el = document.getElementById(id);
            if (!el) return;
            const on = localStorage.getItem(key) === '1';
            document.body.classList.toggle(cls, on);
            el.setAttribute('aria-pressed', on ? 'true' : 'false');
            el.addEventListener('click', () => {
                const newOn = !(el.getAttribute('aria-pressed') === 'true');
                el.setAttribute('aria-pressed', newOn ? 'true' : 'false');
                document.body.classList.toggle(cls, newOn);
                localStorage.setItem(key, newOn ? '1' : '0');
            });
        });

        // TEXT ALIGNMENT CYCLE: center -> right -> left -> off
        const alignKey = 'a11y_align_state';
        const alignTile = document.getElementById('feat-text-align');
        const applyAlign = (state) => {
            document.body.classList.toggle('a11y-align-center', state === 'center');
            document.body.classList.toggle('a11y-align-right', state === 'right');
            document.body.classList.toggle('a11y-left-align', state === 'left');
            if (alignTile) alignTile.setAttribute('aria-pressed', state === 'off' ? 'false' : 'true');
        };
        let alignState = localStorage.getItem(alignKey) || 'off';
        applyAlign(alignState);
        if (alignTile) alignTile.addEventListener('click', () => {
            alignState = (alignState === 'off') ? 'center' : (alignState === 'center') ? 'right' : (alignState === 'right') ? 'left' : 'off';
            localStorage.setItem(alignKey, alignState);
            applyAlign(alignState);
        });

        // SATURATION CYCLE (off -> low -> high -> desaturate -> off)
        const satKey = 'a11y_saturation';
        const satTile = document.getElementById('feat-saturation');
        const applySat = (state) => {
            document.body.classList.toggle('a11y-saturation-low', state === 'low');
            document.body.classList.toggle('a11y-saturation-high', state === 'high');
            document.body.classList.toggle('a11y-desaturate', state === 'desat');
            if (satTile) {
                satTile.dataset.state = state;
                satTile.setAttribute('aria-pressed', state === 'off' ? 'false' : 'true');
            }
        };
        let satState = localStorage.getItem(satKey) || 'off';
        applySat(satState);
        if (satTile) satTile.addEventListener('click', () => {
            satState = (satState === 'off') ? 'low' : (satState === 'low') ? 'high' : (satState === 'high') ? 'desat' : 'off';
            localStorage.setItem(satKey, satState);
            applySat(satState);
        });

        const openA11y = () => { a11yPanel.classList.add('open'); a11yPanel.setAttribute('aria-hidden', 'false'); };
        const closeA11y = () => { a11yPanel.classList.remove('open'); a11yPanel.setAttribute('aria-hidden', 'true'); };
        /* CONFLICT FIX: Logic moved to floating-widgets.blade.php
        a11yBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (a11yPanel.classList.contains('open')) closeA11y(); else openA11y();
        });
        if (a11yClose) a11yClose.addEventListener('click', (e) => { e.stopPropagation(); closeA11y(); });
        document.addEventListener('click', (e) => {
            if (!a11yPanel.classList.contains('open')) return;
            const t = e.target;
            if (a11yPanel.contains(t) || a11yBtn.contains(t)) return;
            closeA11y();
        });
        */

        if (a11yInc) a11yInc.addEventListener('click', () => { scale = clamp(scale + 0.1, 0.8, 1.6); applyScale(scale); saveScale(scale); });
        if (a11yDec) a11yDec.addEventListener('click', () => { scale = clamp(scale - 0.1, 0.8, 1.6); applyScale(scale); saveScale(scale); });
        if (a11yReset) a11yReset.addEventListener('click', () => { scale = 1; applyScale(scale); saveScale(scale); });
        if (a11yResetAll) a11yResetAll.addEventListener('click', () => {
            // reset font scale
            scale = 1; applyScale(scale); saveScale(scale);
            // turn off all tile features
            featMap.forEach(({ id, cls, key }) => {
                const el = document.getElementById(id);
                if (el) el.setAttribute('aria-pressed', 'false');
                document.body.classList.remove(cls);
                localStorage.setItem(key, '0');
            });
            // also remove high contrast if previously on
            document.body.classList.remove('high-contrast');
            localStorage.removeItem('a11yHC');
        });

        // Narrator (speech synthesis) â€” read selected text when enabled
        const narratorTile = document.getElementById('feat-narrator');
        let narratorOn = localStorage.getItem('a11y_narrator') === '1';
        if (narratorTile) narratorTile.setAttribute('aria-pressed', narratorOn ? 'true' : 'false');

        const speak = (text) => {
            if (!('speechSynthesis' in window)) return;
            try {
                window.speechSynthesis.cancel();
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'id-ID';
                u.rate = 1;
                u.pitch = 1.05; // slightly higher, more natural female
                const pickVoice = () => {
                    const voices = window.speechSynthesis.getVoices() || [];
                    const byLang = (v, code) => (v.lang || '').toLowerCase().startsWith(code);
                    const isFemaleName = (name) => /(female|woman|wanita|perempuan|gadis|siti|lia|nia|a$)/i.test(name || '');
                    const isNatural = (name) => /(natural|neural|wavenet|online)/i.test(name || '');
                    const preferScore = (v) => {
                        let s = 0;
                        const name = (v.name || '');
                        if (byLang(v, 'id')) s += 50;
                        if (byLang(v, 'en')) s += 5;
                        if (/google/i.test(name)) s += 15;
                        if (/microsoft/i.test(name)) s += 14;
                        if (isFemaleName(name)) s += 20;
                        if (isNatural(name)) s += 10;
                        return s;
                    };
                    let chosen = null;
                    if (voices.length) {
                        chosen = voices.slice().sort((a,b) => preferScore(b) - preferScore(a))[0];
                    }
                    if (chosen) u.voice = chosen;
                    window.speechSynthesis.speak(u);
                };
                if ((window.speechSynthesis.getVoices() || []).length) pickVoice();
                else window.speechSynthesis.onvoiceschanged = pickVoice;
            } catch(e) { /* ignore */ }
        };

        let narrTimer = null;
        const isInsidePanels = (node) => {
            if (!node) return false;
            let el = node.nodeType === 1 ? node : node.parentElement;
            if (!el) return false;
            return !!(el.closest('#a11yPanel') || el.closest('#whatsappPanel'));
        };
        const handleSelectionSpeak = () => {
            if (!narratorOn) return;
            const sel = window.getSelection && window.getSelection();
            if (!sel || sel.isCollapsed) return;
            if (isInsidePanels(sel.anchorNode) || isInsidePanels(sel.focusNode)) return;
            const text = String(sel.toString() || '').trim();
            if (text.length < 2) return;
            speak(text.length > 800 ? text.slice(0, 800) : text);
        };
        const scheduleSpeak = () => {
            if (narrTimer) clearTimeout(narrTimer);
            narrTimer = setTimeout(handleSelectionSpeak, 120);
        };
        document.addEventListener('mouseup', scheduleSpeak);
        document.addEventListener('keyup', (e) => {
            if (e.key && ['Shift','Control','Alt','Meta'].includes(e.key)) return;
            scheduleSpeak();
        });
        document.addEventListener('selectionchange', () => {
            if (!narratorOn) return;
            const sel = window.getSelection && window.getSelection();
            if (!sel || sel.isCollapsed) {
                try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch(e) {}
                return;
            }
            scheduleSpeak();
        });

        if (narratorTile) narratorTile.addEventListener('click', () => {
            narratorOn = !narratorOn;
            localStorage.setItem('a11y_narrator', narratorOn ? '1' : '0');
            narratorTile.setAttribute('aria-pressed', narratorOn ? 'true' : 'false');
            if (!narratorOn && 'speechSynthesis' in window) window.speechSynthesis.cancel();
        });
    }
    // Initialize CSS donut for Penduduk
    const donut = document.getElementById('donutPenduduk');
    if (donut) {
        // Util: ambil angka pertama (mendukung "1,637 (51%)" atau "1.637")
        const firstNumber = (text) => {
            const m = (text || '').match(/[\d.,]+/);
            if (!m) return 0;
            return Number(m[0].replace(/[^\d]/g, '')) || 0;
        };

        const maleStrong = document.querySelector('#cardPenduduk .stat-detail-item[data-seg="primary"] strong');
        const femaleStrong = document.querySelector('#cardPenduduk .stat-detail-item[data-seg="secondary"] strong');
        const male = firstNumber(maleStrong?.textContent || '');
        const female = firstNumber(femaleStrong?.textContent || '');
        const total = male + female;

        // Fallback ke data attribute jika parsing gagal
        let p1 = Number(donut.dataset.p1);
        if (total > 0) {
            p1 = Math.round((male / total) * 100);

            // Tampilkan total di tengah (format ribuan dengan koma sesuai contoh)
            const totalEl = document.querySelector('#cardPenduduk .donut-center .total-number');
            if (totalEl) totalEl.textContent = total.toLocaleString('id-ID');

            // Perbaharui persentase di baris detail bila ada
            const p2 = 100 - p1;
            if (maleStrong) maleStrong.textContent = `${male.toLocaleString('id-ID')} (${p1}%)`;
            if (femaleStrong) femaleStrong.textContent = `${female.toLocaleString('id-ID')} (${p2}%)`;
        } else if (!isFinite(p1)) {
            p1 = 51;
        }

        const p2final = isFinite(p1) ? Math.max(0, 100 - p1) : 0;
        donut.style.setProperty('--p1', p1);
        donut.style.setProperty('--p2', p2final);

        // Interaksi: highlight saat hover item detail
        const card = document.getElementById('cardPenduduk');
        if (card) {
            const items = card.querySelectorAll('.stat-detail-item');
            items.forEach((it) => {
                it.addEventListener('mouseenter', () => {
                    donut.classList.toggle('focus-primary', it.dataset.seg === 'primary');
                    donut.classList.toggle('focus-secondary', it.dataset.seg === 'secondary');
                });
                it.addEventListener('mouseleave', () => {
                    donut.classList.remove('focus-primary');
                    donut.classList.remove('focus-secondary');
                });
                it.addEventListener('click', () => {
                    // Toggle lock focus on click
                    if (it.dataset.seg === 'primary') {
                        donut.classList.toggle('focus-primary');
                        donut.classList.remove('focus-secondary');
                    } else {
                        donut.classList.toggle('focus-secondary');
                        donut.classList.remove('focus-primary');
                    }
                });
            });
        }
    }

    // Auto-highlight current day in Jam Kerja inside Informasi Publik card
    (function highlightToday(){
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const todayName = days[new Date().getDay()];
        const list = document.querySelector('#informasi-publik .jam-list');
        if (!list) return;
        list.querySelectorAll('.jam-item').forEach(li => li.classList.remove('is-today'));
        // find by text content of day span
        const items = list.querySelectorAll('.jam-item');
        items.forEach(li => {
            const dayEl = li.querySelector('.day');
            if (dayEl && dayEl.textContent.trim().toLowerCase() === todayName.toLowerCase()) {
                li.classList.add('is-today');
            }
        });
    })();

    // Info Publik: banner slider inside card
    let ipIndex = 0;
    let ipTimer = null;
    const ipSlider = document.getElementById('ipSlider');
    const ipTrack = document.getElementById('ipSlides');
    function renderIp(){ if(!ipTrack) return; const items = ipTrack.querySelectorAll('.ip-item'); if(!items.length) return; ipIndex = (ipIndex + items.length) % items.length; ipTrack.style.transform = `translateX(-${ipIndex*100}%)`; }
    window.changeIpSlide = function(delta){ const items = ipTrack?.querySelectorAll('.ip-item') || []; if(!items.length) return; ipIndex += delta; if(ipIndex<0) ipIndex = items.length-1; if(ipIndex>=items.length) ipIndex = 0; renderIp(); restartIpAuto(); }
    function startIpAuto(){ stopIpAuto(); ipTimer = setInterval(()=>{ ipIndex++; renderIp(); }, 5000); }
    function stopIpAuto(){ if(ipTimer){ clearInterval(ipTimer); ipTimer=null; } }
    function restartIpAuto(){ stopIpAuto(); startIpAuto(); }
    if(ipSlider && ipTrack){ startIpAuto(); ipSlider.addEventListener('mouseenter', stopIpAuto); ipSlider.addEventListener('mouseleave', startIpAuto); }

    // Inject three-dots menu to each stat card
    const statCards = document.querySelectorAll('.statistics-dashboard .stat-card');
    const linksMap = {
        'penduduk': '#kependudukan',
        'data wilayah': '#statistik',
        'pekerjaan': '#statistik',
        'pendidikan': '#statistik',
        'agama': '#statistik',
        'status perkawinan': '#statistik',
        'usia': '#statistik',
        'golongan darah': '#statistik'
    };

    statCards.forEach((card, idx) => {
        if (card.querySelector('.card-menu-btn')) return;
        const titleEl = card.querySelector('.stat-card-title');
        const title = (titleEl?.textContent || '').trim().toLowerCase();
        const link = linksMap[title] || '#';

        const btn = document.createElement('button');
        btn.className = 'card-menu-btn';
        btn.setAttribute('aria-haspopup', 'true');
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<i class="fas fa-ellipsis-h"></i>';

        // Wrap title + button in a header row so they align
        if (titleEl) {
            let top = document.createElement('div');
            top.className = 'stat-card-top';
            titleEl.parentNode.insertBefore(top, titleEl);
            top.appendChild(titleEl);
            top.appendChild(btn);
        } else {
            // Fallback: append button at start
            card.insertBefore(btn, card.firstChild);
        }

        const menu = document.createElement('div');
        menu.className = 'card-menu';
        menu.innerHTML = `
            <a class="card-menu-item" href="${link}">Lihat Detail</a>
        `;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = menu.classList.contains('open');
            document.querySelectorAll('.card-menu').forEach(m => m.classList.remove('open'));
            menu.classList.toggle('open', !isOpen);
            btn.setAttribute('aria-expanded', String(!isOpen));
        });

        // close on outside click
        document.addEventListener('click', () => menu.classList.remove('open'));
        document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') menu.classList.remove('open');});

        card.appendChild(menu);
    });
});
// Top-left Clock & Weather + Dark Mode
document.addEventListener('DOMContentLoaded', () => {
  // Clock
  const clk = document.getElementById('tliClock');
  const dte = document.getElementById('tliDate');
  const fmt2 = (n) => String(n).padStart(2, '0');
  const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  function tickClock(){
    const now = new Date();
    const hh = fmt2(now.getHours());
    const mm = fmt2(now.getMinutes());
    const ss = fmt2(now.getSeconds());
    if (clk) clk.textContent = `${hh}:${mm}:${ss}`;
    if (dte) dte.textContent = `${days[now.getDay()]}, ${fmt2(now.getDate())} ${months[now.getMonth()]} ${now.getFullYear()}`;
  }
  tickClock();
  setInterval(tickClock, 1000);

  // Weather via Open-Meteo
  const wEl = document.getElementById('tliWeather');
  const wIcon = wEl?.querySelector('.tli-weather-icon');
  const wText = wEl?.querySelector('.tli-weather-text');
  const tliWrap = document.getElementById('topLeftInfo');
  const codeToIcon = (code, isDay) => {
    // Simplified mapping
    if ([0].includes(code)) return isDay ? 'fa-sun' : 'fa-moon';
    if ([1,2,3].includes(code)) return 'fa-cloud';
    if ([45,48].includes(code)) return 'fa-smog';
    if ([51,53,55,61,63,65,80,81,82].includes(code)) return 'fa-cloud-rain';
    if ([56,57,66,67,71,73,75,77,85,86].includes(code)) return 'fa-snowflake';
    if ([95,96,99].includes(code)) return 'fa-bolt';
    return 'fa-cloud';
  };
  function themeFrom(code, isDay){
    if (code === 0 || code === 1) return isDay ? 'sunny' : 'night';
    if ([2].includes(code)) return isDay ? 'partly' : 'night';
    if ([3,45,48].includes(code)) return 'cloudy';
    if ([51,53,55,61,63,65,80,81,82,95,96,99].includes(code)) return 'rain';
    if ([56,57,66,67,71,73,75,77,85,86].includes(code)) return 'snow';
    return isDay ? 'partly' : 'night';
  }
  let lastTheme = '';
  const applyTliTheme = (theme) => {
    if (!tliWrap) return;
    const list = ['sunny','cloudy','partly','rain','night','snow'];
    list.forEach(t => tliWrap.classList.remove('tli--' + t));
    tliWrap.classList.add('tli--' + theme);
    lastTheme = theme;
  };

  async function loadWeather(){
    if (!wEl) return;
    const lat = parseFloat(wEl.dataset.latitude || '-5.257');
    const lon = parseFloat(wEl.dataset.longitude || '105.465');
    const tz = encodeURIComponent(wEl.dataset.timezone || 'Asia/Jakarta');
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code,is_day,wind_speed_10m&timezone=${tz}`;
    try {
      const res = await fetch(url, { cache: 'no-store' });
      const data = await res.json();
      const c = data?.current || {};
      const temp = (typeof c.temperature_2m === 'number') ? Math.round(c.temperature_2m) : null;
      const code = c.weather_code;
      const isDay = c.is_day === 1;
      if (wIcon) { wIcon.className = `tli-weather-icon fas ${codeToIcon(code, isDay)}`; }
      if (wText) {
        const t = temp !== null ? `${temp}Â°C` : 'â€”';
        wText.textContent = `${t}`;
      }
      const th = themeFrom(code, isDay);
      if (th !== lastTheme) applyTliTheme(th);
    } catch(e) {
      if (wText) wText.textContent = 'Gagal memuat cuaca';
    }
  }
  loadWeather();
  setInterval(loadWeather, 10 * 60 * 1000); // refresh per 10 menit

  // Dark Mode Toggle
  const dmBtn = document.getElementById('darkModeBtn');
  const applyDark = (on) => {
    document.body.classList.toggle('dark-mode', !!on);
    if (dmBtn) {
      const i = dmBtn.querySelector('i');
      if (i) i.className = `fas ${on ? 'fa-sun' : 'fa-moon'}`;
      dmBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
    }
  };
  let darkOn = localStorage.getItem('dark_mode') === '1';
  applyDark(darkOn);
  if (dmBtn) dmBtn.addEventListener('click', () => {
    darkOn = !darkOn;
    localStorage.setItem('dark_mode', darkOn ? '1' : '0');
    applyDark(darkOn);
  });

  // Admin login modal (hidden trigger on clock)


  const clockBtn = document.getElementById('tliClock');
  const adminModal = document.getElementById('adminLoginModal');
  const adminClose = document.getElementById('adminClose');
  const adminPassInput = document.getElementById('adminPass');
  const adminPassToggle = document.getElementById('adminPassToggle');
  const adminLocationLabel = document.getElementById('adminLocationLabel');
  const adminPassChecklist = document.getElementById('adminPassChecklist');
  const adminLoginForm = document.getElementById('adminLoginForm');
  const enforcePasswordPolicy = adminLoginForm?.dataset.enforcePolicy !== 'false';
  const adminSubmitBtn = adminLoginForm?.querySelector('.admin-submit');
  const shouldAutoOpenAdmin = adminModal?.dataset.showOnLoad === 'true';

  const adminPasswordRules = {
    length: (value) => value.length >= 8,
    'upper-lower': (value) => /[A-Z]/.test(value) && /[a-z]/.test(value),
    number: (value) => /\d/.test(value),
    symbol: (value) => /[^A-Za-z0-9]/.test(value),
  };

  const refreshAdminPassChecklist = (value) => {
    if (!adminPassChecklist) return true;
    const items = adminPassChecklist.querySelectorAll('li[data-rule]');
    if (!items.length) return true;
    let everyRulePassed = true;
    items.forEach((item) => {
      const ruleName = item.dataset.rule;
      const validator = adminPasswordRules[ruleName];
      const passed = validator ? validator(value) : false;
      if (!item.hasAttribute('role')) item.setAttribute('role', 'checkbox');
      item.setAttribute('aria-checked', passed ? 'true' : 'false');
      item.classList.toggle('valid', !!passed);
      const icon = item.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-circle', !passed);
        icon.classList.toggle('fa-check', passed);
      }
      everyRulePassed = everyRulePassed && passed;
    });
    return everyRulePassed;
  };

  const syncAdminPasswordState = (value = '') => {
    if (!enforcePasswordPolicy) {
      refreshAdminPassChecklist(value);
      if (adminPassInput) {
        adminPassInput.setCustomValidity('');
        adminPassInput.setAttribute('aria-invalid', 'false');
      }
      if (adminSubmitBtn) {
        adminSubmitBtn.disabled = false;
        adminSubmitBtn.setAttribute('aria-disabled', 'false');
      }
      return true;
    }

    const checklistOk = refreshAdminPassChecklist(value);
    const hasValue = value.length > 0;
    const isValid = hasValue && checklistOk;
    if (adminPassInput) {
      adminPassInput.setAttribute('aria-invalid', isValid || !hasValue ? 'false' : 'true');
      if (!isValid && hasValue) {
        adminPassInput.setCustomValidity('Password belum memenuhi persyaratan keamanan.');
      } else {
        adminPassInput.setCustomValidity('');
      }
    }
    if (adminSubmitBtn) {
      adminSubmitBtn.disabled = !isValid;
      adminSubmitBtn.setAttribute('aria-disabled', !isValid ? 'true' : 'false');
    }
    return isValid;
  };

  const setLocationLabel = (label) => {
    if (adminLocationLabel && label) adminLocationLabel.textContent = label;
  };

  const deriveTimeZoneLocation = () => {
    try {
      const tz = Intl.DateTimeFormat().resolvedOptions()?.timeZone;
      if (!tz) return null;
      const parts = tz.split('/');
      if (parts.length < 2) return tz.replace(/_/g, ' ');
      const city = parts.pop()?.replace(/_/g, ' ');
      const region = parts.join(' / ').replace(/_/g, ' ');
      if (!region || region === 'Etc' || region === 'GMT') return city;
      if (region === 'Asia') return city;
      return `${city}, ${region}`;
    } catch (err) {
      return null;
    }
  };

  const cleanValue = (value) => value?.replace(/_/g, ' ').replace(/\s+(Regency|Regencies|City|District)$/i, '').trim();

  const pickField = (address, keys) => {
    for (const key of keys) {
      const value = address?.[key];
      if (value) return cleanValue(value);
    }
    return null;
  };

  const ensureLabel = (value, label, knownPrefixes = []) => {
    if (!value) return null;
    const normalized = cleanValue(value);
    if (!normalized) return null;
    const lower = normalized.toLowerCase();
    if (knownPrefixes.some((prefix) => lower.startsWith(prefix.toLowerCase()))) {
      return normalized;
    }
    return `${label} ${normalized}`;
  };

  const formatAddressHierarchy = (address) => {
    if (!address) return null;
    const desa = pickField(address, ['village', 'hamlet', 'locality', 'neighbourhood', 'suburb']);
    const kecamatan = pickField(address, ['city_district', 'district', 'state_district', 'suburb']);
    const kabupaten = pickField(address, ['county', 'municipality', 'city']);
    const provinsi = pickField(address, ['state', 'region']);
    const negara = pickField(address, ['country']);

    const ordered = [];
    const pushUnique = (label) => {
      if (label && !ordered.includes(label)) ordered.push(label);
    };

    pushUnique(ensureLabel(desa, 'Desa', ['desa', 'kelurahan', 'kampung', 'dusun']));
    pushUnique(ensureLabel(kecamatan, 'Kecamatan', ['kecamatan']));
    pushUnique(ensureLabel(kabupaten, 'Kabupaten', ['kabupaten', 'kota']));
    pushUnique(ensureLabel(provinsi, 'Provinsi', ['provinsi']));
    pushUnique(negara);

    return ordered.length ? ordered.join(', ') : null;
  };

  const reverseGeocode = async (lat, lon) => {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&accept-language=id`;
    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!response.ok) return null;
    const data = await response.json();
    return formatAddressHierarchy(data.address);
  };

  const updateLocationFromCoordinates = async (lat, lon) => {
    try {
      const formatted = await reverseGeocode(lat, lon);
      if (formatted) {
        currentLocationLabel = formatted;
        setLocationLabel(currentLocationLabel);
        return;
      }
    } catch (error) {
      console.warn('Reverse geocode gagal', error);
    }
    const fallback = `Koordinat ${lat.toFixed(3)}?, ${lon.toFixed(3)}?`;
    currentLocationLabel = fallback;
    setLocationLabel(currentLocationLabel);
  };

  let currentLocationLabel = deriveTimeZoneLocation() || 'Lokasi Anda';
  setLocationLabel(currentLocationLabel);

  let requestedAdminGeolocation = false;
  const requestAdminGeolocation = () => {
    if (requestedAdminGeolocation || !adminLocationLabel || !('geolocation' in navigator)) return;
    requestedAdminGeolocation = true;
    navigator.geolocation.getCurrentPosition(async ({ coords }) => {
      await updateLocationFromCoordinates(coords.latitude, coords.longitude);
    }, () => {}, { maximumAge: 900000, timeout: 5000 });
  };

  const applyPasswordVisibility = (show) => {
    if (!adminPassInput) return;
    adminPassInput.type = show ? 'text' : 'password';
    if (adminPassToggle) {
      adminPassToggle.setAttribute('aria-pressed', show ? 'true' : 'false');
      adminPassToggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      const icon = adminPassToggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
      }
    }
  };

  const openAdmin = () => {
    if (window.isUserAuthenticated) {
      window.location.href = '/admin/dashboard';
      return;
    }
    if (!adminModal) return;
    adminModal.classList.add('open');
    adminModal.setAttribute('aria-hidden', 'false');
    requestAdminGeolocation();
  };
  const closeAdmin = () => {
    if (adminModal) {
      adminModal.classList.remove('open');
      adminModal.setAttribute('aria-hidden', 'true');
    }
    applyPasswordVisibility(false);
    setLocationLabel(currentLocationLabel);
  };

  if (clockBtn && adminModal) {
    clockBtn.addEventListener('click', (e) => { e.stopPropagation(); openAdmin(); });
    adminModal.addEventListener('click', (e) => {
      const overlay = e.target.closest('.admin-modal-overlay');
      if (overlay) closeAdmin();
    });
    if (adminClose) adminClose.addEventListener('click', closeAdmin);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && adminModal.classList.contains('open')) closeAdmin(); });
  }

  if (adminPassInput) {
    const handleAdminPasswordInput = () => syncAdminPasswordState(adminPassInput.value);
    handleAdminPasswordInput();
    adminPassInput.addEventListener('input', handleAdminPasswordInput);
    adminPassInput.addEventListener('change', handleAdminPasswordInput);
  } else if (adminSubmitBtn) {
    adminSubmitBtn.disabled = false;
    adminSubmitBtn.setAttribute('aria-disabled', 'false');
  }

  if (adminLoginForm && enforcePasswordPolicy) {
    adminLoginForm.addEventListener('submit', (event) => {
      const valid = syncAdminPasswordState(adminPassInput?.value || '');
      if (!valid) {
        event.preventDefault();
        if (adminPassInput?.reportValidity) adminPassInput.reportValidity();
      }
    });
  }

  if (adminPassInput && adminPassToggle) {
    applyPasswordVisibility(false);
    adminPassToggle.addEventListener('click', () => {
      const shouldShow = adminPassInput.type === 'password';
      applyPasswordVisibility(shouldShow);
    });
  }

  if (shouldAutoOpenAdmin) {
    openAdmin();
  }
});

// Pegawai runner: pause animation while user scrolls or hovers
document.addEventListener('DOMContentLoaded', () => {
    const runner = document.querySelector('.pegawai-runner');
    const track = runner?.querySelector('.pegawai-track');
    if (!runner || !track) return;

    // Pause/resume helpers
    let pauseTimer = null;
    const pause = () => track.classList.add('paused');
    const resume = () => track.classList.remove('paused');

    // Continuous loop when manually scrolled
    let loopWidth = 0;
    const computeLoopWidth = () => {
        // Asumsikan konten diduplikasi 2x di HTML
        loopWidth = Math.floor(track.scrollWidth / 2);
    };
    computeLoopWidth();
    window.addEventListener('resize', computeLoopWidth);
    window.addEventListener('load', computeLoopWidth);

    // Small offset to avoid sticking at 0
    if (runner.scrollLeft === 0) runner.scrollLeft = 1;

    runner.addEventListener('scroll', () => {
        // Seamless wrap
        if (loopWidth > 0) {
            if (runner.scrollLeft >= loopWidth) runner.scrollLeft -= loopWidth;
            else if (runner.scrollLeft <= 0) runner.scrollLeft += loopWidth;
        }

        // Pause auto animation during interaction
        pause();
        if (pauseTimer) clearTimeout(pauseTimer);
        pauseTimer = setTimeout(resume, 1500);
    }, { passive: true });

    runner.addEventListener('mouseenter', pause);
    runner.addEventListener('mouseleave', resume);
});

// Pegawai modal preview (home + pegawai page)
document.addEventListener('DOMContentLoaded', () => {
    const statusClassMap = {
        'aktif': 'status-aktif',
        'tidak aktif': 'status-tidak-aktif',
        'nonaktif': 'status-tidak-aktif',
        'non-aktif': 'status-tidak-aktif',
        'cuti': 'status-cuti',
        'pensiun': 'status-pensiun'
    };

    const ensureModal = () => {
        let modal = document.getElementById('pegawaiModal');
        if (modal) return modal;
        modal = document.createElement('div');
        modal.id = 'pegawaiModal';
        modal.className = 'pegawai-modal';
        modal.innerHTML = `
            <div class="pegawai-modal__backdrop" data-pegawai-close></div>
            <div class="pegawai-modal__dialog">
                <button class="pegawai-modal__close" type="button" data-pegawai-close aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
                <div class="pegawai-modal__img-container">
                    <img class="pegawai-modal__img" src="" alt="Foto Pegawai">
                </div>
                <div class="pegawai-modal__body">
                    <h3 class="pegawai-modal__name"></h3>
                    <div class="pegawai-modal__meta">
                        <div class="pegawai-modal__info-item">
                            <span class="pegawai-modal__label">Jabatan</span>
                            <span class="pegawai-modal__role"></span>
                        </div>
                        <div class="pegawai-modal__info-item">
                            <span class="pegawai-modal__label">Status</span>
                            <span class="pegawai-modal__status"></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    };

    const modal = ensureModal();
    const imgEl = modal.querySelector('.pegawai-modal__img');
    const nameEl = modal.querySelector('.pegawai-modal__name');
    const roleEl = modal.querySelector('.pegawai-modal__role');
    const statusEl = modal.querySelector('.pegawai-modal__status');

    const closeModal = () => modal.classList.remove('open');
    modal.querySelectorAll('[data-pegawai-close]').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    const applyStatusClass = (text) => {
        const st = (text || '').toLowerCase().trim();
        statusEl.classList.remove('status-aktif','status-tidak-aktif','status-cuti','status-pensiun');
        const cls = statusClassMap[st];
        if (cls) statusEl.classList.add(cls);
    };

    const cards = Array.from(document.querySelectorAll('.pegawai-card'));
    cards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            const img = card.querySelector('img')?.getAttribute('src') || '';
            const name = card.querySelector('.pegawai-name')?.textContent?.trim() || 'Pegawai Desa';
            const role = card.querySelector('.pegawai-role')?.textContent?.trim() || 'Perangkat Desa';
            // Try both old and new status classes
            const status = (card.querySelector('.pegawai-status-badge') || card.querySelector('.pegawai-status'))?.textContent?.trim() || 'Status';

            if (imgEl) imgEl.src = img;
            if (nameEl) nameEl.textContent = name;
            if (roleEl) roleEl.textContent = role;
            if (statusEl) {
                statusEl.textContent = status;
                applyStatusClass(status);
            }
            modal.classList.add('open');
        });
    });
});

// Search functionality
function toggleSearch() {
    const searchBar = document.getElementById('searchBar');
    const searchBarInput = document.getElementById('searchBarInput');
    const hero = document.querySelector('.hero');

    // Check if searchBar exists before proceeding
    if (!searchBar) {
        console.warn('Search bar element not found');
        return;
    }

    searchBar.classList.toggle('active');

    if (searchBar.classList.contains('active')) {
        if (searchBarInput) {
            searchBarInput.focus();
        }
        // Adjust margin only if hero element exists
        if (hero) {
            if (window.innerWidth <= 768) {
                hero.style.marginTop = '120px';
            } else {
                hero.style.marginTop = '130px';
            }
        }
    } else {
        if (searchBarInput) {
            searchBarInput.value = '';
        }
        if (hero) {
            hero.style.marginTop = '70px';
        }
    }
}

function performSearchBar() {
    const searchBarInput = document.getElementById('searchBarInput');
    const query = searchBarInput.value.trim();

    if (query) {
        // Redirect to news page with search query
        window.location.href = `/news?q=${encodeURIComponent(query)}`;
    } else {
        // If empty, just go to news page
        window.location.href = '/news';
    }
}



// Mobile menu functionality
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn i');

    // Check if elements exist before manipulating them
    if (!mobileMenu) {
        console.warn('Mobile menu element not found');
        return;
    }

    mobileMenu.classList.toggle('active');
    mobileMenu.style.display = mobileMenu.classList.contains('active') ? 'block' : 'none';

    // Change icon
    if (mobileMenuBtn) {
        if (mobileMenu.classList.contains('active')) {
            mobileMenuBtn.className = 'fas fa-times';
        } else {
            mobileMenuBtn.className = 'fas fa-bars';
        }
    }

    // Close all submenus when closing mobile menu
    if (!mobileMenu.classList.contains('active')) {
        document.querySelectorAll('.mobile-submenu').forEach(submenu => {
            submenu.classList.remove('active');
        });
        document.querySelectorAll('.mobile-menu a[onclick*="toggleMobileSubmenu"]').forEach(link => {
            link.classList.remove('active');
        });
    }
}

// Mobile submenu functionality
function toggleMobileSubmenu(evt, menuId) {
    evt.preventDefault(); // Prevent default link behavior
    const submenu = document.getElementById(menuId + '-submenu');
    const menuLink = evt.target.closest('a');
    const allSubmenus = document.querySelectorAll('.mobile-submenu');
    const allMenuLinks = document.querySelectorAll('.mobile-menu a[onclick*="toggleMobileSubmenu"]');

    // Close all other submenus and remove active class from other links
    allSubmenus.forEach(menu => {
        if (menu !== submenu) {
            menu.classList.remove('active');
        }
    });

    allMenuLinks.forEach(link => {
        if (link !== menuLink) {
            link.classList.remove('active');
        }
    });

    // Toggle current submenu and link
    submenu.classList.toggle('active');
    menuLink.classList.toggle('active');
}

// Close search when clicking outside
document.addEventListener('click', function (event) {
    const searchContainer = document.querySelector('.search-container');
    const searchBar = document.getElementById('searchBar');
    const hero = document.querySelector('.hero');
    const searchInput = document.getElementById('searchBarInput');

    if (!searchContainer || !searchBar || !hero || !searchInput) return;

    if (!searchContainer.contains(event.target) && !searchBar.contains(event.target)) {
        searchBar.classList.remove('active');
        searchInput.value = '';
        hero.style.marginTop = '70px';
    }
});

// Handle window resize for responsive search bar
window.addEventListener('resize', function () {
    const searchBar = document.getElementById('searchBar');
    const hero = document.querySelector('.hero');

    if (!searchBar || !hero) return;

    if (searchBar.classList.contains('active')) {
        hero.style.marginTop = window.innerWidth <= 768 ? '120px' : '130px';
    }
});

// Search on Enter key
const searchBarInput = document.getElementById('searchBarInput');
if (searchBarInput) {
    searchBarInput.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            performSearchBar();
        }
    });
}



// Close mobile menu when clicking on a link (only for non-submenu links)
const mobileMenuLinks = document.querySelectorAll('.mobile-menu a');
if (mobileMenuLinks && mobileMenuLinks.length) {
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Check if this is a submenu toggle link
            const hasSubmenu = this.getAttribute('onclick') && this.getAttribute('onclick')
                .includes('toggleMobileSubmenu');

            if (!hasSubmenu) {
                const mobileMenu = document.getElementById('mobileMenu');
                const mobileMenuBtn = document.querySelector('.mobile-menu-btn i');

                if (mobileMenu && mobileMenuBtn) {
                    mobileMenu.classList.remove('active');
                    mobileMenu.style.display = 'none';
                    mobileMenuBtn.className = 'fas fa-bars';
                }
            }
        });
    });
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (!href) {
            return;
        }
        if (href === '#') {
            e.preventDefault();
            return;
        }
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Header scroll effect
window.addEventListener('scroll', function () {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.style.background = 'rgba(255, 255, 255, 0.95)';
        header.style.backdropFilter = 'blur(10px)';
    } else {
        header.style.background = 'white';
        header.style.backdropFilter = 'none';
    }
});


// Add click animations to service cards
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', function () {
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = '';
        }, 150);
    });
});

// Add click animations to news cards
document.querySelectorAll('.news-card').forEach(card => {
    card.addEventListener('click', function () {
        this.style.transform = 'scale(0.98)';
        setTimeout(() => {
            this.style.transform = '';
        }, 150);
    });
});

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.service-card, .news-card, .stat-item, .stat-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// Animated number counters for population stats
(function initCounters() {
    const counters = document.querySelectorAll('.stat-number[data-target]');
    if (!counters.length) return;

    const nf = new Intl.NumberFormat('id-ID');

    const animateValue = (el, to, duration = 1600) => {
        let startTime = null;
        const step = (ts) => {
            if (!startTime) startTime = ts;
            const progress = Math.min((ts - startTime) / duration, 1);
            const value = Math.floor(progress * to);
            el.textContent = nf.format(value);
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-target'), 10) || 0;
                animateValue(el, target);
                io.unobserve(el);
            }
        });
    }, { threshold: 0.4 });

    counters.forEach((el) => {
        el.textContent = '0';
        io.observe(el);
    });
})();

// Statistics Dashboard Interactions
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('click', function () {
        this.style.transform = 'scale(0.98)';
        setTimeout(() => {
            this.style.transform = '';
        }, 150);
    });
});



// Smooth scroll for statistics dashboard
const dashboardContainer = document.querySelector('.dashboard-container');
if (dashboardContainer) {
    let isDown = false;
    let startX;
    let scrollLeft;

    dashboardContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        dashboardContainer.style.cursor = 'grabbing';
        startX = e.pageX - dashboardContainer.offsetLeft;
        scrollLeft = dashboardContainer.scrollLeft;
    });

    dashboardContainer.addEventListener('mouseleave', () => {
        isDown = false;
        dashboardContainer.style.cursor = 'grab';
    });

    dashboardContainer.addEventListener('mouseup', () => {
        isDown = false;
        dashboardContainer.style.cursor = 'grab';
    });

    dashboardContainer.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - dashboardContainer.offsetLeft;
        const walk = (x - startX) * 2;
        dashboardContainer.scrollLeft = scrollLeft - walk;
    });
}


// Plugin untuk tulis icon + total di tengah chart (hanya jika Chart tersedia)
(() => {
  if (typeof Chart === 'undefined' || typeof ChartDataLabels === 'undefined') return;

  const statData = window.__STAT_DATA__ || {};
  const defaultPalette = ["#2162E2", "#4dabf7", "#9b59b6", "#27ae60", "#f39c12", "#e74c3c", "#10b981", "#6366f1", "#06b6d4", "#f59e0b"];

  const pickColors = (base, count) => {
    const palette = Array.isArray(base) && base.length ? base : defaultPalette;
    let colors = [];
    while (colors.length < count) {
      colors = colors.concat(palette);
    }
    return colors.slice(0, count);
  };

  const clampDataset = (dataset, maxItems = 8, aggregateLabel = 'Lainnya') => {
    const labels = Array.isArray(dataset?.labels) ? dataset.labels : [];
    const data = Array.isArray(dataset?.data) ? dataset.data.map((v) => Number(v) || 0) : [];
    const pairs = labels.map((label, idx) => ({
      label: label ?? 'Tidak diketahui',
      value: data[idx] ?? 0,
    })).filter((p) => p.value >= 0);

    const sorted = pairs.sort((a, b) => b.value - a.value);
    const limit = Math.max(1, maxItems - 1);
    const top = sorted.slice(0, limit);
    const rest = sorted.slice(limit);

    if (rest.length) {
      const restTotal = rest.reduce((sum, p) => sum + p.value, 0);
      top.push({ label: aggregateLabel, value: restTotal });
    }

    return {
      labels: top.map((p) => p.label),
      data: top.map((p) => p.value),
    };
  };

  const centerTextPlugin = (icon, label) => ({
    id: 'centerText_' + icon,
    beforeDraw(chart) {
      const { ctx, width } = chart;
      const dataset = (chart.data.datasets[0]?.data || []).map((v) => Number(v) || 0);
      const total = dataset.reduce((a, b) => a + b, 0);

      ctx.save();
      const chartArea = chart.chartArea;
      const centerX = (chartArea.left + chartArea.right) / 2;
      const centerY = (chartArea.top + chartArea.bottom) / 2;

      // Icon
      ctx.font = "900 22px 'Font Awesome 6 Free'";
      ctx.fillStyle = "#333";
      ctx.textAlign = "center";
      ctx.fillText(icon, centerX, centerY - 25);

      // Total number
      ctx.font = "bold 22px Poppins";
      ctx.fillStyle = "#000";
      ctx.fillText(total, centerX, centerY + 5);

      // Label bawah
      ctx.font = "12px Poppins";
      ctx.fillStyle = "#666";
      ctx.fillText(label, centerX, centerY + 25);

      ctx.restore();
    }
  });

  // Reusable Donut Chart Function
  function donutChart(id, labels, data, colors, icon, label) {
    const el = document.getElementById(id);
    if (!el) return;

    const safeLabels = Array.isArray(labels) ? labels : [];
    const safeData = Array.isArray(data) ? data.map((v) => Number(v) || 0) : [];
    const total = safeData.reduce((sum, val) => sum + val, 0);

    const chartLabels = safeLabels.length ? safeLabels : ['Belum ada data'];
    const chartData = safeData.length ? safeData : [0];
    const chartColors = pickColors(Array.isArray(colors) ? colors : [], chartData.length);

    new Chart(el, {
      type: "doughnut",
      data: {
        labels: chartLabels,
        datasets: [{
          data: chartData,
          backgroundColor: chartColors,
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "70%",
        plugins: {
          legend: { 
            position: "right", 
            labels: { 
              font: { size: 12 },
              padding: 15,
              usePointStyle: true
            } 
          },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                const value = Number(ctx.raw || 0);
                const data = ctx.chart.data.datasets[0].data.map((v) => Number(v) || 0);
                const total = data.reduce((a, b) => a + b, 0);
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) + "%" : "0%";
                return `${ctx.label}: ${value}${total > 0 ? " (" + percent + ")" : ""}`;
              }
            }
          },
          datalabels: {
            color: "#fff",
            formatter: (value, ctx) => {
              const data = ctx.chart.data.datasets[0].data.map((v) => Number(v) || 0);
              const total = data.reduce((a, b) => a + b, 0);
              if (total <= 0) return "";
              const percent = (value / total) * 100;
              return percent >= 5 ? percent.toFixed(1) + "%" : "";
            },
            font: { weight: "bold", size: 11 }
          }
        }
      },
      plugins: [ChartDataLabels, centerTextPlugin(icon, label)]
    });
  }

  // ==== DATA STATISTIK ====
  const stats = {
    agama: statData.agama || { labels: [], data: [] },
    pekerjaan: statData.pekerjaan || { labels: [], data: [] },
    pendidikan: statData.pendidikan || { labels: [], data: [] },
    perkawinan: statData.perkawinan || { labels: [], data: [] },
    usia: statData.usia || { labels: [], data: [] },
    golongan_darah: statData.golongan_darah || { labels: [], data: [] },
    suku: statData.suku || { labels: [], data: [] },
  };

  const chartLimits = {
    agama: 6,
    pekerjaan: 7,
    pendidikan: 6,
    perkawinan: 6,
    usia: 6,
    golongan_darah: 5,
    suku: 7,
  };

  const datasetFor = (key) => clampDataset(stats[key] || { labels: [], data: [] }, chartLimits[key] || 8);

  donutChart("chartAgama",
    datasetFor('agama').labels,
    datasetFor('agama').data,
    ["#2162E2", "#27ae60", "#e74c3c", "#f39c12", "#9b59b6", "#10b981"],
    "\uf0f0", "Agama"
  );

  donutChart("chartPekerjaan",
    datasetFor('pekerjaan').labels,
    datasetFor('pekerjaan').data,
    ["#2162E2", "#4dabf7", "#9b59b6", "#27ae60", "#f39c12", "#e74c3c"],
    "\uf0b1", "Pekerjaan"
  );

  donutChart("chartPendidikan",
    datasetFor('pendidikan').labels,
    datasetFor('pendidikan').data,
    ["#2162E2", "#4dabf7", "#9b59b6", "#27ae60", "#f39c12", "#e74c3c"],
    "\uf19d", "Pendidikan"
  );

  donutChart("chartPerkawinan",
    datasetFor('perkawinan').labels,
    datasetFor('perkawinan').data,
    ["#2162E2", "#4dabf7", "#9b59b6", "#27ae60"],
    "\uf004", "Perkawinan"
  );

  donutChart("chartUsia",
    datasetFor('usia').labels,
    datasetFor('usia').data,
    ["#2162E2", "#4dabf7", "#9b59b6", "#27ae60", "#f39c12"],
    "\uf1fd", "Usia"
  );

  donutChart("chartDarah",
    datasetFor('golongan_darah').labels,
    datasetFor('golongan_darah').data,
    ["#2162E2", "#4dabf7", "#9b59b6", "#e74c3c"],
    "\uf043", "Golongan Darah"
  );

  donutChart("chartSuku",
    datasetFor('suku').labels,
    datasetFor('suku').data,
    ["#6366f1", "#10b981", "#f97316", "#14b8a6", "#f43f5e", "#8b5cf6"],
    "\uf0c0", "Suku"
  );

  // ==== MODE RINGKAS (progress-style) ====
  const compactContainer = document.getElementById('compactStatList');
  const compactFilters = document.getElementById('compactStatFilters');

  const renderCompactStats = (key) => {
    if (!compactContainer) return;

    const dataset = datasetFor(key);
    const colors = pickColors([], dataset.data.length);
    const total = dataset.data.reduce((a, b) => a + b, 0);

    if (!dataset.labels.length) {
      compactContainer.innerHTML = '<div class="compact-stat-empty">Belum ada data.</div>';
      return;
    }

    const rows = dataset.labels.map((label, idx) => {
      const value = dataset.data[idx] || 0;
      const percent = total > 0 ? ((value / total) * 100) : 0;
      const width = Math.min(100, percent);
      const color = colors[idx] || defaultPalette[idx % defaultPalette.length];

      return `
        <div class="compact-stat-item" data-idx="${idx}">
          <div class="compact-stat-row">
            <div class="compact-stat-label">${label}</div>
            <div class="compact-stat-value">${value.toLocaleString('id-ID')}</div>
          </div>
          <div class="compact-stat-bar">
            <div class="compact-stat-bar-fill" style="width:${width}%; background:${color};"></div>
            <span class="compact-stat-percent">${percent.toFixed(1)}%</span>
          </div>
        </div>
      `;
    });

    compactContainer.innerHTML = rows.join('');
  };

  const bindCompactFilters = () => {
    if (!compactFilters || !compactContainer) return;

    const buttons = Array.from(compactFilters.querySelectorAll('[data-stat-key]'));
    if (!buttons.length) return;

    let activeKey = buttons[0].dataset.statKey || 'pekerjaan';

    const setActive = (key) => {
      activeKey = key;
      buttons.forEach((btn) => btn.classList.toggle('active', btn.dataset.statKey === key));
      renderCompactStats(key);
    };

    buttons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const key = btn.dataset.statKey;
        if (key) setActive(key);
      });
    });

    setActive(activeKey);
  };

  bindCompactFilters();
})();


document.addEventListener('DOMContentLoaded', () => {
    const infoModal = document.getElementById('infografisModal');
    const infoImg = document.getElementById('infografisModalImg');
    const closeBtn = document.getElementById('infografisModalClose');

    if (infoModal && infoImg) {
        // Find all slides and attach click listener
        document.querySelectorAll('.infografis-slide img').forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                infoImg.src = img.src;
                infoModal.classList.add('open');
                // Ensure visibility class (if using different css framework)
                infoModal.style.visibility = 'visible'; 
                infoModal.style.opacity = '1';
                infoModal.setAttribute('aria-hidden', 'false');
            });
        });

        const close = () => {
            infoModal.classList.remove('open');
            infoModal.style.visibility = 'hidden';
            infoModal.style.opacity = '0';
            infoModal.setAttribute('aria-hidden', 'true');
        };

        if (closeBtn) closeBtn.addEventListener('click', close);
        
        infoModal.addEventListener('click', (e) => {
            if (e.target === infoModal) close();
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });
    }
});

// Statistics Chart Export and Menu Functions
function toggleCardMenu(event) {
    event.stopPropagation();
    const button = event.currentTarget;
    const dropdown = button.nextElementSibling;
    
    // Close other dropdowns
    document.querySelectorAll('.card-menu-dropdown').forEach(d => {
        if (d !== dropdown) d.classList.remove('show');
    });
    
    dropdown.classList.toggle('show');
}

function exportHomeChart(canvasId, type = 'jpg') {
    const element = document.getElementById(canvasId);
    if (!element) return;
    
    // Helper function for download
    const downloadCanvas = (canvas, filename) => {
        const link = document.createElement('a');
        if (type === 'jpg' || type === 'jpeg') {
            link.href = canvas.toDataURL('image/jpeg', 1.0);
            link.download = filename + '.jpg';
        } else if (type === 'png') {
            link.href = canvas.toDataURL('image/png');
            link.download = filename + '.png';
        }
        link.click();
    };

    const card = element.closest('.stat-card');
    let titleText = 'Statistik';
    if (card) {
        const titleEl = card.querySelector('.stat-card-title');
        if (titleEl) titleText = titleEl.innerText.trim();
    }
    
    const timestamp = new Date().toISOString().slice(0, 10);
    const filename = titleText.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '_' + timestamp;
    const padding = 20;
    const titleHeight = 40;

    // IF CANVAS: Use traditional drawImage (Faster)
    if (element.tagName === 'CANVAS') {
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        
        tempCanvas.width = element.width + (padding * 2);
        tempCanvas.height = element.height + titleHeight + (padding * 2);
        
        // Fill background
        if (type === 'jpg' || type === 'jpeg') {
            tempCtx.fillStyle = '#ffffff';
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        }
        
        // Draw Title
        tempCtx.font = 'bold 24px Poppins';
        tempCtx.fillStyle = '#333333';
        tempCtx.textAlign = 'center';
        tempCtx.textBaseline = 'middle';
        tempCtx.fillText(titleText, tempCanvas.width / 2, padding + (titleHeight / 2));
        
        // Draw Chart
        tempCtx.drawImage(element, padding, padding + titleHeight);
        
        downloadCanvas(tempCanvas, filename);
    } 
    // IF NOT CANVAS (CSS/Div): Use html2canvas
    else {
        if (typeof html2canvas === 'undefined') {
            alert('Library html2canvas belum dimuat. Mohon refresh halaman.');
            return;
        }

        const captureTarget = element.closest('.stat-card-content') || element;

        html2canvas(captureTarget, {
            scale: 2,
            backgroundColor: null
        }).then(capturedCanvas => {
            const finalCanvas = document.createElement('canvas');
            const ctx = finalCanvas.getContext('2d');

            finalCanvas.width = capturedCanvas.width + (padding * 2);
            finalCanvas.height = capturedCanvas.height + titleHeight + (padding * 2);

            // Fill background
            if (type === 'jpg' || type === 'jpeg') {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
            }

            // Draw Title
            ctx.font = 'bold 24px Poppins';
            ctx.fillStyle = '#333333';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(titleText, finalCanvas.width / 2, padding + (titleHeight / 2));

            // Draw Captured Content (Text & Legends)
            ctx.drawImage(capturedCanvas, padding, padding + titleHeight);

            // Re-draw Donut Ring Manually if it is the "Penduduk" Chart
            if (element.classList.contains('donut-css')) {
                const p1 = parseFloat(element.getAttribute('data-p1') || 0);  // Male %
                const p2 = parseFloat(element.getAttribute('data-p2') || 0);  // Female %

                const cardRect = captureTarget.getBoundingClientRect();
                const donutRect = element.getBoundingClientRect();

                // Calculate position relative to the captured canvas
                // We add padding + titleHeight because that's where we drew the capturedCanvas
                // We offset Y slightly more to ensure top isn't clipped by title/padding boundary
                const relativeX = (donutRect.left - cardRect.left) * 2 + padding; 
                const relativeY = (donutRect.top - cardRect.top) * 2 + padding + titleHeight;
                
                const diameter = donutRect.width * 2;
                const radius = diameter / 2;
                const cx = relativeX + radius;
                const cy = relativeY + radius;
                const thickness = 10 * 2; 

                // Ensure full circle by using 2*PI for the total if p1+p2 approx 100
                // or just draw the second segment to fill the rest
                const startAngle = -0.5 * Math.PI; // -90 degrees
                const angle1 = (p1 / 100) * 2 * Math.PI;
                const endAngle1 = startAngle + angle1;
                const endAngle2 = startAngle + 2 * Math.PI; // Full circle end

                // Draw Blue Ring (Male)
                ctx.beginPath();
                ctx.arc(cx, cy, radius - thickness / 2, startAngle, endAngle1);
                ctx.strokeStyle = '#2563eb'; // Blue
                ctx.lineWidth = thickness;
                ctx.lineCap = 'butt';
                ctx.stroke();

                // Draw Light Blue Ring (Female) - Remainder
                ctx.beginPath();
                ctx.arc(cx, cy, radius - thickness / 2, endAngle1, endAngle2);
                ctx.strokeStyle = '#93c5fd'; // Light Blue
                ctx.lineWidth = thickness;
                ctx.lineCap = 'butt';
                ctx.stroke();
            }

            downloadCanvas(finalCanvas, filename);
        }).catch(err => {
            console.error('Export failed:', err);
            alert('Gagal mengunduh grafik. Silakan coba lagi.');
        });
    }
}


// Global click handler to close dropdowns
document.addEventListener('click', function(event) {
    if (!event.target.closest('.card-menu-btn')) {
        document.querySelectorAll('.card-menu-dropdown').forEach(d => d.classList.remove('show'));
    }
});

// ============================================
// Global Search Bar Functions
// ============================================

/**
 * Toggle the visibility of the global search bar
 */
function toggleSearch() {
    const searchBar = document.getElementById('searchBar');
    const searchInput = document.getElementById('searchBarInput');
    
    if (!searchBar) {
        console.warn('Search bar element not found');
        return;
    }
    
    // Toggle visibility
    const isVisible = searchBar.style.display === 'block';
    
    if (isVisible) {
        // Hide search bar
        searchBar.style.display = 'none';
        searchBar.classList.remove('active');
    } else {
        // Show search bar
        searchBar.style.display = 'block';
        searchBar.classList.add('active');
        
        // Focus on input field
        if (searchInput) {
            setTimeout(() => {
                searchInput.focus();
            }, 100);
        }
    }
}

/**
 * Perform search when user presses Enter in the search bar
 */
function performSearchBar() {
    const searchInput = document.getElementById('searchBarInput');
    
    if (!searchInput) {
        console.warn('Search input element not found');
        return;
    }
    
    const query = searchInput.value.trim();
    
    if (query) {
        // Redirect to news page with search query
        window.location.href = `/news?q=${encodeURIComponent(query)}`;
    } else {
        // If empty, just go to news page
        window.location.href = '/news';
    }
}

// Close search bar when clicking outside
document.addEventListener('click', function(event) {
    const searchBar = document.getElementById('searchBar');
    const searchButton = document.querySelector('.search-icon');
    
    if (!searchBar) return;
    
    // Check if click is outside search bar and search button
    if (!searchBar.contains(event.target) && !searchButton?.contains(event.target)) {
        if (searchBar.classList.contains('active')) {
            searchBar.style.display = 'none';
            searchBar.classList.remove('active');
        }
    }
});

// Close search bar on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const searchBar = document.getElementById('searchBar');
        if (searchBar && searchBar.classList.contains('active')) {
            searchBar.style.display = 'none';
            searchBar.classList.remove('active');
        }
    }
});
