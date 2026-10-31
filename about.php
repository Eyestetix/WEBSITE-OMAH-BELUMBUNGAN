<?php
/**
 * OMAH BELUMBUNGAN - Anatomi & Filosofi Arsitektur Tradisional Bali
 */
require_once __DIR__ . '/includes/functions.php';

// Jika pengunjung mengakses dengan ?id= atau ?slug=, arahkan ke Dedicated Detail Page
if (!empty($_GET['id']) || !empty($_GET['slug'])) {
    $param = $_GET['id'] ?? $_GET['slug'];
    header('Location: anatomy.php?id=' . urlencode($param));
    exit;
}

$pageTitle = 'Anatomi Rumah Tradisional Bali';
$anatomyList = get_anatomy(true);

include __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 50px;">
  <div class="container">
    <!-- Hero Header Halaman Anatomi -->
    <div class="about-hero-header">
      <span class="section-tag">Kearifan Asta Kosala Kosali</span>
      <h1 class="about-hero-title">Anatomi Pekarangan Tradisional Bali</h1>
      <p class="about-hero-lead">
        Pekarangan rumah tradisional Bali bukan sekadar kumpulan bangunan tempat berteduh, melainkan perwujudan 
        filosofis <strong>Tri Hita Karana</strong> (harmoni antara Sang Pencipta, sesama manusia, dan alam semesta) 
        serta tata kelola orientasi <strong>Sanga Mandala</strong> dan ukuran tubuh manusia berdasar kitab <strong>Asta Kosala Kosali</strong>.
      </p>
    </div>

    <!-- 3 Pilar Filosofi Inti Grid -->
    <div class="philosophy-pillars-grid">
      
      <!-- Pilar 1: Tri Angga -->
      <div class="philosophy-pillar-card pillar-terracotta">
        <div class="pillar-card-header">
          <div class="pillar-icon-badge">
            <?php echo ob_icon('lotus', ['size' => 22, 'raw' => true]); ?>
          </div>
          <div class="pillar-header-meta">
            <span class="pillar-card-eyebrow">Kosmologi Vertikal</span>
            <h3 class="pillar-card-title">1. Tri Angga (Tiga Tingkatan)</h3>
          </div>
        </div>
        <p class="pillar-card-desc">
          Pekarangan dianalogikan seperti raga manusia: <em>Utama</em> (kepala/sakral: Pemerajan), <em>Madya</em> (tubuh/kegiatan sosial: Natah &amp; Bale), dan <em>Nista</em> (kaki/hilir: Paon &amp; Jineng).
        </p>
      </div>

      <!-- Pilar 2: Poros Kaja - Kelod -->
      <div class="philosophy-pillar-card pillar-forest">
        <div class="pillar-card-header">
          <div class="pillar-icon-badge">
            <?php echo ob_icon('compass', ['size' => 22, 'raw' => true]); ?>
          </div>
          <div class="pillar-header-meta">
            <span class="pillar-card-eyebrow">Orientasi Sakral</span>
            <h3 class="pillar-card-title">2. Poros Kaja - Kelod</h3>
          </div>
        </div>
        <p class="pillar-card-desc">
          Orientasi suci mengarah ke <em>Kaja</em> (arah gunung luhur/Gunung Agung sebagai hulu kesucian) dan <em>Kelod</em> (arah laut sebagai muara pelabuhan energi).
        </p>
      </div>

      <!-- Pilar 3: Proporsi Raga Pemilik -->
      <div class="philosophy-pillar-card pillar-gold">
        <div class="pillar-card-header">
          <div class="pillar-icon-badge">
            <?php echo ob_icon('maximize', ['size' => 22, 'raw' => true]); ?>
          </div>
          <div class="pillar-header-meta">
            <span class="pillar-card-eyebrow">Ukuran Tradisional</span>
            <h3 class="pillar-card-title">3. Proporsi Raga Pemilik</h3>
          </div>
        </div>
        <p class="pillar-card-desc">
          Setiap ukuran pintu, tiang, dan jarak antar bale diukur langsung dari anatomi tubuh kepala keluarga menggunakan satuan tradisional: <em>depa, musti, gumi, dan lengkat</em>.
        </p>
      </div>

    </div>

    <!-- Katalog Arsitektur Anatomi Pekarangan -->
    <div id="anatomi-lengkap" style="scroll-margin-top: 100px;"></div>
    <div class="anatomy-catalog-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: 28px;">
      <div style="max-width: 640px;">
        <span class="eyebrow-accent" style="font-size: 0.82rem; font-weight: 800; color: var(--terracotta); letter-spacing: 1px; text-transform: uppercase;">KATALOG ARSITEKTUR</span>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3vw, 2.2rem); margin: 6px 0 0; color: var(--forest-green); line-height: 1.2;">Mengenal Anatomi Pekarangan Bali</h2>
      </div>
      <p style="font-size: 0.95rem; color: var(--stone-text); max-width: 520px; margin: 0; line-height: 1.6;">
        Jelajahi berbagai bangunan, ruang, dan elemen arsitektur Omah Belumbungan beserta fungsi, material, orientasi, dan filosofi tradisional yang melatarbelakanginya.
      </p>
    </div>
    
    <div class="anatomy-editorial-stack">
      <?php foreach ($anatomyList as $item): 
        $orderNum = sprintf('%02d', (int)($item['sort_order'] ?? 1));
        $imgSrc = (!empty($item['image']) && file_exists(__DIR__ . '/' . $item['image'])) 
            ? $item['image'] 
            : 'assets/images/photo-01.jpg';
        $detailUrl = 'anatomy.php?id=' . urlencode($item['slug'] ?? $item['id']);
      ?>
      <article class="anatomy-editorial-card" id="<?php echo htmlspecialchars($item['id']); ?>">
        
        <!-- Kolom Kiri: Foto Arsitektur (28–32%) -->
        <div class="anatomy-editorial-media">
          <a href="<?php echo htmlspecialchars($detailUrl); ?>" 
             class="anatomy-media-link" 
             aria-label="Lihat rincian arsitektur <?php echo htmlspecialchars($item['name']); ?>">
            <div class="anatomy-media-frame">
              <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                   alt="Dokumentasi arsitektur <?php echo htmlspecialchars($item['name']); ?> di Omah Belumbungan" 
                   loading="lazy" 
                   class="anatomy-media-img">
              <span class="anatomy-media-tag">#<?php echo $orderNum; ?></span>
            </div>
          </a>
        </div>

        <!-- Kolom Kanan: Konten Terstruktur (68–72%) -->
        <div class="anatomy-editorial-content">
          
          <!-- 1. Kategori & 2. Nomor Elemen -->
          <div class="anatomy-card-eyebrow-row">
            <span class="anatomy-card-category"><?php echo htmlspecialchars(strtoupper($item['zone'] ?? 'MANDALA')); ?></span>
            <span class="anatomy-card-number">#<?php echo $orderNum; ?></span>
          </div>

          <!-- 3. Nama Elemen (Heading Serif Hijau Hutan) -->
          <h3 class="anatomy-card-title">
            <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="anatomy-title-link">
              <?php echo htmlspecialchars($item['name']); ?>
            </a>
          </h3>

          <!-- 4. Deskripsi Ringkas -->
          <p class="anatomy-card-desc">
            <?php echo htmlspecialchars($item['short_desc'] ?: $item['philosophy']); ?>
          </p>

          <!-- 5. Bold "Fungsi Utama" & Penjelasan di Bawahnya -->
          <div class="anatomy-card-field">
            <strong class="anatomy-card-label">Fungsi Utama</strong>
            <p class="anatomy-card-value"><?php echo htmlspecialchars($item['function']); ?></p>
          </div>

          <!-- 6. Bold "Arah & Material" & Penjelasan di Bawahnya -->
          <div class="anatomy-card-field">
            <strong class="anatomy-card-label">Arah &amp; Material</strong>
            <p class="anatomy-card-value">
              <?php echo htmlspecialchars($item['orientation']); ?> &bull; <em><?php echo htmlspecialchars($item['materials']); ?></em>
            </p>
          </div>

          <!-- 7. Terracotta "Pelajari Rincian →" Link -->
          <div class="anatomy-card-action">
            <a href="<?php echo htmlspecialchars($detailUrl); ?>" 
               class="anatomy-detail-cta" 
               aria-label="Pelajari rincian lengkap <?php echo htmlspecialchars($item['name']); ?>">
              <span>Pelajari Rincian</span>
              <span class="cta-arrow" aria-hidden="true">&rarr;</span>
            </a>
          </div>

        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Ajakan Kunjungan Studi -->
    <div style="background: var(--paras-card); border: 1px solid var(--paras-border); border-radius: var(--radius-lg); padding: 40px; margin-top: 60px; text-align: center;">
      <h3 style="font-size: 1.6rem; margin-bottom: 12px;">Ingin Mengamati dan Mengukur Langsung di Lapangan?</h3>
      <p style="color: var(--text-muted); max-width: 650px; margin: 0 auto 24px;">
        Kami menyediakan sesi tur pemanduan khusus arsitektur untuk sekolah, perguruan tinggi, maupun peneliti independen di Sibang Kaja, Abiansemal.
      </p>
      <a href="reservation.php?type=school_study" class="btn btn-primary">Reservasi Kunjungan Studi Sekolah</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
