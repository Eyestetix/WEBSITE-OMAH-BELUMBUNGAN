<?php
/**
 * OMAH BELUMBUNGAN - Kontak & Panduan Lokasi
 * Lokasi: Banjar Piadan, Desa Sibang Kaja, Kec. Abiansemal, Kab. Badung, Bali
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Kontak & Petunjuk Arah';
$settings = get_settings();

include __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 50px;">
  <div class="container">
    <div style="max-width: 800px; margin-bottom: 40px;">
      <span class="section-tag">Lokasi & Kemitraan</span>
      <h1 class="section-title">Temui Kami di Sibang Kaja</h1>
      <p class="section-desc">
        Omah Belumbungan terletak di lingkungan asri Desa Sibang Kaja, Abiansemal, Badung. 
        Dikelilingi udara sejuk persawahan dan kehidupan banjar yang kental, berjarak sekitar 25 menit dari Kota Denpasar atau Ubud.
      </p>
    </div>

    <div class="reservation-layout" style="margin-bottom: 50px;">
      <!-- Informasi Kontak & Alamat -->
      <div class="form-card">
        <h2 style="font-size: 1.5rem; margin-bottom: 20px;">Informasi Alamat & Akses</h2>

        <div style="margin-bottom: 24px;">
          <strong style="display: flex; align-items: center; gap: 8px; color: var(--wood-dark); margin-bottom: 4px;">
            <?php echo ob_icon('map-pin', ['size' => 17, 'color' => 'primary']); ?>
            <span>Alamat Lengkap:</span>
          </strong>
          <p style="color: var(--text-muted); font-size: 0.95rem; margin-left: 25px;">
            <?php echo htmlspecialchars($settings['address'] ?? 'Banjar Piadan, Desa Sibang Kaja, Kec. Abiansemal, Kab. Badung, Bali 80352'); ?>
          </p>
        </div>

        <div style="margin-bottom: 24px;">
          <strong style="display: flex; align-items: center; gap: 8px; color: var(--wood-dark); margin-bottom: 4px;">
            <?php echo ob_icon('clock', ['size' => 17, 'color' => 'primary']); ?>
            <span>Jam Operasional:</span>
          </strong>
          <p style="color: var(--text-muted); font-size: 0.95rem; margin-left: 25px;">
            <?php echo htmlspecialchars($settings['opening_hours'] ?? '08:30 - 18:00 WITA'); ?> (Setiap Hari, kecuali Hari Raya Nyepi & Upacara Adat Banjar)
          </p>
        </div>

        <div style="margin-bottom: 24px;">
          <strong style="display: flex; align-items: center; gap: 8px; color: var(--wood-dark); margin-bottom: 4px;">
            <?php echo ob_icon('message-circle', ['size' => 17, 'color' => 'primary']); ?>
            <span>WhatsApp Layanan:</span>
          </strong>
          <p style="color: var(--text-muted); font-size: 0.95rem; margin-left: 25px;">
            +<?php echo htmlspecialchars($settings['whatsapp'] ?? '6281234567890'); ?>
          </p>
        </div>

        <div style="margin-bottom: 30px;">
          <strong style="display: flex; align-items: center; gap: 8px; color: var(--wood-dark); margin-bottom: 4px;">
            <?php echo ob_icon('mail', ['size' => 17, 'color' => 'primary']); ?>
            <span>Surat Elektronik (Email):</span>
          </strong>
          <p style="color: var(--text-muted); font-size: 0.95rem; margin-left: 25px;">
            <?php echo htmlspecialchars($settings['email'] ?? 'salam@omahbelumbungan.com'); ?>
          </p>
        </div>

        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
          <a href="<?php echo htmlspecialchars($settings['maps_url'] ?? 'https://maps.google.com'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
            <?php echo ob_icon('compass', ['size' => 16, 'color' => 'white']); ?>
            <span>Buka di Google Maps</span>
          </a>
          <?php 
          $waLink = make_whatsapp_url($settings['whatsapp'] ?? '6281234567890', "Om Swastyastu, salam hangat. Saya ingin menanyakan informasi lokasi dan kunjungan ke Omah Belumbungan.");
          ?>
          <a href="<?php echo htmlspecialchars($waLink); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" style="border-color: #25D366; color: #1E8E3E !important; display: inline-flex; align-items: center; gap: 8px;">
            <?php echo ob_icon('whatsapp', ['size' => 18, 'raw' => true]); ?>
            <span>Chat Langsung Pengelola</span>
          </a>
        </div>
      </div>

      <!-- Akses & Petunjuk Rute -->
      <div class="guidelines-box">
        <h3>Petunjuk Rute Menuju Lokasi</h3>
        <ul class="guidelines-list" style="margin-bottom: 20px;">
          <li><strong>Dari Denpasar / Renon (± 25 Menit):</strong> Arahkan kendaraan ke utara melewati Jl. Gatot Subroto Timur &rarr; Jl. Raya Penatih &rarr; perempatan Mambal ke arah barat menuju Desa Sibang Kaja.</li>
          <li><strong>Dari Ubud (± 20 Menit):</strong> Melalui Sayan &rarr; Tebongkang &rarr; Singapadu &rarr; jembatan Sungai Ayung menuju Jl. Raya Sibang Kaja.</li>
          <li><strong>Akses Bus Sekolah / Rombongan:</strong> Jalan utama desa dapat diakses hingga bus medium (kapasitas 30-35 seat). Tersedia area penurunan penumpang di dekat banjar.</li>
        </ul>

        <div style="background: var(--paras-bg); padding: 18px; border-radius: var(--radius-sm); border-left: 3px solid var(--terracotta);">
          <strong style="color: var(--wood-dark); display: block; margin-bottom: 4px;">Titik Patokan Lingkungan:</strong>
          <span style="font-size: 0.88rem; color: var(--text-muted);">
            Berada di kawasan Banjar Piadan, dekat dengan aliran jalur irigasi subak dan pepohonan rindang khas pedesaan Badung tengah.
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
