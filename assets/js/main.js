/**
 * OMAH BLUMBUNGAN — Client-Side Controller
 * Features:
 * 1. Mobile Menu Drawer & Smooth Scrolling
 * 2. Dynamic Live Countdown Timer (Asia/Makassar Timezone)
 * 3. Interactive 9-Element Balinese Architecture Detail Modal
 * 4. Fast Archive & Cultural Search Modal
 * 5. Reservation Date Validation & Blackout Protection
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // --------------------------------------------------------------------------
  // 1. Mobile Navigation Drawer
  // --------------------------------------------------------------------------
  const menuToggle = document.getElementById('menuToggle');
  const navContainer = document.getElementById('navContainer');
  const navLinks = document.querySelectorAll('.nav-link');

  if (menuToggle && navContainer) {
    menuToggle.addEventListener('click', function () {
      const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', !isExpanded);
      navContainer.classList.toggle('open');
    });

    // Close menu when clicking a link
    navLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        navContainer.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
      });
    });

    // Close menu on click outside
    document.addEventListener('click', function (e) {
      if (!navContainer.contains(e.target) && !menuToggle.contains(e.target)) {
        navContainer.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // --------------------------------------------------------------------------
  // 2. Header Scroll Effect
  // --------------------------------------------------------------------------
  const siteHeader = document.getElementById('siteHeader');
  if (siteHeader) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 30) {
        siteHeader.classList.add('scrolled');
      } else {
        siteHeader.classList.remove('scrolled');
      }
    }, { passive: true });
  }

  // --------------------------------------------------------------------------
  // 2B. Hero Background Slideshow Controller
  // --------------------------------------------------------------------------
  const heroSection = document.getElementById('heroSection');
  const heroSlides = document.querySelectorAll('.hero-slide');
  const heroDots = document.querySelectorAll('.hero-slide-dot');

  if (heroSection && heroSlides.length > 1) {
    const rawInterval = parseInt(heroSection.getAttribute('data-interval'), 10);
    const intervalMs = (!isNaN(rawInterval) && rawInterval >= 2 ? rawInterval : 5) * 1000;
    let currentSlide = 0;
    let slideTimer = null;

    function goToSlide(index) {
      if (index === currentSlide) return;
      heroSlides[currentSlide].classList.remove('active');
      if (heroDots[currentSlide]) heroDots[currentSlide].classList.remove('active');

      currentSlide = (index + heroSlides.length) % heroSlides.length;

      heroSlides[currentSlide].classList.add('active');
      if (heroDots[currentSlide]) heroDots[currentSlide].classList.add('active');
    }

    function nextSlide() {
      goToSlide(currentSlide + 1);
    }

    function startTimer() {
      if (slideTimer) clearInterval(slideTimer);
      slideTimer = setInterval(nextSlide, intervalMs);
    }

    heroDots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        const targetIdx = parseInt(dot.getAttribute('data-target-slide'), 10);
        if (!isNaN(targetIdx)) {
          goToSlide(targetIdx);
          startTimer();
        }
      });
    });

    startTimer();

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        if (slideTimer) clearInterval(slideTimer);
      } else {
        startTimer();
      }
    });
  }

  // --------------------------------------------------------------------------
  // 3. Dynamic Live Countdown Timer (Asia/Makassar Time)
  // --------------------------------------------------------------------------
  const countdownTimer = document.getElementById('heroCountdownTimer');
  if (countdownTimer) {
    const targetDateStr = countdownTimer.getAttribute('data-target');
    if (targetDateStr) {
      // Parse ISO or "YYYY-MM-DD HH:mm:ss" in UTC+8 (WITA)
      let targetTime;
      if (targetDateStr.includes('T')) {
        targetTime = new Date(targetDateStr).getTime();
      } else {
        // Treat as WITA / UTC+8: "YYYY-MM-DDTHH:mm:ss+08:00"
        const formatted = targetDateStr.replace(' ', 'T') + '+08:00';
        targetTime = new Date(formatted).getTime();
        if (isNaN(targetTime)) {
          targetTime = new Date(targetDateStr).getTime();
        }
      }

      const elDays = document.getElementById('cdDays');
      const elHours = document.getElementById('cdHours');
      const elMinutes = document.getElementById('cdMinutes');
      const elSeconds = document.getElementById('cdSeconds');

      function updateCountdown() {
        const now = new Date().getTime();
        const diff = targetTime - now;

        if (diff <= 0) {
          countdownTimer.innerHTML = '<div style="grid-column: span 4; text-align: center; font-weight: 700; color: var(--forest-green); padding: 8px; font-size: 0.9rem;">Agenda Sedang Berlangsung / Selesai</div>';
          return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if (elDays) elDays.textContent = String(days).padStart(2, '0');
        if (elHours) elHours.textContent = String(hours).padStart(2, '0');
        if (elMinutes) elMinutes.textContent = String(minutes).padStart(2, '0');
        if (elSeconds) elSeconds.textContent = String(seconds).padStart(2, '0');
      }

      updateCountdown();
      setInterval(updateCountdown, 1000);
    }
  }

  // --------------------------------------------------------------------------
  // 4. Interactive 9-Element Balinese Architecture Detail Modal
  // --------------------------------------------------------------------------
  const anatomyCards = document.querySelectorAll('.anatomy-card-item');
  const anatomyModal = document.getElementById('anatomyModal');
  const modalAnatomyClose = document.getElementById('modalAnatomyClose');

  const modalImg = document.getElementById('modalAnatomyImg');
  const modalOrder = document.getElementById('modalAnatomyOrder');
  const modalZone = document.getElementById('modalAnatomyZone');
  const modalTitle = document.getElementById('modalAnatomyTitle');
  const modalShort = document.getElementById('modalAnatomyShort');
  const modalOrientation = document.getElementById('modalAnatomyOrientation');
  const modalFunction = document.getElementById('modalAnatomyFunction');
  const modalMaterials = document.getElementById('modalAnatomyMaterials');
  const modalPhilosophy = document.getElementById('modalAnatomyPhilosophy');

  function openAnatomyDetail(card) {
    if (!anatomyModal) return;

    const name = card.getAttribute('data-name');
    const order = card.getAttribute('data-order');
    const shortDesc = card.getAttribute('data-short');
    const zone = card.getAttribute('data-zone');
    const orientation = card.getAttribute('data-orientation');
    const fn = card.getAttribute('data-function');
    const materials = card.getAttribute('data-materials');
    const philosophy = card.getAttribute('data-philosophy');
    const image = card.getAttribute('data-image');
    const id = card.getAttribute('data-id');

    if (modalTitle) modalTitle.textContent = name;
    if (modalOrder) modalOrder.textContent = '#' + order;
    if (modalZone) modalZone.textContent = zone;
    if (modalShort) modalShort.textContent = shortDesc;
    if (modalOrientation) modalOrientation.textContent = orientation;
    if (modalFunction) modalFunction.textContent = fn;
    if (modalMaterials) modalMaterials.textContent = materials;
    if (modalPhilosophy) modalPhilosophy.textContent = philosophy;
    if (modalImg && image) {
      modalImg.src = image;
      modalImg.alt = 'Dokumentasi ' + name + ' Omah Blumbungan';
    }

    const modalLink = document.getElementById('modalAnatomyLink');
    if (modalLink && id) {
      modalLink.href = 'about.php?id=' + encodeURIComponent(id) + '#' + encodeURIComponent(id);
      modalLink.setAttribute('aria-label', 'Lihat selengkapnya tentang ' + name);
    }

    anatomyModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeAnatomyDetail() {
    if (!anatomyModal) return;
    anatomyModal.classList.remove('active');
    document.body.style.overflow = '';
  }

  anatomyCards.forEach(function (card) {
    if (card.tagName === 'A') {
      return; // Navigasi native ke halaman detail anatomi khusus
    }
    card.addEventListener('click', function () {
      openAnatomyDetail(card);
    });
    card.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openAnatomyDetail(card);
      }
    });
  });

  if (modalAnatomyClose) {
    modalAnatomyClose.addEventListener('click', closeAnatomyDetail);
  }

  if (anatomyModal) {
    anatomyModal.addEventListener('click', function (e) {
      if (e.target === anatomyModal) {
        closeAnatomyDetail();
      }
    });

    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && anatomyModal.classList.contains('active')) {
        closeAnatomyDetail();
      }
    });
  }

  // --------------------------------------------------------------------------
  // 5. Search Modal Overlay & Cultural Query Filter
  // --------------------------------------------------------------------------
  const searchBtn = document.getElementById('searchBtn');
  const searchModal = document.getElementById('searchModal');
  const searchModalClose = document.getElementById('searchModalClose');
  const siteSearchInput = document.getElementById('siteSearchInput');
  const searchResultsList = document.getElementById('searchResultsList');

  // Archive data dictionary for instant query
  const searchableArchive = [
    { title: 'Angkul-angkul (Gerbang Utama)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Aling-aling (Penyekat Spiritual)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Natah (Halaman Terbuka)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Bale Meten / Bale Daja (Kamar Utama)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Bale Dangin (Bale Upacara)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Bale Dauh (Bale Tiang Sanga)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Paon (Dapur Tradisional)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Jineng / Klumpu (Lumbung Pangan)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Pemerajan / Sanggah (Tempat Suci)', category: 'Anatomi Arsitektur', link: 'index.php#anatomi' },
    { title: 'Lokakarya Terbuka: Membaca Anatomi Rumah Bali', category: 'Agenda Terdekat', link: 'events.php' },
    { title: 'Sarasehan Undagi: Filosofi Asta Kosala Kosali', category: 'Agenda Terdekat', link: 'events.php' },
    { title: 'Praktik Pembuatan Sanggah Mini', category: 'Agenda Terdekat', link: 'events.php' },
    { title: 'Reservasi Kunjungan Edukasi & Studi Sekolah', category: 'Kunjungan', link: 'reservation.php' }
  ];

  function openSearch() {
    if (!searchModal) return;
    searchModal.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (siteSearchInput) {
      siteSearchInput.value = '';
      renderSearchResults('');
      setTimeout(() => siteSearchInput.focus(), 100);
    }
  }

  function closeSearch() {
    if (!searchModal) return;
    searchModal.classList.remove('active');
    document.body.style.overflow = '';
  }

  function renderSearchResults(query) {
    if (!searchResultsList) return;
    const cleanQ = query.trim().toLowerCase();
    const matches = cleanQ === ''
      ? searchableArchive.slice(0, 6)
      : searchableArchive.filter(item => item.title.toLowerCase().includes(cleanQ) || item.category.toLowerCase().includes(cleanQ));

    if (matches.length === 0) {
      searchResultsList.innerHTML = '<div style="padding: 16px; color: var(--stone-text); font-size: 0.9rem;">Tidak ditemukan hasil untuk "' + htmlEscape(query) + '". Silakan coba kata kunci lain.</div>';
      return;
    }

    let html = '';
    matches.forEach(item => {
      html += `
        <a href="${item.link}" class="search-result-item" onclick="document.getElementById('searchModal').classList.remove('active'); document.body.style.overflow='';">
          <div>
            <div style="font-weight: 700; color: var(--dark-wood); font-size: 0.95rem;">${item.title}</div>
            <div style="font-size: 0.78rem; color: var(--stone-text); text-transform: uppercase; letter-spacing: 0.5px;">${item.category}</div>
          </div>
          <span style="color: var(--terracotta); font-weight: 700;">&rarr;</span>
        </a>
      `;
    });
    searchResultsList.innerHTML = html;
  }

  function htmlEscape(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  if (searchBtn) {
    searchBtn.addEventListener('click', openSearch);
  }
  if (searchModalClose) {
    searchModalClose.addEventListener('click', closeSearch);
  }
  if (searchModal) {
    searchModal.addEventListener('click', function (e) {
      if (e.target === searchModal) closeSearch();
    });
  }
  if (siteSearchInput) {
    siteSearchInput.addEventListener('input', function () {
      renderSearchResults(this.value);
    });
  }

  // Global ESC Key Listener for Modals
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAnatomyDetail();
      closeSearch();
    }
  });

  // --------------------------------------------------------------------------
  // 6. Reservation Date Validation & Blackout Date Checking
  // --------------------------------------------------------------------------
  const dateInput = document.getElementById('bookingDate');
  const dateFeedback = document.getElementById('dateFeedback');

  if (dateInput && dateFeedback) {
    // Tomorrow onwards
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.min = tomorrow.toISOString().split('T')[0];

    dateInput.addEventListener('change', function () {
      const selectedDate = this.value;
      if (!selectedDate) return;

      const blackoutDates = window.omahBlackouts || [];

      if (blackoutDates.includes(selectedDate)) {
        dateFeedback.innerHTML = '<span style="display:inline-flex; align-items:center; gap:6px; color: #c62828; font-weight: 600;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> Tanggal ini bertepatan dengan Hari Raya / Upacara Adat Banjar (Kawasan Ditutup). Silakan pilih tanggal lain.</span>';
        this.value = '';
      } else {
        dateFeedback.innerHTML = '<span style="display:inline-flex; align-items:center; gap:6px; color: #294522; font-weight: 600;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg> Tanggal ini tersedia untuk permohonan kunjungan.</span>';
      }
    });
  }

  // --------------------------------------------------------------------------
  // 7. Accessible Event Gallery Lightbox Controller
  // --------------------------------------------------------------------------
  const galleryItems = document.querySelectorAll('.gallery-item-card');
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxCounter = document.getElementById('lightboxCounter');
  const lightboxClose = document.getElementById('lightboxClose');
  const lightboxPrev = document.getElementById('lightboxPrev');
  const lightboxNext = document.getElementById('lightboxNext');

  let currentPhotoIndex = 0;
  const galleryPhotosList = window.galleryPhotos || [];
  const galleryTitle = window.galleryTitle || 'Dokumentasi Kegiatan';

  function updateLightboxPhoto(index) {
    if (!lightboxImg || galleryPhotosList.length === 0) return;
    if (index < 0) index = galleryPhotosList.length - 1;
    if (index >= galleryPhotosList.length) index = 0;
    currentPhotoIndex = index;

    lightboxImg.src = galleryPhotosList[currentPhotoIndex];
    if (lightboxCounter) {
      lightboxCounter.textContent = (currentPhotoIndex + 1) + ' / ' + galleryPhotosList.length;
    }
    if (lightboxCaption) {
      lightboxCaption.textContent = galleryTitle + ' — Foto ' + (currentPhotoIndex + 1);
    }
  }

  function openLightbox(index) {
    if (!lightbox) return;
    updateLightboxPhoto(index);
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }

  galleryItems.forEach(function(card) {
    card.addEventListener('click', function() {
      const idx = parseInt(card.getAttribute('data-index'), 10) || 0;
      openLightbox(idx);
    });
    card.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const idx = parseInt(card.getAttribute('data-index'), 10) || 0;
        openLightbox(idx);
      }
    });
  });

  if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
  if (lightboxPrev) lightboxPrev.addEventListener('click', () => updateLightboxPhoto(currentPhotoIndex - 1));
  if (lightboxNext) lightboxNext.addEventListener('click', () => updateLightboxPhoto(currentPhotoIndex + 1));

  if (lightbox) {
    lightbox.addEventListener('click', function(e) {
      if (e.target === lightbox) closeLightbox();
    });
  }

  // Keyboard navigation for Lightbox (Left, Right, ESC)
  document.addEventListener('keydown', function(e) {
    if (lightbox && lightbox.classList.contains('active')) {
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') updateLightboxPhoto(currentPhotoIndex - 1);
      if (e.key === 'ArrowRight') updateLightboxPhoto(currentPhotoIndex + 1);
    }
  });

  // --------------------------------------------------------------------------
  // 8. Refined Booking Availability Calendar & Slot Controller
  // --------------------------------------------------------------------------
  const calDaysGrid = document.getElementById('calDaysGrid');
  const calCurrentMonth = document.getElementById('calCurrentMonth');
  const calPrevBtn = document.getElementById('calPrevMonth');
  const calNextBtn = document.getElementById('calNextMonth');

  const slotCardDate = document.getElementById('slotCardDate');
  const slotCardBadge = document.getElementById('slotCardBadge');
  const slotRowMorning = document.getElementById('slotRowMorning');
  const slotRowAfternoon = document.getElementById('slotRowAfternoon');
  const slotBadgeMorning = document.getElementById('slotBadgeMorning');
  const slotBadgeAfternoon = document.getElementById('slotBadgeAfternoon');
  const calReservationBtn = document.getElementById('calReservationBtn');

  const kpiSlotValue = document.getElementById('kpiSlotValue');
  const kpiSlotUnit = document.getElementById('kpiSlotUnit');
  const kpiSlotSubtext = document.getElementById('kpiSlotSubtext');

  if (calDaysGrid && calCurrentMonth) {
    const data = window.omahCalendarData || {};
    let activeYear = data.initialYear || 2026;
    let activeMonth = data.initialMonth || 11; // 1-12
    let selectedDate = data.defaultDate || '2026-11-21';
    let selectedSession = data.defaultSession || 'morning';

    const blackoutDates = data.blackoutDates || [];
    const bookings = data.bookings || [];
    const events = data.events || [];

    const monthNamesID = [
      '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const monthShortID = [
      '', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    const dayNamesID = [
      'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];

    function formatFullDate(dateStr) {
      if (!dateStr) return '';
      const parts = dateStr.split('-');
      const y = parseInt(parts[0], 10);
      const m = parseInt(parts[1], 10);
      const d = parseInt(parts[2], 10);
      const dt = new Date(y, m - 1, d);
      const dayName = dayNamesID[dt.getDay()];
      const monthName = monthNamesID[m];
      return `${dayName}, ${d} ${monthName} ${y}`;
    }

    function getDateComponents(dateStr) {
      if (!dateStr) return { dayName: '', dateMonth: '', year: '', full: '' };
      const parts = dateStr.split('-');
      const y = parseInt(parts[0], 10);
      const m = parseInt(parts[1], 10);
      const d = parseInt(parts[2], 10);
      const dt = new Date(y, m - 1, d);
      const dayName = dayNamesID[dt.getDay()];
      const monthName = monthNamesID[m];
      return {
        dayName: dayName,
        dateMonth: `${d} ${monthName}`,
        year: `${y}`,
        full: `${dayName}, ${d} ${monthName} ${y}`
      };
    }

    function formatShortDate(dateStr) {
      if (!dateStr) return '';
      const parts = dateStr.split('-');
      const d = parseInt(parts[2], 10);
      const m = parseInt(parts[1], 10);
      return `${d} ${monthShortID[m]}`;
    }

    function getDateStatus(dateStr) {
      // 1. Blackout dates check (Hari Suci / Tutup Kawasan / Hold Admin)
      const isBlackout = blackoutDates.includes(dateStr);
      if (isBlackout) {
        return {
          status: 'blackout',
          label: 'Hari Suci / Tutup',
          isAvailable: false,
          morningAvailable: false,
          afternoonAvailable: false,
          availableCount: 0,
          hasEvent: false,
          hasConfirmedBooking: false,
          reason: 'Kawasan ditutup demi kekhidmatan upacara adat dan hari raya keagamaan.'
        };
      }

      // 2. Events check ("acara" dari events.json)
      const dayEvents = events.filter(e => e.date === dateStr);
      const hasEvent = dayEvents.length > 0;
      const eventTitle = hasEvent ? (dayEvents[0].short_title || dayEvents[0].title) : '';

      // 3. Admin-confirmed bookings check
      // Tanggal terisi HANYA untuk reservasi yang sudah berstatus 'confirmed' atau 'hold' oleh admin
      let morningAvailable = true;
      let afternoonAvailable = true;
      let hasConfirmedBooking = false;

      bookings.forEach(function (bk) {
        if (bk.date === dateStr && (bk.status === 'confirmed' || bk.status === 'hold')) {
          hasConfirmedBooking = true;
          const sess = bk.session || '';
          if (sess === 'morning' || sess === 'full_day') {
            morningAvailable = false;
          }
          if (sess === 'afternoon' || sess === 'full_day') {
            afternoonAvailable = false;
          }
        }
      });

      const availableCount = (morningAvailable ? 1 : 0) + (afternoonAvailable ? 1 : 0);

      if (availableCount === 2) {
        return {
          status: 'available',
          label: hasEvent ? 'Tersedia • Ada Acara' : 'Tersedia',
          isAvailable: true,
          morningAvailable: true,
          afternoonAvailable: true,
          availableCount: 2,
          hasEvent: hasEvent,
          eventTitle: eventTitle,
          hasConfirmedBooking: false
        };
      } else if (availableCount === 1) {
        return {
          status: 'partial',
          label: 'Sebagian Terisi',
          isAvailable: true,
          morningAvailable: morningAvailable,
          afternoonAvailable: afternoonAvailable,
          availableCount: 1,
          hasEvent: hasEvent,
          eventTitle: eventTitle,
          hasConfirmedBooking: true
        };
      } else {
        return {
          status: 'full',
          label: 'Penuh (Dikonfirmasi)',
          isAvailable: false,
          morningAvailable: false,
          afternoonAvailable: false,
          availableCount: 0,
          hasEvent: hasEvent,
          eventTitle: eventTitle,
          hasConfirmedBooking: true,
          reason: 'Seluruh kuota sesi kunjungan pada tanggal ini telah terisi dan dikonfirmasi admin.'
        };
      }
    }

    function updateDetailsCard(dateStr) {
      const statusObj = getDateStatus(dateStr);
      const dateComp = getDateComponents(dateStr);

      const slotDayName = document.getElementById('slotDayName');
      const slotDayMonth = document.getElementById('slotDayMonth');
      const slotYear = document.getElementById('slotYear');

      if (slotDayName) slotDayName.textContent = dateComp.dayName;
      if (slotDayMonth) slotDayMonth.textContent = dateComp.dateMonth;
      if (slotYear) slotYear.textContent = dateComp.year;
      if (slotCardDate) {
        slotCardDate.setAttribute('aria-label', dateComp.full);
      }

      // Status Badge
      if (slotCardBadge) {
        slotCardBadge.className = 'slot-status-pill';
        if (statusObj.status === 'available') {
          slotCardBadge.classList.add('status-available');
          slotCardBadge.textContent = 'Tersedia';
        } else if (statusObj.status === 'partial') {
          slotCardBadge.classList.add('status-partial');
          slotCardBadge.textContent = 'Sebagian Terisi';
        } else {
          slotCardBadge.classList.add('status-blocked');
          slotCardBadge.textContent = statusObj.status === 'blackout' ? 'Tutup Adat' : 'Penuh';
        }
      }

      // Slot Card Helper Text
      const slotCardHelp = document.getElementById('slotCardHelp');
      if (slotCardHelp) {
        if (statusObj.hasEvent) {
          slotCardHelp.innerHTML = `<span style="color:var(--terracotta); font-weight:700;">● Acara: ${htmlEscape(statusObj.eventTitle)}</span> &bull; Sesi kunjungan:`;
        } else if (statusObj.status === 'partial') {
          slotCardHelp.textContent = 'Satu sesi telah dikonfirmasi. Pilih sesi yang tersedia:';
        } else if (statusObj.status === 'full') {
          slotCardHelp.textContent = 'Seluruh sesi tanggal ini telah dikonfirmasi terisi.';
        } else if (statusObj.status === 'blackout') {
          slotCardHelp.textContent = 'Kawasan ditutup demi upacara adat / hari suci.';
        } else {
          slotCardHelp.textContent = 'Pilih salah satu sesi kunjungan yang tersedia:';
        }
      }

      // Morning Slot Row
      const radioMorning = slotRowMorning ? slotRowMorning.querySelector('input[type="radio"]') : null;
      if (slotRowMorning && slotBadgeMorning) {
        if (statusObj.morningAvailable) {
          slotRowMorning.classList.remove('is-disabled');
          slotRowMorning.classList.add('is-available');
          if (radioMorning) radioMorning.disabled = false;
          slotBadgeMorning.className = 'slot-row-badge badge-green';
          slotBadgeMorning.innerHTML = 'Tersedia';
        } else {
          slotRowMorning.classList.add('is-disabled');
          slotRowMorning.classList.remove('is-available', 'is-selected');
          if (radioMorning) {
            radioMorning.disabled = true;
            radioMorning.checked = false;
          }
          slotBadgeMorning.className = 'slot-row-badge badge-muted';
          slotBadgeMorning.innerHTML = statusObj.status === 'blackout' ? 'Tutup' : 'Terisi';
        }
      }

      // Afternoon Slot Row
      const radioAfternoon = slotRowAfternoon ? slotRowAfternoon.querySelector('input[type="radio"]') : null;
      if (slotRowAfternoon && slotBadgeAfternoon) {
        if (statusObj.afternoonAvailable) {
          slotRowAfternoon.classList.remove('is-disabled');
          slotRowAfternoon.classList.add('is-available');
          if (radioAfternoon) radioAfternoon.disabled = false;
          slotBadgeAfternoon.className = 'slot-row-badge badge-green';
          slotBadgeAfternoon.innerHTML = 'Tersedia';
        } else {
          slotRowAfternoon.classList.add('is-disabled');
          slotRowAfternoon.classList.remove('is-available', 'is-selected');
          if (radioAfternoon) {
            radioAfternoon.disabled = true;
            radioAfternoon.checked = false;
          }
          slotBadgeAfternoon.className = 'slot-row-badge badge-muted';
          slotBadgeAfternoon.innerHTML = statusObj.status === 'blackout' ? 'Tutup' : 'Terisi';
        }
      }

      // Determine selected session
      if (selectedSession === 'morning' && statusObj.morningAvailable) {
        if (radioMorning) radioMorning.checked = true;
        if (slotRowMorning) slotRowMorning.classList.add('is-selected');
        if (slotRowAfternoon) slotRowAfternoon.classList.remove('is-selected');
      } else if (selectedSession === 'afternoon' && statusObj.afternoonAvailable) {
        if (radioAfternoon) radioAfternoon.checked = true;
        if (slotRowAfternoon) slotRowAfternoon.classList.add('is-selected');
        if (slotRowMorning) slotRowMorning.classList.remove('is-selected');
      } else if (statusObj.morningAvailable) {
        selectedSession = 'morning';
        if (radioMorning) radioMorning.checked = true;
        if (slotRowMorning) slotRowMorning.classList.add('is-selected');
        if (slotRowAfternoon) slotRowAfternoon.classList.remove('is-selected');
      } else if (statusObj.afternoonAvailable) {
        selectedSession = 'afternoon';
        if (radioAfternoon) radioAfternoon.checked = true;
        if (slotRowAfternoon) slotRowAfternoon.classList.add('is-selected');
        if (slotRowMorning) slotRowMorning.classList.remove('is-selected');
      } else {
        selectedSession = '';
      }

      // CTA Button
      if (calReservationBtn) {
        if (statusObj.isAvailable && selectedSession) {
          calReservationBtn.classList.remove('btn-disabled');
          calReservationBtn.removeAttribute('aria-disabled');
          calReservationBtn.href = `reservation.php?date=${encodeURIComponent(dateStr)}&session=${encodeURIComponent(selectedSession)}`;
          calReservationBtn.innerHTML = '<span>Reservasi Kunjungan</span> <span class="btn-arrow">&rarr;</span>';
        } else if (statusObj.isAvailable) {
          calReservationBtn.classList.remove('btn-disabled');
          calReservationBtn.href = `reservation.php?date=${encodeURIComponent(dateStr)}`;
          calReservationBtn.innerHTML = '<span>Reservasi Kunjungan</span> <span class="btn-arrow">&rarr;</span>';
        } else {
          calReservationBtn.classList.add('btn-disabled');
          calReservationBtn.setAttribute('aria-disabled', 'true');
          calReservationBtn.href = 'javascript:void(0)';
          calReservationBtn.innerHTML = '<span>Tanggal Tidak Tersedia</span><span style="font-size:0.72rem; font-weight:500; opacity:0.85;">(Silakan pilih tanggal lain)</span>';
        }
      }

      // Dynamic update of KPI 2 (Slot Tersedia)
      if (kpiSlotValue && kpiSlotUnit && kpiSlotSubtext) {
        kpiSlotValue.textContent = statusObj.availableCount;
        kpiSlotUnit.textContent = 'Sesi';
        if (statusObj.availableCount === 2) {
          kpiSlotSubtext.textContent = `Sesi Pagi & Sore tersedia (${formatShortDate(dateStr)})`;
        } else if (statusObj.availableCount === 1) {
          const which = statusObj.morningAvailable ? 'Pagi' : 'Sore';
          kpiSlotSubtext.textContent = `Sesi ${which} tersedia (${formatShortDate(dateStr)})`;
        } else {
          kpiSlotSubtext.textContent = statusObj.status === 'blackout' ? `Hari Suci / Tutup (${formatShortDate(dateStr)})` : `Seluruh sesi terisi (${formatShortDate(dateStr)})`;
        }
      }
    }

    function renderCalendar(year, month) {
      if (calCurrentMonth) {
        calCurrentMonth.textContent = `${monthNamesID[month]} ${year}`;
      }
      calDaysGrid.innerHTML = '';

      // Days count
      const daysInCurMonth = new Date(year, month, 0).getDate();
      const firstDayDow = new Date(year, month - 1, 1).getDay(); // Sun=0, Mon=1...
      const startOffset = (firstDayDow + 6) % 7; // Mon=0, Tue=1 ... Sun=6

      // Days of prev month
      const daysInPrevMonth = new Date(year, month - 1, 0).getDate();
      for (let i = startOffset - 1; i >= 0; i--) {
        const dNum = daysInPrevMonth - i;
        const btn = document.createElement('div');
        btn.className = 'cal-day-cell is-disabled';
        btn.setAttribute('aria-hidden', 'true');
        btn.innerHTML = `<span>${dNum}</span>`;
        calDaysGrid.appendChild(btn);
      }

      // Current month days
      for (let d = 1; d <= daysInCurMonth; d++) {
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const statusObj = getDateStatus(dateStr);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cal-day-cell';
        btn.setAttribute('data-date', dateStr);

        let dotHtml = '';
        let statusText = 'Tersedia';

        if (statusObj.hasEvent) {
          dotHtml = '<span class="cell-dot dot-event" aria-hidden="true"></span>';
          statusText = `Ada Acara (${statusObj.eventTitle}), Tersedia`;
        } else if (statusObj.hasConfirmedBooking && statusObj.status === 'partial') {
          dotHtml = '<span class="cell-dot dot-partial" aria-hidden="true"></span>';
          statusText = 'Sebagian Terisi (Dikonfirmasi Admin)';
        } else if (statusObj.hasConfirmedBooking && statusObj.status === 'full') {
          dotHtml = '<span class="cell-dot dot-blocked" aria-hidden="true"></span>';
          statusText = 'Terisi Penuh (Dikonfirmasi Admin)';
        } else if (statusObj.status === 'blackout') {
          dotHtml = '<span class="cell-dot dot-blocked" aria-hidden="true"></span>';
          statusText = 'Hari Suci Adat / Tutup';
        } else {
          // Tanggal biasa yang tersedia: TIDAK ADA TANDA DOT
          dotHtml = '';
          statusText = 'Tersedia';
        }

        btn.setAttribute('aria-label', `${d} ${monthNamesID[month]} ${year}, ${statusText}`);

        if (dateStr === selectedDate) {
          btn.classList.add('is-selected');
        }

        btn.innerHTML = `<span>${d}</span>${dotHtml}`;

        btn.addEventListener('click', function () {
          const cells = calDaysGrid.querySelectorAll('.cal-day-cell');
          cells.forEach(c => c.classList.remove('is-selected'));
          btn.classList.add('is-selected');

          selectedDate = dateStr;
          updateDetailsCard(selectedDate);
        });

        calDaysGrid.appendChild(btn);
      }

      // Trailing days for grid completion
      const totalCells = startOffset + daysInCurMonth;
      const targetCells = totalCells > 35 ? 42 : 35;
      const remainingCells = targetCells - totalCells;
      for (let i = 1; i <= remainingCells; i++) {
        const btn = document.createElement('div');
        btn.className = 'cal-day-cell is-disabled';
        btn.setAttribute('aria-hidden', 'true');
        btn.innerHTML = `<span>${i}</span>`;
        calDaysGrid.appendChild(btn);
      }
    }

    // Month Navigation listeners
    if (calPrevBtn) {
      calPrevBtn.addEventListener('click', function () {
        activeMonth--;
        if (activeMonth < 1) {
          activeMonth = 12;
          activeYear--;
        }
        renderCalendar(activeYear, activeMonth);
      });
    }

    if (calNextBtn) {
      calNextBtn.addEventListener('click', function () {
        activeMonth++;
        if (activeMonth > 12) {
          activeMonth = 1;
          activeYear++;
        }
        renderCalendar(activeYear, activeMonth);
      });
    }

    // Session selection radios listeners
    [slotRowMorning, slotRowAfternoon].forEach(function (row) {
      if (!row) return;
      row.addEventListener('click', function () {
        if (row.classList.contains('is-disabled')) return;
        const radio = row.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = true;
          selectedSession = radio.value;
          if (slotRowMorning) slotRowMorning.classList.toggle('is-selected', selectedSession === 'morning');
          if (slotRowAfternoon) slotRowAfternoon.classList.toggle('is-selected', selectedSession === 'afternoon');

          if (calReservationBtn && selectedDate) {
            calReservationBtn.href = `reservation.php?date=${encodeURIComponent(selectedDate)}&session=${encodeURIComponent(selectedSession)}`;
          }
        }
      });
    });

    // Initial render
    renderCalendar(activeYear, activeMonth);
    updateDetailsCard(selectedDate);
  }

  // --------------------------------------------------------------------------
  // 9. Agenda & Events Horizontal Carousel / Slider Controller
  // --------------------------------------------------------------------------
  const sliderViewport = document.getElementById('eventsSliderViewport');
  const sliderTrack = document.getElementById('eventsSliderTrack');
  const sliderPrevBtn = document.getElementById('eventSliderPrev');
  const sliderNextBtn = document.getElementById('eventSliderNext');
  const sliderIndicator = document.getElementById('eventSliderIndicator');

  if (sliderTrack && sliderViewport) {
    const cards = sliderTrack.querySelectorAll('.event-slider-card');
    const totalCards = cards.length;
    let currentPage = 0;

    function getItemsPerPage() {
      // Mobile (< 768px): 1 card visible per slide
      // Desktop & Tablet (>= 768px): 2 cards visible per slide
      return window.innerWidth < 768 ? 1 : 2;
    }

    function getTotalPages() {
      const itemsPerPage = getItemsPerPage();
      return Math.max(1, Math.ceil(totalCards / itemsPerPage));
    }

    function updateSlider() {
      const itemsPerPage = getItemsPerPage();
      const totalPages = getTotalPages();

      if (currentPage >= totalPages) {
        currentPage = totalPages - 1;
      }
      if (currentPage < 0) {
        currentPage = 0;
      }

      if (cards.length > 0) {
        const cardWidth = cards[0].getBoundingClientRect().width;
        const gap = 16;
        const shift = currentPage * itemsPerPage * (cardWidth + gap);
        sliderTrack.style.transform = `translateX(-${Math.round(shift)}px)`;
      }

      // Update indicator: "1 / 2" (desktop) or "1 / 4" (mobile)
      if (sliderIndicator) {
        sliderIndicator.textContent = `${currentPage + 1} / ${totalPages}`;
      }

      // Update arrow buttons
      if (sliderPrevBtn) {
        sliderPrevBtn.disabled = (currentPage === 0);
        sliderPrevBtn.setAttribute('aria-disabled', currentPage === 0 ? 'true' : 'false');
      }
      if (sliderNextBtn) {
        sliderNextBtn.disabled = (currentPage >= totalPages - 1);
        sliderNextBtn.setAttribute('aria-disabled', currentPage >= totalPages - 1 ? 'true' : 'false');
      }
    }

    if (sliderPrevBtn) {
      sliderPrevBtn.addEventListener('click', function () {
        if (currentPage > 0) {
          currentPage--;
          updateSlider();
        }
      });
    }

    if (sliderNextBtn) {
      sliderNextBtn.addEventListener('click', function () {
        const totalPages = getTotalPages();
        if (currentPage < totalPages - 1) {
          currentPage++;
          updateSlider();
        }
      });
    }

    // Touch Swipe Support for Mobile & Touchscreens
    let touchStartX = 0;
    let touchStartY = 0;
    let isSwiping = false;

    sliderViewport.addEventListener('touchstart', function (e) {
      if (e.touches.length === 1) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isSwiping = true;
      }
    }, { passive: true });

    sliderViewport.addEventListener('touchend', function (e) {
      if (!isSwiping || e.changedTouches.length === 0) return;
      isSwiping = false;
      const touchEndX = e.changedTouches[0].clientX;
      const touchEndY = e.changedTouches[0].clientY;
      const diffX = touchEndX - touchStartX;
      const diffY = touchEndY - touchStartY;

      // Threshold: 35px horizontal movement, horizontal dominant
      if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 35) {
        const totalPages = getTotalPages();
        if (diffX < 0 && currentPage < totalPages - 1) {
          // Swipe Left -> Next Slide
          currentPage++;
          updateSlider();
        } else if (diffX > 0 && currentPage > 0) {
          // Swipe Right -> Prev Slide
          currentPage--;
          updateSlider();
        }
      }
    }, { passive: true });

    // Handle screen resize & orientation change
    let resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        updateSlider();
      }, 100);
    });

    // Initial slider calculation
    updateSlider();
  }

  // --------------------------------------------------------------------------
  // 10. Interactive Agenda Category Filter (events.php)
  // --------------------------------------------------------------------------
  const agendaFilterBar = document.querySelector('.agenda-filter-bar');
  const agendaCardsStack = document.getElementById('agendaCardsStack');
  const agendaFilterEmpty = document.getElementById('agendaFilterEmpty');
  const btnResetFilter = document.getElementById('btnResetFilter');

  if (agendaFilterBar && agendaCardsStack) {
    const filterButtons = agendaFilterBar.querySelectorAll('.agenda-filter-btn');
    const cards = agendaCardsStack.querySelectorAll('.agenda-card');

    function applyAgendaFilter(selectedCategory, updateUrl) {
      let visibleCount = 0;

      // Update button active and aria states
      filterButtons.forEach(function (btn) {
        const cat = btn.getAttribute('data-filter');
        if (cat === selectedCategory) {
          btn.classList.add('active');
          btn.setAttribute('aria-pressed', 'true');
        } else {
          btn.classList.remove('active');
          btn.setAttribute('aria-pressed', 'false');
        }
      });

      // Show/hide matching cards
      cards.forEach(function (card) {
        const cardCat = card.getAttribute('data-category');
        if (selectedCategory === 'all' || cardCat === selectedCategory) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Toggle empty filter notice if no cards match
      if (agendaFilterEmpty) {
        agendaFilterEmpty.style.display = (visibleCount === 0) ? 'block' : 'none';
      }

      // Update URL query param cleanly without full page refresh
      if (updateUrl && window.history && window.history.replaceState) {
        const url = new URL(window.location.href);
        if (selectedCategory === 'all') {
          url.searchParams.delete('category');
        } else {
          url.searchParams.set('category', selectedCategory);
        }
        window.history.replaceState({}, '', url.toString());
      }
    }

    // Attach click handlers to filter buttons
    filterButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const category = this.getAttribute('data-filter') || 'all';
        applyAgendaFilter(category, true);
      });
    });

    // Reset filter button in empty state
    if (btnResetFilter) {
      btnResetFilter.addEventListener('click', function () {
        applyAgendaFilter('all', true);
      });
    }

    // Check initial category from URL parameter on load
    const urlParams = new URLSearchParams(window.location.search);
    const initialCategory = urlParams.get('category');
    if (initialCategory) {
      const matchBtn = Array.from(filterButtons).find(function (btn) {
        return btn.getAttribute('data-filter') === initialCategory;
      });
      if (matchBtn) {
        applyAgendaFilter(initialCategory, false);
      }
    }
  }
});
