<?php
/**
 * OMAH BLUMBUNGAN — Homepage (Beranda)
 * Editorial Cultural Sanctuary & Living Balinese Architecture Archive
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Beranda';
$settings = get_settings();
$allEvents = get_events('upcoming');
$allAnatomy = get_anatomy(); // All 9 elements

// Featured event for Hero Card & Countdown
$featuredEvent = null;
foreach ($allEvents as $evt) {
    if (!empty($evt['is_featured'])) {
        $featuredEvent = $evt;
        break;
    }
}
if (!$featuredEvent && !empty($allEvents)) {
    $featuredEvent = $allEvents[0];
}

$allBookings = get_bookings();

// Upcoming events sorted chronologically for Agenda section
$upcomingEvents = get_events('upcoming');
usort($upcomingEvents, function($a, $b) {
    return strcmp($a['date'] ?? '', $b['date'] ?? '');
});

$totalAgenda = count($upcomingEvents);
$nextEvent = !empty($upcomingEvents) ? $upcomingEvents[0] : null;

$monthNamesID = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];

if ($nextEvent) {
    $evTime = strtotime($nextEvent['date']);
    $mNum = (int)date('n', $evTime);
    $nextEventDateStr = date('j', $evTime) . ' ' . ($monthNamesID[$mNum] ?? date('M', $evTime)) . ' ' . date('Y', $evTime);
    
    // Timezone Asia/Makassar countdown
    $todayMakassar = new DateTime('now', new DateTimeZone('Asia/Makassar'));
    $eventDateObj = new DateTime($nextEvent['date'], new DateTimeZone('Asia/Makassar'));
    $interval = $todayMakassar->diff($eventDateObj);
    $daysDiff = (int)$interval->format('%r%a');
    if ($daysDiff > 0) {
        $countdownText = "{$daysDiff} Hari Lagi";
    } elseif ($daysDiff === 0) {
        $countdownText = "Hari Ini";
    } else {
        $countdownText = "Selesai";
    }
    $nextEventTitle = $nextEvent['short_title'] ?? $nextEvent['title'];
} else {
    $nextEventDateStr = 'Belum Ada';
    $countdownText = 'Belum ada agenda terjadwal';
    $nextEventTitle = '';
}

// Calculate Available Visiting Time Slots for active month (November 2026 default)
$activeCalYear = 2026;
$activeCalMonth = 11;
$daysInMonth = 30;
$blackoutDates = $settings['blackout_dates'] ?? [];

$monthlyAvailableSlots = 0;
for ($d = 1; $d <= $daysInMonth; $d++) {
    $dStr = sprintf('%04d-%02d-%02d', $activeCalYear, $activeCalMonth, $d);
    if (in_array($dStr, $blackoutDates, true)) {
        continue;
    }
    $mAvail = true;
    $aAvail = true;
    foreach ($allBookings as $bk) {
        if (($bk['date'] ?? '') === $dStr && in_array($bk['status'] ?? '', ['confirmed', 'hold'], true)) {
            $sess = $bk['session'] ?? '';
            if ($sess === 'morning' || $sess === 'full_day') $mAvail = false;
            if ($sess === 'afternoon' || $sess === 'full_day') $aAvail = false;
        }
    }
    if ($mAvail) $monthlyAvailableSlots++;
    if ($aAvail) $monthlyAvailableSlots++;
}

$waNumber = $settings['whatsapp'] ?? '6281234567890';
$waLink = make_whatsapp_url($waNumber, "Om Swastyastu, salam hangat. Saya ingin reservasi kunjungan edukasi ke Omah Blumbungan.");

// Hero Background Slideshow Settings
$heroSlides = $settings['hero_slideshow'] ?? [
    'assets/images/herobg.jpg',
    'assets/images/hero-compound.jpg',
    'assets/images/photo-01.jpg',
    'assets/images/photo-03.jpg'
];
if (empty($heroSlides)) {
    $heroSlides = ['assets/images/herobg.jpg'];
}
$heroInterval = (int)($settings['hero_slideshow_interval'] ?? 5);
if ($heroInterval < 2) $heroInterval = 5;

include __DIR__ . '/includes/header.php';
?>

<!-- ========================================================================
     SECTION 2: HERO EXPERIENCE (Visual Dominan 60-70vh + Slideshow Dinamis)
     ======================================================================== -->
<section class="hero-section" id="heroSection" data-interval="<?php echo $heroInterval; ?>">
  <!-- Slideshow Background Layers -->
  <div class="hero-slideshow" id="heroSlideshow" aria-hidden="true">
    <?php foreach ($heroSlides as $idx => $slideImg): ?>
      <div class="hero-slide <?php echo $idx === 0 ? 'active' : ''; ?>" 
           style="background-image: url('<?php echo htmlspecialchars($slideImg); ?>');" 
           data-slide-index="<?php echo $idx; ?>">
      </div>
    <?php endforeach; ?>
    <div class="hero-slide-overlay"></div>
  </div>

  <!-- Slideshow Navigation Indicators (if > 1 slide) -->
  <?php if (count($heroSlides) > 1): ?>
  <div class="hero-slide-dots" id="heroSlideDots" aria-label="Navigasi Slideshow">
    <?php foreach ($heroSlides as $idx => $slideImg): ?>
      <button type="button" 
              class="hero-slide-dot <?php echo $idx === 0 ? 'active' : ''; ?>" 
              data-target-slide="<?php echo $idx; ?>" 
              aria-label="Slide <?php echo $idx + 1; ?>">
      </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="container hero-event-container">
    <?php if ($featuredEvent): ?>
    <div class="hero-event-card-standalone">
      <div class="event-top-bar">
        <span class="badge-agenda">Agenda Terdekat</span>
        <a href="events.php" class="event-link-all">Lihat Semua &rarr;</a>
      </div>

      <h3 class="hero-event-title"><?php echo htmlspecialchars($featuredEvent['title']); ?></h3>

      <?php if (!empty($featuredEvent['description'])): ?>
      <p class="hero-event-desc"><?php echo htmlspecialchars($featuredEvent['description']); ?></p>
      <?php endif; ?>

      <div class="hero-event-meta">
        <div class="meta-row">
          <?php echo ob_icon('calendar', ['size' => 16, 'color' => 'warm']); ?>
          <span><?php echo format_date_id($featuredEvent['date']); ?> &bull; <?php echo ob_icon('clock', ['size' => 15, 'color' => 'warm']); ?> <?php echo htmlspecialchars($featuredEvent['time']); ?></span>
        </div>
        <div class="meta-row">
          <?php echo ob_icon('map-pin', ['size' => 16, 'color' => 'warm']); ?>
          <span><?php echo htmlspecialchars($featuredEvent['location']); ?></span>
        </div>
      </div>

      <!-- Dynamic Live Countdown Timer -->
      <div class="countdown-strip">
        <div class="countdown-label">WAKTU MENUJU PELAKSANAAN</div>
        <div class="countdown-digits" id="heroCountdownTimer" data-target="<?php echo htmlspecialchars($featuredEvent['date'] . ' 09:00:00'); ?>">
          <div class="cd-box"><span class="cd-num" id="cdDays">00</span><span class="cd-lbl">Hari</span></div>
          <div class="cd-box"><span class="cd-num" id="cdHours">00</span><span class="cd-lbl">Jam</span></div>
          <div class="cd-box"><span class="cd-num" id="cdMinutes">00</span><span class="cd-lbl">Menit</span></div>
          <div class="cd-box"><span class="cd-num" id="cdSeconds">00</span><span class="cd-lbl">Detik</span></div>
        </div>
      </div>

      <div class="hero-event-bottom">
        <div class="speaker-info">
          <img src="assets/images/photo-01.jpg" alt="Foto Pemateri" class="speaker-avatar">
          <div class="speaker-text">
            <div class="speaker-name"><?php echo htmlspecialchars($featuredEvent['speaker'] ?? 'I Ketut Wirawan'); ?></div>
            <div class="speaker-role"><?php echo htmlspecialchars($featuredEvent['speaker_role'] ?? 'Undagi & Praktisi Arsitektur Bali'); ?></div>
          </div>
        </div>
        <a href="events.php#<?php echo htmlspecialchars($featuredEvent['id']); ?>" class="btn btn-terracotta btn-sm">
          <span>Detail &amp; Daftar</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ========================================================================
     SECTION 2B: CULTURAL VALUE STRIP (Sebagai Divider / Pemisah Section Bawah Hero)
     ======================================================================== -->
<section class="cultural-value-strip">
  <div class="container values-grid">
    <!-- Value 1 -->
    <div class="value-item">
      <div class="value-icon">
        <?php echo ob_icon('bale', ['size' => 24, 'color' => 'terracotta', 'raw' => true]); ?>
      </div>
      <div class="value-text">
        <h4>Ruang budaya</h4>
        <p>yang hidup dan autentik</p>
      </div>
    </div>

    <!-- Value 2 -->
    <div class="value-item">
      <div class="value-icon">
        <?php echo ob_icon('angkul-angkul', ['size' => 24, 'color' => 'terracotta', 'raw' => true]); ?>
      </div>
      <div class="value-text">
        <h4>Edukasi arsitektur</h4>
        <p>berbasis kearifan lokal</p>
      </div>
    </div>

    <!-- Value 3 -->
    <div class="value-item">
      <div class="value-icon">
        <?php echo ob_icon('users', ['size' => 24, 'color' => 'terracotta', 'raw' => true]); ?>
      </div>
      <div class="value-text">
        <h4>Dialog komunitas</h4>
        <p>dan kolaborasi lintas disiplin</p>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     SECTION 2C: NARASI UTAMA & EDUKASI TRADISIONAL
     ======================================================================== -->
<section class="intro-story-section">
  <div class="container">
    <div class="intro-story-card">
      <span class="eyebrow">RUANG BUDAYA &amp; EDUKASI TRADISIONAL</span>
      <h2 class="intro-story-headline">Menyelami Jiwa &amp; Proporsi Rumah Bali</h2>
      <p class="intro-story-description">
        <strong>Omah Blumbungan</strong> adalah ruang belajar dan sanggraloka budaya di Sibang Kaja, Abiansemal. 
        Tempat di mana filosofi <em>Tri Hita Karana</em> dan kearifan <em>Asta Kosala Kosali</em> diwujudkan 
        dalam harmoni pekarangan, natah terbuka, dan bale-bale tradisional yang hidup.
      </p>
    </div>
  </div>
</section>

<!-- ========================================================================
     DIVIDER: PEMISAH GARIS TIPIS SEBELUM MASUK KE NILAI & FILOSOFI
     ======================================================================== -->
<div class="section-divider-thin-wrap">
  <div class="container">
    <hr class="section-divider-thin">
  </div>
</div>

<!-- ========================================================================
     SECTION 4: FILOSOFI & NILAI (Harmoni Ruang dan Kehidupan)
     ======================================================================== -->
<section class="philosophy-section">
  <div class="container">
    <div class="philosophy-header-centered">
      <span class="eyebrow">NILAI &amp; FILOSOFI</span>
      <h2 class="section-heading-editorial">Harmoni Ruang dan Kehidupan</h2>
      <p class="section-subtitle-editorial">
        Lebih dari sekadar bangunan fisik, pekarangan rumah tradisional Bali dirancang sebagai 
        mikrokosmos semesta yang menyatukan manusia, alam, dan spiritualitas dalam keseimbangan 
        <em>Tri Hita Karana</em>.
      </p>
      <div class="philosophy-header-link-wrap">
        <a href="about.php" class="link-arrow">
          <span>Tentang Omah Blumbungan</span>
          <span aria-hidden="true">&rarr;</span>
        </a>
      </div>
    </div>

    <!-- 3 Cultural Content Cards -->
    <div class="philosophy-cards-grid">
      <!-- Card 1 -->
      <div class="culture-card">
        <div class="culture-card-icon">
          <?php echo ob_icon('bale', ['size' => 26, 'color' => 'terracotta', 'raw' => true]); ?>
        </div>
        <h3>Edukasi Arsitektur Tradisional</h3>
        <p>
          Membedah proporsi ukuran tubuh manusia (depa, lengkat, musti) dan susunan Sanga Mandala 
          bersama para undagi ahli.
        </p>
      </div>

      <!-- Card 2 -->
      <div class="culture-card">
        <div class="culture-card-icon">
          <?php echo ob_icon('users', ['size' => 26, 'color' => 'terracotta', 'raw' => true]); ?>
        </div>
        <h3>Ruang Dialog &amp; Komunitas</h3>
        <p>
          Tersedia ruang terbuka beratap alang-alang dan natah rindang untuk sarasehan, 
          lokakarya budaya, dan diskusi lintas disiplin.
        </p>
      </div>

      <!-- Card 3 -->
      <div class="culture-card">
        <div class="culture-card-icon">
          <?php echo ob_icon('camera', ['size' => 26, 'color' => 'terracotta', 'raw' => true]); ?>
        </div>
        <h3>Latar Budaya &amp; Dokumentasi</h3>
        <p>
          Suasana otentik bata merah pres dan ornamen klasik Bali yang lestari, cocok untuk riset, 
          studi visual, maupun sesi foto berkarakter.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     SECTION 5: ANATOMI PEKARANGAN BALI (3x3 Grid with All 9 Elements)
     ======================================================================== -->
<section class="anatomy-section" id="anatomi">
  <div class="container">
    <div class="anatomy-header">
      <div>
        <span class="eyebrow">ANATOMI PEKARANGAN</span>
        <h2 class="section-heading-editorial">Kepingan Elemen Asta Kosala Kosali</h2>
        <p class="section-subtitle-editorial">
          Mengenal peran dan letak setiap elemen pekarangan Bali, dari angkul-angkul hingga bale-bale, beserta nilai filosofisnya.
        </p>
      </div>
      <a href="about.php#anatomi-lengkap" class="link-arrow">
        <span>Lihat Seluruh Anatomi</span>
        <span>&rarr;</span>
      </a>
    </div>

    <!-- 3x3 Grid Displaying All 9 Architectural Elements (Navigates to Dedicated Detail Page) -->
    <div class="anatomy-grid-3x3">
      <?php foreach ($allAnatomy as $elem): ?>
      <a href="anatomy.php?id=<?php echo urlencode($elem['slug'] ?? $elem['id']); ?>" 
         class="anatomy-card-item" 
         aria-label="Lihat rincian detail <?php echo htmlspecialchars($elem['name']); ?>">
        
        <div class="anatomy-thumb-wrap">
          <img src="<?php echo htmlspecialchars($elem['image'] ?? 'assets/images/photo-01.jpg'); ?>" 
               alt="<?php echo htmlspecialchars($elem['name']); ?>" 
               class="anatomy-thumb" 
               loading="lazy">
        </div>

        <div class="anatomy-info">
          <span class="anatomy-num"><?php echo sprintf('%02d', $elem['sort_order']); ?></span>
          <div class="anatomy-name-row">
            <h3 class="anatomy-name"><?php echo htmlspecialchars($elem['name']); ?></h3>
            <span class="anatomy-arrow">&rarr;</span>
          </div>
          <p class="anatomy-snippet"><?php echo htmlspecialchars($elem['short_desc']); ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========================================================================
     SECTION 6: AGENDA & REFINED BOOKING AVAILABILITY CALENDAR
     ======================================================================== -->
<section class="agenda-section" id="agenda">
  <div class="container">
    
    <!-- 1. Refined Section Heading & Intro -->
    <div class="agenda-section-header-refined">
      <span class="eyebrow-accent">AGENDA &amp; KUNJUNGAN</span>
      <h2 class="agenda-main-title">Cek Ketersediaan &amp; Kegiatan Budaya</h2>
      <p class="agenda-lead-text">
        Rencanakan kunjungan Anda atau ikuti kegiatan budaya di Omah Belumbungan. Pilih tanggal pada kalender untuk melihat jam kunjungan yang tersedia.
      </p>
    </div>

    <!-- 2. Exactly Three Dynamic KPI Cards -->
    <div class="agenda-kpi-row" role="region" aria-label="Ringkasan Kunjungan dan Agenda">
      <!-- KPI 1: Total Agenda -->
      <div class="agenda-kpi-card">
        <div class="kpi-top">
          <span class="kpi-label">Total Agenda</span>
          <span class="kpi-icon-wrap" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </span>
        </div>
        <div class="kpi-value-row">
          <span class="kpi-value" id="kpiTotalAgenda"><?php echo $totalAgenda; ?></span>
          <span class="kpi-unit">Agenda</span>
        </div>
        <div class="kpi-subtext">Kegiatan budaya mendatang</div>
      </div>

      <!-- KPI 2: Slot Tersedia (Available Visiting Time Slots) -->
      <div class="agenda-kpi-card" id="kpiSlotCard">
        <div class="kpi-top">
          <span class="kpi-label">Slot Tersedia</span>
          <span class="kpi-icon-wrap" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </span>
        </div>
        <div class="kpi-value-row">
          <span class="kpi-value" id="kpiSlotValue"><?php echo $monthlyAvailableSlots; ?></span>
          <span class="kpi-unit" id="kpiSlotUnit">Sesi</span>
        </div>
        <div class="kpi-subtext" id="kpiSlotSubtext">Sesi waktu kunjungan bulan ini (Pagi &amp; Sore)</div>
      </div>

      <!-- KPI 3: Agenda Terdekat -->
      <div class="agenda-kpi-card">
        <div class="kpi-top">
          <span class="kpi-label">Agenda Terdekat</span>
          <span class="kpi-icon-wrap" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
          </span>
        </div>
        <div class="kpi-value-row">
          <span class="kpi-value kpi-date-value" id="kpiNextEventDate"><?php echo htmlspecialchars($nextEventDateStr); ?></span>
        </div>
        <div class="kpi-subtext" id="kpiNextEventSubtext">
          <?php if ($nextEvent): ?>
            <span><?php echo htmlspecialchars($countdownText); ?> &bull; <?php echo htmlspecialchars($nextEventTitle); ?></span>
          <?php else: ?>
            <span>Belum ada agenda terjadwal</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 3. Main 50% / 50% Two-Column Content Layout -->
    <div class="agenda-50-50-layout">
      
      <!-- ==================================================================
           LEFT 50%: BOOKING CALENDAR & AVAILABILITY (Side-by-Side Subcolumns)
           ================================================================== -->
      <div class="agenda-booking-col">
        <div class="booking-master-card">
          <!-- Calendar Master Header -->
          <div class="booking-card-header">
            <span class="booking-header-icon" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </span>
            <div class="booking-header-text">
              <h3 class="booking-main-title">Kalender Ketersediaan Kunjungan</h3>
              <p class="booking-subtitle">Lihat tanggal dan jam kunjungan yang tersedia.</p>
            </div>
          </div>

          <!-- Two Side-by-Side Subcolumns on Desktop (50% Calendar | 50% Availability) -->
          <div class="booking-subcolumns">
            
            <!-- SUBCOLUMN A: Calendar (50%) -->
            <div class="b-subcol-calendar">
              <!-- Month Navigation Bar -->
              <div class="cal-nav-bar">
                <button type="button" class="cal-nav-btn" id="calPrevMonth" aria-label="Bulan sebelumnya">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                  </svg>
                </button>
                <div class="cal-month-title" id="calCurrentMonth">November 2026</div>
                <button type="button" class="cal-nav-btn" id="calNextMonth" aria-label="Bulan berikutnya">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
              </div>

              <!-- Indonesian Weekdays Header (Sen to Min) -->
              <div class="cal-weekdays">
                <span>Sen</span>
                <span>Sel</span>
                <span>Rab</span>
                <span>Kam</span>
                <span>Jum</span>
                <span>Sab</span>
                <span>Min</span>
              </div>

              <!-- Dynamic Calendar Days Grid -->
              <div class="cal-days-grid" id="calDaysGrid" role="grid" aria-label="Kalender ketersediaan kunjungan">
                <!-- Rendered by client-side JavaScript -->
              </div>

              <!-- Calendar Status Legend -->
              <div class="cal-legend">
                <div class="legend-item">
                  <span class="legend-dot dot-event"></span>
                  <span>Acara</span>
                </div>
                <div class="legend-item">
                  <span class="legend-dot dot-partial"></span>
                  <span>Reservasi</span>
                </div>
                <div class="legend-item">
                  <span class="legend-dot dot-blocked"></span>
                  <span>Tutup Adat</span>
                </div>
              </div>
            </div>

            <!-- SUBCOLUMN B: Ketersediaan / Selected Date Availability Panel (50%) -->
            <div class="b-subcol-availability" id="calSlotCard">
              <div class="slot-details-top">
                <div class="slot-header-row">
                  <div class="slot-date-group">
                    <span class="slot-cal-icon" aria-hidden="true">
                      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                      </svg>
                    </span>
                    <div class="slot-date-editorial" id="slotCardDate" aria-label="Sabtu, 21 November 2026">
                      <span class="slot-date-day" id="slotDayName">Sabtu</span>
                      <span class="slot-date-month" id="slotDayMonth">21 November</span>
                      <span class="slot-date-year" id="slotYear">2026</span>
                    </div>
                  </div>
                  <div class="slot-status-pill status-available" id="slotCardBadge">Tersedia</div>
                </div>
                <div class="slot-instruction-text" id="slotCardHelp">Pilih salah satu sesi kunjungan yang tersedia:</div>
              </div>

              <!-- Visiting Time Slots List -->
              <div class="slot-options-list" id="slotOptionsList">
                <!-- Sesi Pagi -->
                <label class="slot-option-row is-available is-selected" id="slotRowMorning">
                  <input type="radio" name="selected_session" value="morning" checked class="slot-radio">
                  <div class="slot-row-content">
                    <span class="slot-time-icon" aria-hidden="true"><?php echo ob_icon('clock', ['size' => 15, 'color' => 'warm', 'raw' => true]); ?></span>
                    <span class="slot-time-label">08:30 – 12:30 WITA</span>
                  </div>
                  <span class="slot-row-badge badge-green" id="slotBadgeMorning">
                    <span class="indicator-dot dot-available"></span> Tersedia
                  </span>
                </label>

                <!-- Sesi Sore -->
                <label class="slot-option-row is-available" id="slotRowAfternoon">
                  <input type="radio" name="selected_session" value="afternoon" class="slot-radio">
                  <div class="slot-row-content">
                    <span class="slot-time-icon" aria-hidden="true"><?php echo ob_icon('clock', ['size' => 15, 'color' => 'warm', 'raw' => true]); ?></span>
                    <span class="slot-time-label">14:30 – 17:30 WITA</span>
                  </div>
                  <span class="slot-row-badge badge-green" id="slotBadgeAfternoon">
                    <span class="indicator-dot dot-available"></span> Tersedia
                  </span>
                </label>
              </div>

              <!-- Prominent Reservation CTA Button -->
              <a href="reservation.php?date=2026-11-21&session=morning" id="calReservationBtn" class="btn btn-terracotta btn-block btn-reservation-cta">
                <span>Reservasi Kunjungan</span>
                <span class="btn-arrow" aria-hidden="true">&rarr;</span>
              </a>

              <!-- Restrained Footer Notice -->
              <div class="slot-notice-text">
                <span class="notice-icon" aria-hidden="true"><?php echo ob_icon('info', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?></span>
                <span>Kuota kunjungan terbatas. Beberapa tanggal mungkin tidak tersedia karena kegiatan khusus atau hari suci adat.</span>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- ==================================================================
           RIGHT 50%: EVENT HIGHLIGHTS & EVENT SLIDER (2 Cards Desktop / 1 Mobile)
           ================================================================== -->
      <div class="agenda-events-col">
        <!-- Right Column Header with Title, Action & Slider Controls -->
        <div class="agenda-col-header">
          <div class="agenda-header-left">
            <div class="agenda-title-row">
              <span class="agenda-title-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
              </span>
              <h3 class="agenda-col-heading">Agenda &amp; Kegiatan Budaya</h3>
            </div>
            <p class="agenda-col-desc">
              Temukan lokakarya, sarasehan, dan kegiatan budaya di Omah Belumbungan.
            </p>
          </div>
          
          <div class="agenda-header-actions">
            <a href="events.php" class="link-view-all">
              <span>Lihat Semua Agenda</span>
              <span class="view-all-arrow" aria-hidden="true">&rarr;</span>
            </a>
            
            <!-- Compact Slider Navigation Controls -->
            <div class="slider-ctrl-group" aria-label="Navigasi Slider Agenda">
              <button type="button" class="slider-arrow-btn" id="eventSliderPrev" aria-label="Agenda sebelumnya" title="Sebelumnya">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
              </button>
              <span class="slider-page-indicator" id="eventSliderIndicator" aria-live="polite">1 / 2</span>
              <button type="button" class="slider-arrow-btn" id="eventSliderNext" aria-label="Agenda berikutnya" title="Berikutnya">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Event Slider Viewport -->
        <div class="events-slider-viewport" id="eventsSliderViewport">
          <div class="events-slider-track" id="eventsSliderTrack">
            <?php 
            $monthNamesAbbr = [
                1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
                5 => 'MEI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGU',
                9 => 'SEP', 10 => 'OKT', 11 => 'NOV', 12 => 'DES'
            ];
            foreach ($upcomingEvents as $ev):
                $dt = strtotime($ev['date']);
                $evDay = date('d', $dt);
                $evMonth = $monthNamesAbbr[(int)date('n', $dt)] ?? strtoupper(date('M', $dt));
                $evYear = date('Y', $dt);
                $categoryDisplay = strtoupper($ev['category'] ?? 'Kegiatan');
                $shortDesc = $ev['short_description'] ?? $ev['description'] ?? '';
            ?>
            <article class="event-slider-card">
              <div class="event-card-media">
                <img src="<?php echo htmlspecialchars($ev['image'] ?? 'assets/images/photo-01.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($ev['title']); ?>" 
                     class="event-card-photo" 
                     loading="lazy">
                
                <!-- Floating White Date Badge Overlay -->
                <div class="event-badge-overlay" aria-label="Tanggal Kegiatan: <?php echo "{$evDay} {$evMonth} {$evYear}"; ?>">
                  <span class="badge-day"><?php echo $evDay; ?></span>
                  <span class="badge-month"><?php echo $evMonth; ?></span>
                  <span class="badge-year"><?php echo $evYear; ?></span>
                </div>
              </div>

              <div class="event-card-content">
                <span class="event-category-eyebrow"><?php echo htmlspecialchars($categoryDisplay); ?></span>
                
                <h4 class="event-card-title">
                  <a href="events.php#<?php echo htmlspecialchars($ev['id']); ?>">
                    <?php echo htmlspecialchars($ev['short_title'] ?? $ev['title']); ?>
                  </a>
                </h4>

                <p class="event-card-desc">
                  <?php echo htmlspecialchars($shortDesc); ?>
                </p>

                <div class="event-card-meta-list">
                  <div class="meta-item">
                    <span class="meta-icon" aria-hidden="true"><?php echo ob_icon('clock', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?></span>
                    <span><?php echo htmlspecialchars($ev['time'] ?? '09:00 – 15:30 WITA'); ?></span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-icon" aria-hidden="true"><?php echo ob_icon('map-pin', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?></span>
                    <span><?php echo htmlspecialchars($ev['location'] ?? 'Omah Blumbungan, Sibang Kaja'); ?></span>
                  </div>
                </div>

                <div class="event-card-action">
                  <a href="events.php#<?php echo htmlspecialchars($ev['id']); ?>" class="event-action-link">
                    <span>Detail &amp; Daftar</span>
                    <span class="action-arrow" aria-hidden="true">&rarr;</span>
                  </a>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

    </div>

  </div>
</section>

<!-- Authoritative Data Payload for Interactive Calendar -->
<script>
  window.omahCalendarData = {
    initialYear: <?php echo $activeCalYear; ?>,
    initialMonth: <?php echo $activeCalMonth; ?>,
    defaultDate: '2026-11-21',
    defaultSession: 'morning',
    monthlyAvailableSlots: <?php echo $monthlyAvailableSlots; ?>,
    blackoutDates: <?php echo json_encode($settings['blackout_dates'] ?? []); ?>,
    bookings: <?php echo json_encode($allBookings); ?>,
    events: <?php echo json_encode($allEvents); ?>
  };
</script>

<!-- ========================================================================
     SECTION 7: RESERVATION & VISIT EXPERIENCE
     ======================================================================== -->
<section class="reservation-section" id="kunjungan">
  <div class="container reservation-split-grid">
    <!-- Kolom Kiri: Google Maps Interaktif & Petunjuk Rute Akses -->
    <div class="kunjungan-map-col">
      <div class="map-card-wrapper">
        <div class="map-header-bar">
          <div class="map-title-group">
            <span class="map-live-dot"></span>
            <strong>Peta Lokasi Omah Blumbungan</strong>
          </div>
          <a href="<?php echo htmlspecialchars($settings['maps_url'] ?? 'https://maps.app.goo.gl/B8ZZYqaLepbJ1r9g6'); ?>" target="_blank" rel="noopener noreferrer" class="map-external-link" title="Buka di Google Maps">
            <span>Buka di Maps &rarr;</span>
          </a>
        </div>

        <div class="map-frame-box">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3945.334!2d115.20566!3d-8.5667155!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd23dac2939fc3f%3A0x2161dacaa8e800a8!2sOm_Ah%20Blumbungan!5e0!3m2!1sen!2sid"
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            title="Peta Lokasi Omah Blumbungan">
          </iframe>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Rincian Lengkap Kontak & Pedoman Kunjungan -->
    <div class="reservation-content">
      <span class="eyebrow">LOKASI &amp; KUNJUNGAN</span>
      <h2 class="section-heading-editorial">Rencanakan Kunjungan &amp; Hubungi Kami</h2>
      <p>
        Omah Blumbungan menyambut kunjungan belajar, studi komparatif arsitektur, riset budaya, 
        dan sarasehan komunitas dengan reservasi terlebih dahulu demi menjaga keasrian pekarangan.
      </p>

      <!-- Informasi Kontak Lengkap -->
      <div class="kunjungan-contact-box">
        <div class="contact-line-item">
          <span class="c-line-icon"><?php echo ob_icon('map-pin', ['size' => 18, 'color' => 'primary', 'raw' => true]); ?></span>
          <div class="c-line-body">
            <strong>Alamat Lengkap:</strong>
            <span><?php echo htmlspecialchars($settings['address'] ?? 'Banjar Blumbungan, Desa Sibang Kaja, Kec. Abiansemal, Kab. Badung, Bali 80352'); ?></span>
          </div>
        </div>

        <div class="contact-line-item">
          <span class="c-line-icon"><?php echo ob_icon('clock', ['size' => 18, 'color' => 'primary', 'raw' => true]); ?></span>
          <div class="c-line-body">
            <strong>Jam Kunjungan Edukasi:</strong>
            <span><?php echo htmlspecialchars($settings['opening_hours'] ?? '08:30 - 17:30 WITA'); ?> (Berdasarkan jadwal terdaftar)</span>
          </div>
        </div>

        <div class="contact-line-item">
          <span class="c-line-icon"><?php echo ob_icon('message-circle', ['size' => 18, 'color' => 'primary', 'raw' => true]); ?></span>
          <div class="c-line-body">
            <strong>WhatsApp &amp; Layanan Jadwal:</strong>
            <div class="c-line-link-wrap">
              <a href="<?php echo htmlspecialchars($waLink); ?>" target="_blank" rel="noopener noreferrer" class="contact-wa-link">
                +<?php echo htmlspecialchars($settings['whatsapp'] ?? '6281234567890'); ?>
              </a>
              <span class="badge-fast">Respon Cepat</span>
            </div>
          </div>
        </div>

        <div class="contact-line-item">
          <span class="c-line-icon"><?php echo ob_icon('mail', ['size' => 18, 'color' => 'primary', 'raw' => true]); ?></span>
          <div class="c-line-body">
            <strong>Surat Elektronik (Email):</strong>
            <span>
              <a href="mailto:<?php echo htmlspecialchars($settings['email'] ?? 'omah.blumbungan@gmail.com'); ?>" class="contact-email-link">
                <?php echo htmlspecialchars($settings['email'] ?? 'omah.blumbungan@gmail.com'); ?>
              </a>
            </span>
          </div>
        </div>
      </div>

      <!-- 3 Visitor Benefits -->
      <div class="visitor-benefits-row">
        <div class="benefit-box">
          <div class="benefit-icon"><?php echo ob_icon('padi', ['size' => 22, 'color' => 'primary', 'raw' => true]); ?></div>
          <strong>Kuota terbatas</strong>
          <span>setiap hari</span>
        </div>

        <div class="benefit-box">
          <div class="benefit-icon"><?php echo ob_icon('lotus', ['size' => 22, 'color' => 'primary', 'raw' => true]); ?></div>
          <strong>Proteksi hari suci</strong>
          <span>(Nyepi, Galungan, Kuningan)</span>
        </div>

        <div class="benefit-box">
          <div class="benefit-icon"><?php echo ob_icon('user', ['size' => 22, 'color' => 'primary', 'raw' => true]); ?></div>
          <strong>Pendampingan</strong>
          <span>oleh pengelola</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="reservation-actions-row">
        <a href="reservation.php" class="btn btn-terracotta">
          <span>Formulir Reservasi</span>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </a>

        <a href="<?php echo htmlspecialchars($waLink); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-wood">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.053-1.077-.089-.47-.151-1.077-.42-1.85-1.089-.99-.857-1.636-1.923-1.884-2.349-.247-.426-.027-.656.12-.862.13-.182.288-.376.432-.564.116-.151.154-.257.23-.427.078-.171.039-.32-.02-.439-.058-.119-.526-1.268-.72-1.737-.189-.456-.381-.394-.526-.402-.135-.007-.289-.009-.443-.009-.154 0-.404.058-.615.289-.211.231-.808.789-.808 1.923s.827 2.231.942 2.385c.115.154 1.627 2.485 3.942 3.484 2.316.999 2.316.666 2.738.627.423-.039 1.365-.558 1.558-1.097.192-.539.192-1.001.135-1.098-.058-.096-.212-.154-.442-.269z"/>
          </svg>
          <span>Tanya via WhatsApp</span>
        </a>

        <a href="<?php echo htmlspecialchars($settings['maps_directions_url'] ?? 'https://www.google.com/maps/dir/?api=1&destination=-8.5667155,115.2082351'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-wood btn-maps-action" title="Buka rute navigasi Google Maps dari posisi Anda">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
          </svg>
          <span>Petunjuk Arah Google Maps</span>
          <span class="btn-arrow" aria-hidden="true">&rarr;</span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
