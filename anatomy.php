<?php
/**
 * OMAH BELUMBUNGAN — Dedicated Architectural Anatomy Detail Page
 * 
 * Halaman detail kultural & arsitektural untuk 9 elemen pekarangan tradisional Bali:
 * Angkul-angkul, Aling-aling, Natah, Bale Meten, Bale Dangin, Bale Dauh, Paon, Jineng, Pemerajan.
 */

require_once __DIR__ . '/includes/functions.php';

// Ambil parameter ID atau Slug
$requestedId = $_GET['id'] ?? $_GET['slug'] ?? '';

if (empty($requestedId)) {
    // Jika tidak ada ID yang dipilih, arahkan ke katalog lengkap
    header('Location: about.php#anatomi-lengkap');
    exit;
}

// Cari data elemen dari JSON sumber kebenaran tunggal
$item = get_anatomy_by_identifier($requestedId);

if (!$item) {
    // Bila ID tidak ditemukan, kembalikan ke katalog
    header('Location: about.php');
    exit;
}

// Metadata Halaman
$pageTitle = $item['name'] . ' — Anatomi Pekarangan Bali';
$metaDescription = $item['short_desc'] ?? 'Mengenal peran, makna filosofis, dan kaidah arsitektur Asta Kosala Kosali untuk ' . $item['name'] . ' di Omah Belumbungan.';

// Ambil navigasi elemen sebelumnya dan berikutnya sesuai urutan katalog
$adjacent = get_anatomy_adjacent($item['id']);
$prevItem = $adjacent['prev'];
$nextItem = $adjacent['next'];
$orderFormatted = sprintf('%02d', (int)($item['sort_order'] ?? 1));

// Icon pendukung sesuai identitas elemen
$anatomyIcons = [
    'angkul_angkul' => 'angkul-angkul',
    'aling_aling' => 'shield',
    'natah' => 'natah',
    'bale_meten' => 'bed',
    'bale_dangin' => 'sparkles',
    'bale_dauh' => 'bale',
    'paon' => 'utensils',
    'jineng' => 'padi',
    'pemerajan' => 'pemerajan',
];
$elemIcon = $anatomyIcons[$item['id']] ?? 'bale';

include __DIR__ . '/includes/header.php';
?>

<!-- ========================================================================
     A. BREADCRUMB NAVIGATION
     ======================================================================== -->
<nav class="anatomy-breadcrumb-bar" aria-label="Navigasi Breadcrumb">
  <div class="container">
    <ol class="anatomy-breadcrumb-list">
      <li><a href="index.php">Beranda</a></li>
      <li class="breadcrumb-separator" aria-hidden="true">/</li>
      <li><a href="about.php#anatomi-lengkap">Anatomi Pekarangan</a></li>
      <li class="breadcrumb-separator" aria-hidden="true">/</li>
      <li class="active" aria-current="page"><?php echo htmlspecialchars($item['name']); ?></li>
    </ol>
  </div>
</nav>

<!-- ========================================================================
     B. HERO & C. STRUCTURED INFORMATION
     ======================================================================== -->
<section class="anatomy-detail-hero-section">
  <div class="container">
    <div class="anatomy-hero-grid">
      
      <!-- Kolom Kiri: Judul, Ringkasan, & Informasi Terstruktur Vertikal -->
      <div class="anatomy-hero-info">
        
        <div class="anatomy-hero-badge-wrap">
          <span class="anatomy-order-tag">#<?php echo $orderFormatted; ?></span>
          <span class="anatomy-hero-badge">
            <?php echo ob_icon($elemIcon, ['size' => 16, 'color' => 'terracotta', 'raw' => true]); ?>
            <span><?php echo htmlspecialchars($item['zone'] ?? 'Mandala Arsitektur'); ?></span>
          </span>
        </div>

        <h1 class="anatomy-detail-title"><?php echo htmlspecialchars($item['name']); ?></h1>

        <p class="anatomy-detail-subtitle">
          <?php echo htmlspecialchars($item['short_desc']); ?>
        </p>

        <!-- Informasi Terstruktur Format Vertikal (Section 3.C) -->
        <div class="anatomy-spec-card">
          
          <div class="anatomy-spec-group">
            <span class="anatomy-spec-label">Orientasi &amp; Letak</span>
            <p class="anatomy-spec-value"><?php echo htmlspecialchars($item['orientation'] ?? '—'); ?></p>
          </div>

          <div class="anatomy-spec-group">
            <span class="anatomy-spec-label">Fungsi Utama</span>
            <p class="anatomy-spec-value"><?php echo htmlspecialchars($item['function'] ?? '—'); ?></p>
          </div>

          <div class="anatomy-spec-group">
            <span class="anatomy-spec-label">Material &amp; Karakter</span>
            <p class="anatomy-spec-value"><?php echo htmlspecialchars($item['materials'] ?? '—'); ?></p>
          </div>

        </div>

      </div>

      <!-- Kolom Kanan: Foto Dokumentasi Utama -->
      <div class="anatomy-hero-photo-wrap">
        <div class="anatomy-photo-frame">
          <span class="anatomy-photo-overlay-tag">#<?php echo $orderFormatted; ?> &bull; <?php echo htmlspecialchars($item['short_name'] ?? $item['name']); ?></span>
          <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/images/photo-01.jpg'); ?>" 
               alt="Dokumentasi arsitektur <?php echo htmlspecialchars($item['name']); ?> di Omah Belumbungan" 
               loading="eager">
        </div>
        <p class="anatomy-photo-caption">
          Dokumentasi fisik <?php echo htmlspecialchars($item['name']); ?> di kawasan cagar budaya Omah Belumbungan, Sibang Kaja.
        </p>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================================
     D. CULTURAL EXPLANATION (Kajian Filosofis & Konteks Budaya)
     ======================================================================== -->
<section class="anatomy-cultural-section">
  <div class="container">
    <div class="anatomy-cultural-grid">
      
      <!-- Kolom Konten Utama: Ulasan Budaya Bertingkat -->
      <div class="anatomy-narrative-col">
        
        <!-- 1. Makna Filosofis -->
        <?php if (!empty($item['philosophy'])): ?>
        <div class="anatomy-content-block">
          <div class="anatomy-block-header">
            <span class="anatomy-block-eyebrow">NILAI SPIRITUAL &amp; KOSMOLOGI</span>
            <h2 class="anatomy-block-heading">Makna Filosofis</h2>
          </div>
          <div class="anatomy-prose">
            <p><?php echo nl2br(htmlspecialchars($item['philosophy'])); ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- 2. Hubungan dengan Asta Kosala Kosali (Jika Ada) -->
        <?php if (!empty($item['asta_kosala_kosali'])): ?>
        <div class="anatomy-content-block">
          <div class="anatomy-block-header">
            <span class="anatomy-block-eyebrow">TATA UKUR &amp; PROPORSI RAGA</span>
            <h2 class="anatomy-block-heading">Hubungan dengan Asta Kosala Kosali</h2>
          </div>
          <div class="anatomy-prose">
            <p><?php echo nl2br(htmlspecialchars($item['asta_kosala_kosali'])); ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- 3. Hubungan dengan Tri Hita Karana (Jika Ada) -->
        <?php if (!empty($item['tri_hita_karana'])): ?>
        <div class="anatomy-content-block">
          <div class="anatomy-block-header">
            <span class="anatomy-block-eyebrow">KESEIMBANGAN RELASIONAL</span>
            <h2 class="anatomy-block-heading">Hubungan dengan Tri Hita Karana</h2>
          </div>
          <div class="anatomy-prose">
            <p><?php echo nl2br(htmlspecialchars($item['tri_hita_karana'])); ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- 4. Konteks Arsitektural di Omah Belumbungan (Jika Ada) -->
        <?php if (!empty($item['detail_article'])): ?>
        <div class="anatomy-content-block">
          <div class="anatomy-block-header">
            <span class="anatomy-block-eyebrow">CATATAN KONSERVASI &amp; UNDAGI</span>
            <h2 class="anatomy-block-heading">Konteks Arsitektural di Omah Belumbungan</h2>
          </div>
          <div class="anatomy-prose">
            <p><?php echo nl2br(htmlspecialchars($item['detail_article'])); ?></p>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <!-- Kolom Samping: Ringkasan & Panduan Tur -->
      <aside class="anatomy-sidebar-col">
        
        <div class="anatomy-sidebar-card">
          <h3 class="anatomy-sidebar-card-title">Kearifan Undagi Tradisional</h3>
          <p>
            Setiap sendi dan ikatan kayu pada <strong><?php echo htmlspecialchars($item['name']); ?></strong> dibangun dengan teknik sambungan purus dan pasak kayu (tanpa paku besi cor) yang fleksibel meredam getaran seismik alami pulau Bali.
          </p>
          <div style="font-size: 0.85rem; color: var(--forest-green); font-weight: 600; padding: 12px; background: #FAF7F2; border-radius: 8px;">
            📍 Terletak di kompleks pekarangan Omah Belumbungan, Desa Sibang Kaja, Abiansemal, Badung.
          </div>
        </div>

        <div class="anatomy-sidebar-card" style="border-left: 4px solid var(--terracotta);">
          <h3 class="anatomy-sidebar-card-title">Amati Langsung di Lokasi</h3>
          <p>
            Ingin mengukur proporsi depa dan mengamati konstruksi kayu ini secara langsung bersama undagi lokal?
          </p>
          <a href="reservation.php" class="btn btn-terracotta" style="width: 100%; justify-content: center;">
            <span>Rencanakan Kunjungan</span>
            <span aria-hidden="true">&rarr;</span>
          </a>
        </div>

      </aside>

    </div>
  </div>
</section>

<!-- ========================================================================
     E. RELATED ANATOMY NAVIGATION (Sebelumnya, Jelajahi Semua, Berikutnya)
     ======================================================================== -->
<nav class="anatomy-nav-bar" aria-label="Navigasi Jelajah Elemen Pekarangan">
  <div class="container">
    <div class="anatomy-nav-row">
      
      <!-- Tombol Elemen Sebelumnya -->
      <?php if ($prevItem): ?>
        <a href="anatomy.php?id=<?php echo urlencode($prevItem['slug'] ?? $prevItem['id']); ?>" 
           class="anatomy-nav-item anatomy-nav-prev" 
           title="Buka elemen sebelumnya: <?php echo htmlspecialchars($prevItem['name']); ?>">
          <span class="nav-direction">&larr; Elemen Sebelumnya</span>
          <span class="nav-elem-title">#<?php echo sprintf('%02d', (int)($prevItem['sort_order'] ?? 1)); ?> <?php echo htmlspecialchars($prevItem['name']); ?></span>
        </a>
      <?php else: ?>
        <div class="anatomy-nav-item anatomy-nav-prev disabled">
          <span class="nav-direction">&larr; Titik Awal Tur</span>
          <span class="nav-elem-title">Elemen Pertama Pekarangan</span>
        </div>
      <?php endif; ?>

      <!-- Tombol Tengah: Jelajahi Seluruh Anatomi -->
      <a href="about.php#anatomi-lengkap" class="anatomy-nav-center btn btn-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <span>Jelajahi Seluruh Anatomi</span>
      </a>

      <!-- Tombol Elemen Berikutnya -->
      <?php if ($nextItem): ?>
        <a href="anatomy.php?id=<?php echo urlencode($nextItem['slug'] ?? $nextItem['id']); ?>" 
           class="anatomy-nav-item anatomy-nav-next" 
           title="Buka elemen berikutnya: <?php echo htmlspecialchars($nextItem['name']); ?>">
          <span class="nav-direction">Elemen Berikutnya &rarr;</span>
          <span class="nav-elem-title">#<?php echo sprintf('%02d', (int)($nextItem['sort_order'] ?? 2)); ?> <?php echo htmlspecialchars($nextItem['name']); ?></span>
        </a>
      <?php else: ?>
        <div class="anatomy-nav-item anatomy-nav-next disabled">
          <span class="nav-direction">Puncak Sakral &rarr;</span>
          <span class="nav-elem-title">Elemen Terakhir Pekarangan</span>
        </div>
      <?php endif; ?>

    </div>
  </div>
</nav>

<?php include __DIR__ . '/includes/footer.php'; ?>
