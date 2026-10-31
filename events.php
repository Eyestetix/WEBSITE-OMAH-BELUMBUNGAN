<?php
/**
 * OMAH BELUMBUNGAN - Agenda & Aktivitas Budaya
 * Editorial cultural event listing & workshops
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Agenda & Aktivitas Budaya';
$settings = get_settings();
$allEvents = get_events();

// Filter published events
$upcomingEvents = array_values(array_filter($allEvents, function($e) {
    return ($e['status'] ?? '') === 'upcoming';
}));
$completedEvents = array_values(array_filter($allEvents, function($e) {
    return ($e['status'] ?? '') === 'completed';
}));

// Sort upcoming events chronologically by date
usort($upcomingEvents, function($a, $b) {
    $timeA = !empty($a['date']) ? strtotime($a['date']) : PHP_INT_MAX;
    $timeB = !empty($b['date']) ? strtotime($b['date']) : PHP_INT_MAX;
    return $timeA <=> $timeB;
});

// Sort completed events descending by date (most recent first)
usort($completedEvents, function($a, $b) {
    $timeA = !empty($a['date']) ? strtotime($a['date']) : 0;
    $timeB = !empty($b['date']) ? strtotime($b['date']) : 0;
    return $timeB <=> $timeA;
});

// Dynamic Categories from available upcoming events
$categoryMap = [];
foreach ($upcomingEvents as $evt) {
    $rawCat = trim($evt['category'] ?? '');
    if ($rawCat !== '') {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $rawCat)));
        if (!isset($categoryMap[$slug])) {
            $categoryMap[$slug] = [
                'name' => $rawCat,
                'count' => 0
            ];
        }
        $categoryMap[$slug]['count']++;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section agenda-page-section">
  <div class="container">

    <!-- 1. PAGE HEADER -->
    <header class="agenda-page-header">
      <span class="section-tag">AGENDA &amp; AKTIVITAS BUDAYA</span>
      <h1 class="agenda-page-title">Lokakarya, Sarasehan, &amp; Dialog Terbuka</h1>
      <p class="agenda-page-desc">
        Temukan ruang untuk bertukar wawasan seputar arsitektur tradisional Bali, filosofi tata ruang, 
        dan seni ketukangannya di bawah naungan bale dan natah Omah Belumbungan.
      </p>
    </header>

    <!-- 2. EVENT FILTERS -->
    <?php if (!empty($upcomingEvents) && !empty($categoryMap)): ?>
    <div class="agenda-filter-bar" role="navigation" aria-label="Filter Kategori Agenda">
      <button type="button" class="agenda-filter-btn active" data-filter="all" aria-pressed="true">
        <span>Semua Agenda</span>
        <span class="filter-count"><?php echo count($upcomingEvents); ?></span>
      </button>
      <?php foreach ($categoryMap as $slug => $catInfo): ?>
      <button type="button" class="agenda-filter-btn" data-filter="<?php echo htmlspecialchars($slug); ?>" aria-pressed="false">
        <span><?php echo htmlspecialchars(ucwords(strtolower($catInfo['name']))); ?></span>
        <span class="filter-count"><?php echo $catInfo['count']; ?></span>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 3. UPCOMING EVENTS LIST -->
    <div class="agenda-list-container">
      <?php if (empty($upcomingEvents)): ?>
        <div class="agenda-empty-state">
          <div class="empty-icon"><?php echo ob_icon('calendar', ['size' => 48, 'color' => 'warm']); ?></div>
          <h3 class="empty-title">Belum Ada Agenda Terjadwal</h3>
          <p class="empty-desc">
            Saat ini belum ada agenda baru yang dijadwalkan dalam waktu dekat. Pantau terus halaman ini atau hubungi kami melalui WhatsApp untuk informasi kegiatan mendatang.
          </p>
          <a href="<?php echo htmlspecialchars(make_whatsapp_url($settings['whatsapp'] ?? '6281234567890', 'Om Swastyastu, saya ingin bertanya jadwal agenda dan lokakarya berikutnya di Omah Belumbungan.')); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-terracotta btn-empty-cta">
            <span>Tanya Jadwal via WhatsApp</span>
            <span class="btn-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </div>
      <?php else: ?>
        <div class="agenda-cards-stack" id="agendaCardsStack">
          <?php foreach ($upcomingEvents as $evt): 
            $evtId = $evt['id'] ?? ('evt_' . md5($evt['title'] ?? ''));
            $rawCat = trim($evt['category'] ?? 'Kegiatan');
            $catSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $rawCat)));
            
            $quota = (int)($evt['quota'] ?? 0);
            $registered = (int)($evt['registered'] ?? 0);
            $quotaRemaining = max(0, $quota - $registered);
            $isFull = ($quotaRemaining <= 0);
            $isClosed = (($evt['status'] ?? '') === 'closed');

            if ($isClosed) {
                $availStatus = 'closed';
                $availLabel = 'Pendaftaran Ditutup';
            } elseif ($isFull) {
                $availStatus = 'full';
                $availLabel = 'Kuota Penuh';
            } else {
                $availStatus = 'available';
                $availLabel = $quotaRemaining . ' Kursi Tersisa';
            }

            // Image handling with fallback
            $imagePath = $evt['image'] ?? '';
            $hasValidImage = !empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath);
            $displayImage = $hasValidImage ? $imagePath : 'assets/images/herobg.jpg';

            // Date Badge formatting for cover image
            $time = !empty($evt['date']) ? strtotime($evt['date']) : null;
            $dayNum = $time ? date('j', $time) : '';
            $monthShort = $time ? strtoupper(substr(['', 'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'][(int)date('n', $time)] ?? '', 0, 3)) : '';
            $yearNum = $time ? date('Y', $time) : '';

            // WhatsApp link
            $waMsg = "Om Swastyastu, saya ingin mendaftar untuk agenda: " . ($evt['title'] ?? '') . " pada tanggal " . format_date_id($evt['date'] ?? '');
            $registerLink = make_whatsapp_url($settings['whatsapp'] ?? '6281234567890', $waMsg);
          ?>
          <article class="agenda-card" id="<?php echo htmlspecialchars($evtId); ?>" data-category="<?php echo htmlspecialchars($catSlug); ?>">
            <!-- LEFT: Photograph (30-35% on desktop) -->
            <div class="agenda-card-media">
              <img src="<?php echo htmlspecialchars($displayImage); ?>" 
                   alt="Foto kegiatan <?php echo htmlspecialchars($evt['title'] ?? ''); ?>" 
                   class="agenda-card-img" 
                   loading="lazy">
              
              <?php if ($time): ?>
              <div class="agenda-media-date-badge" aria-label="<?php echo format_date_id($evt['date'] ?? ''); ?>">
                <span class="badge-day"><?php echo $dayNum; ?></span>
                <span class="badge-month"><?php echo $monthShort; ?></span>
                <span class="badge-year"><?php echo $yearNum; ?></span>
              </div>
              <?php endif; ?>
            </div>

            <!-- RIGHT: Information & Action (65-70% on desktop) -->
            <div class="agenda-card-info">
              <!-- A. Category & Availability Header -->
              <div class="agenda-card-status-row">
                <span class="agenda-cat-badge"><?php echo htmlspecialchars($rawCat); ?></span>
                <span class="agenda-dot-divider" aria-hidden="true">&middot;</span>
                <span class="agenda-avail-label status-<?php echo $availStatus; ?>">
                  <?php echo htmlspecialchars($availLabel); ?>
                </span>
              </div>

              <!-- B. Event Title -->
              <h2 class="agenda-card-title"><?php echo htmlspecialchars($evt['title'] ?? ''); ?></h2>

              <!-- C. Event Metadata Group -->
              <div class="agenda-metadata-grid">
                <!-- Date -->
                <div class="agenda-meta-item">
                  <span class="agenda-meta-icon"><?php echo ob_icon('calendar', ['size' => 15, 'color' => 'primary', 'raw' => true]); ?></span>
                  <div class="agenda-meta-content">
                    <span class="agenda-meta-label">Tanggal</span>
                    <strong class="agenda-meta-value"><?php echo format_date_id($evt['date'] ?? ''); ?></strong>
                  </div>
                </div>

                <!-- Time -->
                <div class="agenda-meta-item">
                  <span class="agenda-meta-icon"><?php echo ob_icon('clock', ['size' => 15, 'color' => 'primary', 'raw' => true]); ?></span>
                  <div class="agenda-meta-content">
                    <span class="agenda-meta-label">Waktu</span>
                    <span class="agenda-meta-value"><?php echo htmlspecialchars($evt['time'] ?? '-'); ?></span>
                  </div>
                </div>

                <!-- Location -->
                <div class="agenda-meta-item agenda-meta-full">
                  <span class="agenda-meta-icon"><?php echo ob_icon('map-pin', ['size' => 15, 'color' => 'primary', 'raw' => true]); ?></span>
                  <div class="agenda-meta-content">
                    <span class="agenda-meta-label">Lokasi</span>
                    <span class="agenda-meta-value"><?php echo htmlspecialchars($evt['location'] ?? 'Omah Belumbungan, Sibang Kaja'); ?></span>
                  </div>
                </div>

                <!-- Speaker (if available) -->
                <?php if (!empty($evt['speaker'])): ?>
                <div class="agenda-meta-item agenda-meta-full">
                  <span class="agenda-meta-icon"><?php echo ob_icon('user', ['size' => 15, 'color' => 'primary', 'raw' => true]); ?></span>
                  <div class="agenda-meta-content">
                    <span class="agenda-meta-label">Narasumber</span>
                    <div class="agenda-meta-value">
                      <strong><?php echo htmlspecialchars($evt['speaker']); ?></strong>
                      <?php if (!empty($evt['speaker_role'])): ?>
                        <span class="agenda-meta-subtext">(<?php echo htmlspecialchars($evt['speaker_role']); ?>)</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Fee (if applicable) -->
                <?php if (!empty($evt['fee'])): ?>
                <div class="agenda-meta-item agenda-meta-full">
                  <span class="agenda-meta-icon"><?php echo ob_icon('ticket', ['size' => 15, 'color' => 'primary', 'raw' => true]); ?></span>
                  <div class="agenda-meta-content">
                    <span class="agenda-meta-label">Biaya / Investasi</span>
                    <span class="agenda-meta-value"><?php echo htmlspecialchars($evt['fee']); ?></span>
                  </div>
                </div>
                <?php endif; ?>
              </div>

              <!-- D. Event Description -->
              <?php 
                $desc = !empty($evt['description']) ? $evt['description'] : ($evt['short_description'] ?? '');
              ?>
              <?php if (!empty($desc)): ?>
              <p class="agenda-card-desc">
                <?php echo htmlspecialchars($desc); ?>
              </p>
              <?php endif; ?>

              <!-- E. Capacity & Registration Action -->
              <div class="agenda-card-action-bar">
                <div class="agenda-capacity-badge">
                  <span class="capacity-icon"><?php echo ob_icon('users', ['size' => 15, 'color' => 'warm', 'raw' => true]); ?></span>
                  <span class="capacity-text">Kuota: <strong><?php echo $quota; ?> peserta</strong></span>
                </div>

                <div class="agenda-cta-wrapper">
                  <?php if ($isClosed): ?>
                    <button type="button" class="btn btn-secondary btn-agenda-cta" disabled>
                      <span>Pendaftaran Ditutup</span>
                    </button>
                  <?php elseif ($isFull): ?>
                    <button type="button" class="btn btn-secondary btn-agenda-cta" disabled>
                      <span>Kuota Penuh</span>
                    </button>
                  <?php else: ?>
                    <a href="<?php echo htmlspecialchars($registerLink); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn btn-terracotta btn-agenda-cta"
                       aria-label="Detail &amp; Daftar untuk <?php echo htmlspecialchars($evt['title'] ?? ''); ?>">
                      <span>Detail &amp; Daftar</span>
                      <span class="btn-arrow" aria-hidden="true">&rarr;</span>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

        <!-- Filter Empty State (Hidden by default, shown by JS if 0 cards match) -->
        <div class="agenda-filter-empty" id="agendaFilterEmpty" style="display: none;">
          <p>Tidak ada agenda dalam kategori yang dipilih.</p>
          <button type="button" class="btn btn-terracotta btn-sm" id="btnResetFilter" style="margin: 0 auto;">
            Tampilkan Semua Agenda
          </button>
        </div>
      <?php endif; ?>
    </div>

    <!-- 4. PAST EVENTS / DOCUMENTATION ARCHIVE -->
    <?php if (!empty($completedEvents)): ?>
    <div class="agenda-archive-section">
      <div class="agenda-archive-header">
        <h2 class="agenda-archive-title">Dokumentasi &amp; Arsip Agenda Sebelumnya</h2>
        <p class="agenda-archive-desc">
          Rekam jejak sarasehan, lokakarya, dan dialog kebudayaan yang telah terselenggara di Omah Belumbungan.
        </p>
      </div>

      <div class="agenda-archive-grid">
        <?php foreach ($completedEvents as $evt): 
          $hasImg = !empty($evt['image']) && file_exists(__DIR__ . '/' . $evt['image']);
          $archImg = $hasImg ? $evt['image'] : 'assets/images/herobg.jpg';
        ?>
        <article class="agenda-archive-card">
          <div class="archive-card-media">
            <img src="<?php echo htmlspecialchars($archImg); ?>" alt="<?php echo htmlspecialchars($evt['title'] ?? ''); ?>" loading="lazy">
            <span class="badge badge-completed">Telah Selesai</span>
          </div>
          <div class="archive-card-body">
            <span class="archive-cat"><?php echo htmlspecialchars($evt['category'] ?? 'Agenda'); ?></span>
            <h3 class="archive-title"><?php echo htmlspecialchars($evt['title'] ?? ''); ?></h3>
            <div class="archive-meta">
              <span><?php echo ob_icon('calendar', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?> <?php echo format_date_id($evt['date'] ?? ''); ?></span>
              <?php if (!empty($evt['speaker'])): ?>
                <span><?php echo ob_icon('user', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?> <?php echo htmlspecialchars($evt['speaker']); ?></span>
              <?php endif; ?>
            </div>
            <?php if (!empty($evt['description'])): ?>
            <p class="archive-desc"><?php echo htmlspecialchars($evt['description']); ?></p>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
