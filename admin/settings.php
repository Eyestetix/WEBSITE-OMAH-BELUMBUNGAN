<?php
/**
 * OMAH BELUMBUNGAN - Pengaturan Kawasan & Sistem CMS
 * Modular Tabs: Kontak & Lokasi | Identitas & Website | Kapasitas & Libur Adat
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$currentTab = $_GET['tab'] ?? 'identity';
$tabTitles = [
    'identity' => 'Pengaturan Website & Identitas',
    'slideshow' => 'Slideshow Hero Beranda',
    'contact' => 'Kontak & Lokasi',
    'capacity' => 'Kapasitas & Hari Libur Adat',
    'security' => 'Keamanan & Kata Sandi Admin'
];
$adminTitle = $tabTitles[$currentTab] ?? 'Pengaturan Kawasan';
require_once __DIR__ . '/header.php';

$settings = get_settings();

// Tangani simpan konfigurasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $submittedTab = $_POST['tab'] ?? 'identity';

    if ($submittedTab === 'contact') {
        $settings['whatsapp'] = sanitize($_POST['whatsapp'] ?? '');
        $settings['phone'] = sanitize($_POST['phone'] ?? '');
        $settings['email'] = sanitize($_POST['email'] ?? '');
        $settings['address'] = sanitize($_POST['address'] ?? '');
        $settings['maps_url'] = sanitize($_POST['maps_url'] ?? '');
        $settings['opening_hours'] = sanitize($_POST['opening_hours'] ?? '08:30 - 18:00 WITA');
        set_flash('success', 'Informasi <strong>Kontak &amp; Lokasi Kawasan</strong> berhasil diperbarui.');
    } 
    elseif ($submittedTab === 'identity') {
        $settings['site_name'] = sanitize($_POST['site_name'] ?? 'Omah Belumbungan');
        $settings['tagline'] = sanitize($_POST['tagline'] ?? '');
        $settings['description'] = sanitize($_POST['description'] ?? '');
        $settings['instagram'] = sanitize($_POST['instagram'] ?? '');
        $settings['youtube'] = sanitize($_POST['youtube'] ?? '');
        $settings['facebook'] = sanitize($_POST['facebook'] ?? '');
        
        $settings['featured_event_popup']['enabled'] = isset($_POST['popup_enabled']);
        $settings['featured_event_popup']['event_id'] = sanitize($_POST['popup_event_id'] ?? 'evt_01');
        $settings['featured_event_popup']['show_delay_seconds'] = (int)($_POST['popup_delay'] ?? 2);
        set_flash('success', 'Konfigurasi <strong>Identitas &amp; Tampilan Website</strong> berhasil disimpan.');
    } 
    elseif ($submittedTab === 'slideshow') {
        $slides = $_POST['slides'] ?? [];
        $cleanSlides = [];
        if (is_array($slides)) {
            foreach ($slides as $sl) {
                $sl = trim($sl);
                if (!empty($sl)) {
                    $cleanSlides[] = $sl;
                }
            }
        }

        // Tangani unggah foto baru dari komputer secara aman
        if (!empty($_FILES['new_slide_file']['name']) && $_FILES['new_slide_file']['error'] === UPLOAD_ERR_OK) {
            $tmpFile = $_FILES['new_slide_file']['tmp_name'];
            $origName = $_FILES['new_slide_file']['name'];
            $uploadRes = upload_image_secure($tmpFile, $origName, 'hero');
            if ($uploadRes['success']) {
                $cleanSlides[] = $uploadRes['path'];
            }
        }

        if (empty($cleanSlides)) {
            $cleanSlides = ['assets/images/herobg.jpg'];
        }

        $settings['hero_slideshow'] = array_values(array_unique($cleanSlides));
        $interval = (int)($_POST['hero_slideshow_interval'] ?? 5);
        $settings['hero_slideshow_interval'] = max(2, min(30, $interval));

        set_flash('success', 'Konfigurasi <strong>Slideshow Background Hero Beranda</strong> berhasil disimpan.');
    }
    elseif ($submittedTab === 'capacity') {
        $settings['capacity'] = [
            'school_study' => max(1, (int)($_POST['capacity_school'] ?? 80)),
            'seminar_workshop' => max(1, (int)($_POST['capacity_seminar'] ?? 50)),
            'prewedding_slots' => max(1, (int)($_POST['capacity_prewedding'] ?? 2)),
            'gathering' => max(1, (int)($_POST['capacity_gathering'] ?? 70))
        ];

        // Tangani tanggal blackout (libur adat / hari suci)
        $rawBlackouts = explode("\n", str_replace("\r", "", $_POST['blackout_dates'] ?? ''));
        $cleanBlackouts = [];
        foreach ($rawBlackouts as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
                $cleanBlackouts[] = $trimmed;
            }
        }
        sort($cleanBlackouts);
        $settings['blackout_dates'] = array_values(array_unique($cleanBlackouts));
        set_flash('success', 'Batas <strong>Kapasitas &amp; Tanggal Libur Adat</strong> berhasil diperbarui.');
    }
    elseif ($submittedTab === 'security') {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $users = get_json_data('users.json');
        $currentUser = get_logged_in_user();
        $foundUser = false;

        foreach ($users as &$u) {
            if ($u['id'] === $currentUser['id'] || $u['username'] === $currentUser['username']) {
                $foundUser = true;
                if (!password_verify($oldPassword, $u['password'])) {
                    set_flash('error', 'Kata sandi saat ini yang Anda masukkan tidak cocok.');
                } elseif (strlen($newPassword) < 8) {
                    set_flash('error', 'Kata sandi baru minimal harus 8 karakter untuk keamanan.');
                } elseif ($newPassword !== $confirmPassword) {
                    set_flash('error', 'Konfirmasi kata sandi baru tidak sesuai.');
                } else {
                    $u['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
                    save_json_data('users.json', $users);
                    set_flash('success', 'Kata sandi akun administrator berhasil diperbarui! Silakan gunakan kata sandi baru untuk login.');
                }
                break;
            }
        }
        if (!$foundUser) {
            set_flash('error', 'Data pengguna administrator tidak ditemukan.');
        }
        header('Location: settings.php?tab=security');
        exit;
    }

    save_json_data('settings.json', $settings);
    header('Location: settings.php?tab=' . urlencode($submittedTab));
    exit;
}
?>

<!-- Tab Switcher Navigation -->
<div class="adm-tabs-bar">
  <a href="settings.php?tab=identity" class="adm-tab-btn <?php echo ($currentTab === 'identity') ? 'active' : ''; ?>">
    <?php echo ob_icon('settings', ['size' => 15, 'raw' => true]); ?>
    <span>Identitas &amp; Website</span>
  </a>
  <a href="settings.php?tab=slideshow" class="adm-tab-btn <?php echo ($currentTab === 'slideshow') ? 'active' : ''; ?>">
    <?php echo ob_icon('camera', ['size' => 15, 'raw' => true]); ?>
    <span>Slideshow Hero</span>
    <span class="tab-count"><?php echo count($settings['hero_slideshow'] ?? []); ?></span>
  </a>
  <a href="settings.php?tab=contact" class="adm-tab-btn <?php echo ($currentTab === 'contact') ? 'active' : ''; ?>">
    <?php echo ob_icon('map-pin', ['size' => 15, 'raw' => true]); ?>
    <span>Kontak &amp; Lokasi</span>
  </a>
  <a href="settings.php?tab=capacity" class="adm-tab-btn <?php echo ($currentTab === 'capacity') ? 'active' : ''; ?>">
    <?php echo ob_icon('calendar', ['size' => 15, 'raw' => true]); ?>
    <span>Kapasitas &amp; Libur Adat</span>
    <?php if (!empty($settings['blackout_dates'])): ?>
      <span class="tab-count"><?php echo count($settings['blackout_dates']); ?></span>
    <?php endif; ?>
  </a>
  <a href="settings.php?tab=security" class="adm-tab-btn <?php echo ($currentTab === 'security') ? 'active' : ''; ?>">
    <?php echo ob_icon('shield', ['size' => 15, 'raw' => true]); ?>
    <span>Keamanan &amp; Akun</span>
  </a>
</div>

<div style="max-width: 860px;">
  <!-- ========================================== -->
  <!-- TAB 1: IDENTITAS & WEBSITE                 -->
  <!-- ========================================== -->
  <?php if ($currentTab === 'identity'): ?>
    <div class="adm-card">
      <div class="adm-card-header">
        <div>
          <h2 class="adm-card-title">
            <span>Identitas &amp; Profil Publik Kawasan</span>
          </h2>
          <div class="adm-card-subtitle">Pengaturan nama, slogan, narasi misi, dan kanal media sosial resmi</div>
        </div>
      </div>

      <form action="settings.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tab" value="identity">

        <div class="adm-form-group">
          <label for="site_name" class="adm-label">Nama Resmi Kawasan *</label>
          <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Omah Belumbungan'); ?>" required class="adm-input">
        </div>

        <div class="adm-form-group">
          <label for="tagline" class="adm-label">Slogan / Tagline Publik</label>
          <input type="text" id="tagline" name="tagline" value="<?php echo htmlspecialchars($settings['tagline'] ?? ''); ?>" placeholder="Contoh: Ruang Budaya & Edukasi Tradisional Arsitektur Bali" class="adm-input">
        </div>

        <div class="adm-form-group">
          <label for="description" class="adm-label">Deskripsi Profil &amp; Nilai Filosofis (About Summary)</label>
          <textarea id="description" name="description" rows="4" class="adm-textarea"><?php echo htmlspecialchars($settings['description'] ?? ''); ?></textarea>
        </div>

        <div class="adm-form-row">
          <div class="adm-form-group">
            <label for="instagram" class="adm-label">URL Akun Instagram</label>
            <input type="text" id="instagram" name="instagram" value="<?php echo htmlspecialchars($settings['instagram'] ?? ''); ?>" placeholder="https://instagram.com/omahbelumbungan" class="adm-input">
          </div>
          <div class="adm-form-group">
            <label for="youtube" class="adm-label">URL Kanal YouTube</label>
            <input type="text" id="youtube" name="youtube" value="<?php echo htmlspecialchars($settings['youtube'] ?? ''); ?>" placeholder="https://youtube.com/@omahbelumbungan" class="adm-input">
          </div>
        </div>

        <div class="adm-form-group">
          <label for="facebook" class="adm-label">URL Halaman Facebook</label>
          <input type="text" id="facebook" name="facebook" value="<?php echo htmlspecialchars($settings['facebook'] ?? ''); ?>" placeholder="https://facebook.com/omahbelumbungan" class="adm-input">
        </div>

        <!-- Pengaturan Pop-up Pengumuman Beranda -->
        <div style="background: var(--adm-surface-alt); padding: 18px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border); margin: 24px 0;">
          <h3 style="font-size: 0.95rem; font-family: var(--adm-font-serif); color: var(--adm-forest); margin-bottom: 8px;">
            Pop-up Pengumuman Agenda di Beranda
          </h3>
          <label class="adm-checkbox-label" style="margin-bottom: 12px;">
            <input type="checkbox" name="popup_enabled" value="1" <?php echo !empty($settings['featured_event_popup']['enabled']) ? 'checked' : ''; ?>>
            <strong>Aktifkan Pop-up Otomatis saat Pengunjung Membuka Beranda</strong>
          </label>
          <div class="adm-form-row" style="margin-top: 10px;">
            <div>
              <label for="popup_event_id" class="adm-label">Event yang Ditampilkan</label>
              <select id="popup_event_id" name="popup_event_id" class="adm-select">
                <?php foreach (get_events() as $ev): ?>
                  <option value="<?php echo htmlspecialchars($ev['id']); ?>" <?php echo (($settings['featured_event_popup']['event_id'] ?? '') === $ev['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ev['title']); ?> (<?php echo format_date_id($ev['date'], false); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="popup_delay" class="adm-label">Jeda Waktu Kemunculan (Detik)</label>
              <input type="number" id="popup_delay" name="popup_delay" value="<?php echo (int)($settings['featured_event_popup']['show_delay_seconds'] ?? 2); ?>" min="0" max="15" class="adm-input">
            </div>
          </div>
        </div>

        <button type="submit" class="adm-btn adm-btn-primary">
          Simpan Pengaturan Identitas &rarr;
        </button>
      </form>
    </div>
  <?php endif; ?>

  <!-- ========================================== -->
  <!-- TAB: SLIDESHOW HERO BERANDA                -->
  <!-- ========================================== -->
  <?php if ($currentTab === 'slideshow'): ?>
    <div class="adm-card">
      <div class="adm-card-header">
        <div>
          <h2 class="adm-card-title">
            <span>Slideshow Background Hero Beranda</span>
          </h2>
          <div class="adm-card-subtitle">Atur foto latar belakang dinamis yang tampil di bagian atas beranda Omah Belumbungan</div>
        </div>
      </div>

      <form action="settings.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tab" value="slideshow">

        <!-- Interval Slide Duration -->
        <div class="adm-form-group" style="max-width: 320px; margin-bottom: 24px;">
          <label for="hero_slideshow_interval" class="adm-label">Durasi Pergantian Slide (Detik) *</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="number" id="hero_slideshow_interval" name="hero_slideshow_interval" 
                   value="<?php echo (int)($settings['hero_slideshow_interval'] ?? 5); ?>" 
                   min="2" max="30" class="adm-input" style="width: 120px;">
            <span style="font-size: 0.88rem; color: var(--adm-text-subtle);">detik per slide</span>
          </div>
          <span style="font-size: 0.76rem; color: var(--adm-text-subtle); display: block; margin-top: 4px;">
            Standar: 5 detik. Transisi crossfade halus antar gambar secara otomatis.
          </span>
        </div>

        <!-- Daftar Slide Aktif -->
        <div class="adm-form-group">
          <label class="adm-label">Daftar Foto Background Aktif (Urutan Tampil)</label>
          <div id="slideshowList" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
            <?php 
            $currentSlides = $settings['hero_slideshow'] ?? ['assets/images/herobg.jpg'];
            foreach ($currentSlides as $sIdx => $slidePath): 
              $displayPath = (strpos($slidePath, 'http') === 0 || strpos($slidePath, '/') === 0) ? $slidePath : '../' . $slidePath;
            ?>
              <div class="slide-item-row" style="display: flex; align-items: center; gap: 14px; background: var(--adm-surface-alt); padding: 10px 14px; border-radius: var(--adm-radius-sm); border: 1px solid var(--adm-border);">
                <div style="width: 68px; height: 46px; border-radius: 4px; overflow: hidden; background: #000; flex-shrink: 0;">
                  <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Slide" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <input type="text" name="slides[]" value="<?php echo htmlspecialchars($slidePath); ?>" class="adm-input" style="flex: 1; font-family: monospace; font-size: 0.85rem;" readonly>
                <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm btn-remove-slide" onclick="this.closest('.slide-item-row').remove();" title="Hapus Slide">
                  &times; Hapus
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Unggah Foto Baru -->
        <div style="background: var(--adm-surface-alt); padding: 20px; border-radius: var(--adm-radius-md); border: 1px dashed var(--adm-border); margin: 24px 0;">
          <h3 style="font-size: 0.95rem; font-family: var(--adm-font-serif); color: var(--adm-forest); margin-bottom: 6px;">
            + Unggah Foto Baru untuk Slideshow
          </h3>
          <p style="font-size: 0.82rem; color: var(--adm-text-subtle); margin-bottom: 12px;">
            Pilih file foto landscape beresolusi tinggi (format JPG, PNG, atau WEBP). Gambar otomatis disimpan ke folder <code>uploads/hero/</code>.
          </p>
          <input type="file" name="new_slide_file" accept="image/jpeg,image/png,image/webp" class="adm-input" style="padding: 8px;">
        </div>

        <!-- Tambah Cepat dari Koleksi Foto Kawasan -->
        <div style="margin: 24px 0;">
          <label class="adm-label">Atau Tambahkan Cepat dari Koleksi Foto Kawasan:</label>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; max-height: 230px; overflow-y: auto; padding: 10px; background: var(--adm-surface-alt); border-radius: var(--adm-radius-sm); border: 1px solid var(--adm-border);">
            <?php
            $samplePhotos = [
              'assets/images/herobg.jpg' => 'Gerbang & Pintu Angkul',
              'assets/images/hero-compound.jpg' => 'Pekarangan & Natah',
              'assets/images/photo-01.jpg' => 'Bale & Ukiran Kayu',
              'assets/images/photo-02.jpg' => 'Bata Merah & Aling-aling',
              'assets/images/photo-03.jpg' => 'Natah & Pohon Kamboja',
              'assets/images/photo-04.jpg' => 'Bale Meten Tradisional',
              'assets/images/photo-05.jpg' => 'Bale Dangin Upacara',
              'assets/images/photo-06.jpg' => 'Bale Dauh Ruang Diskusi',
              'assets/images/photo-07.jpg' => 'Paon Dapur Tradisional',
              'assets/images/photo-08.jpg' => 'Jineng Lumbung Padi',
              'assets/images/photo-09.jpg' => 'Pemerajan Tempat Doa',
              'assets/images/photo-10.jpg' => 'Detail Struktur Saka'
            ];
            foreach ($samplePhotos as $pPath => $pTitle):
            ?>
              <div style="display: flex; flex-direction: column; gap: 4px; border: 1px solid var(--adm-border); border-radius: 4px; overflow: hidden; background: #fff; padding: 4px;">
                <img src="../<?php echo $pPath; ?>" alt="<?php echo $pTitle; ?>" style="width: 100%; height: 60px; object-fit: cover; border-radius: 2px;">
                <span style="font-size: 0.72rem; color: var(--adm-text-subtle); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo $pTitle; ?></span>
                <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" style="font-size: 0.72rem; padding: 2px 6px;" onclick="addSlideFromLibrary('<?php echo $pPath; ?>', '../<?php echo $pPath; ?>');">
                  + Tambah
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="adm-btn adm-btn-primary">
          Simpan Slideshow Hero &rarr;
        </button>
      </form>
    </div>

    <script>
      function addSlideFromLibrary(path, displayUrl) {
        const existing = Array.from(document.querySelectorAll('#slideshowList input[name="slides[]"]')).map(i => i.value);
        if (existing.includes(path)) {
          alert('Foto ini sudah ada di dalam daftar slideshow aktif.');
          return;
        }
        const row = document.createElement('div');
        row.className = 'slide-item-row';
        row.style.cssText = 'display: flex; align-items: center; gap: 14px; background: var(--adm-surface-alt); padding: 10px 14px; border-radius: var(--adm-radius-sm); border: 1px solid var(--adm-border);';
        row.innerHTML = `
          <div style="width: 68px; height: 46px; border-radius: 4px; overflow: hidden; background: #000; flex-shrink: 0;">
            <img src="${displayUrl}" alt="Slide" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <input type="text" name="slides[]" value="${path}" class="adm-input" style="flex: 1; font-family: monospace; font-size: 0.85rem;" readonly>
          <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm btn-remove-slide" onclick="this.closest('.slide-item-row').remove();" title="Hapus Slide">
            &times; Hapus
          </button>
        `;
        document.getElementById('slideshowList').appendChild(row);
      }
    </script>
  <?php endif; ?>

  <!-- ========================================== -->
  <!-- TAB 2: KONTAK & LOKASI                     -->
  <!-- ========================================== -->
  <?php if ($currentTab === 'contact'): ?>
    <div class="adm-card">
      <div class="adm-card-header">
        <div>
          <h2 class="adm-card-title">
            <span>Kontak &amp; Titik Lokasi Kawasan</span>
          </h2>
          <div class="adm-card-subtitle">Nomor komunikasi resmi, WhatsApp pendaftaran, jam kunjungan, dan navigasi peta</div>
        </div>
      </div>

      <form action="settings.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tab" value="contact">

        <div class="adm-form-row">
          <div class="adm-form-group">
            <label for="whatsapp" class="adm-label">Nomor WhatsApp Resmi (Format: 628xxx) *</label>
            <input type="text" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($settings['whatsapp'] ?? ''); ?>" required placeholder="6281234567890" class="adm-input">
            <span style="font-size: 0.74rem; color: var(--adm-text-subtle);">Digunakan untuk tautan pendaftaran otomatis dan konfirmasi reservasi.</span>
          </div>
          <div class="adm-form-group">
            <label for="phone" class="adm-label">Nomor Telepon Kantor / Fixed-line</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? $settings['whatsapp'] ?? ''); ?>" placeholder="+62 812 3456 7890" class="adm-input">
          </div>
        </div>

        <div class="adm-form-row">
          <div class="adm-form-group">
            <label for="email" class="adm-label">Alamat Email Korespondensi *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" required placeholder="kontak@omahbelumbungan.com" class="adm-input">
          </div>
          <div class="adm-form-group">
            <label for="opening_hours" class="adm-label">Jam Operasional / Kunjungan *</label>
            <input type="text" id="opening_hours" name="opening_hours" value="<?php echo htmlspecialchars($settings['opening_hours'] ?? '08:30 - 18:00 WITA'); ?>" required class="adm-input">
          </div>
        </div>

        <div class="adm-form-group">
          <label for="address" class="adm-label">Alamat Lengkap Kawasan *</label>
          <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>" required placeholder="Contoh: Jl. Raya Sibang Kaja, Abiansemal, Badung, Bali" class="adm-input">
        </div>

        <div class="adm-form-group">
          <label for="maps_url" class="adm-label">Tautan Google Maps / Koordinat Titik Lokasi</label>
          <input type="text" id="maps_url" name="maps_url" value="<?php echo htmlspecialchars($settings['maps_url'] ?? ''); ?>" placeholder="https://maps.google.com/?q=..." class="adm-input">
          <span style="font-size: 0.74rem; color: var(--adm-text-subtle);">Pengunjung dapat membuka tautan ini langsung dari footer atau halaman kontak.</span>
        </div>

        <button type="submit" class="adm-btn adm-btn-primary">
          Simpan Kontak &amp; Lokasi &rarr;
        </button>
      </form>
    </div>
  <?php endif; ?>

  <!-- ========================================== -->
  <!-- TAB 3: KAPASITAS & LIBUR ADAT              -->
  <!-- ========================================== -->
  <?php if ($currentTab === 'capacity'): ?>
    <div class="adm-card">
      <div class="adm-card-header">
        <div>
          <h2 class="adm-card-title">
            <span>Batas Kuota Harian &amp; Libur Adat</span>
          </h2>
          <div class="adm-card-subtitle">Pengendalian daya tampung spasial dan proteksi hari suci / upacara adat</div>
        </div>
      </div>

      <form action="settings.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tab" value="capacity">

        <h3 style="font-family: var(--adm-font-serif); font-size: 1.05rem; color: var(--adm-forest); margin-bottom: 14px;">
          Daya Tampung Maksimal per Jenis Kunjungan (Orang / Sesi)
        </h3>

        <div class="adm-form-row">
          <div class="adm-form-group">
            <label for="capacity_school" class="adm-label">Study Tour / Kunjungan Sekolah (Orang)</label>
            <input type="number" id="capacity_school" name="capacity_school" value="<?php echo (int)($settings['capacity']['school_study'] ?? 80); ?>" required class="adm-input">
          </div>
          <div class="adm-form-group">
            <label for="capacity_seminar" class="adm-label">Seminar / Workshop Arsitektur (Orang)</label>
            <input type="number" id="capacity_seminar" name="capacity_seminar" value="<?php echo (int)($settings['capacity']['seminar_workshop'] ?? 50); ?>" required class="adm-input">
          </div>
        </div>

        <div class="adm-form-row">
          <div class="adm-form-group">
            <label for="capacity_gathering" class="adm-label">Gathering / Komunitas Budaya (Orang)</label>
            <input type="number" id="capacity_gathering" name="capacity_gathering" value="<?php echo (int)($settings['capacity']['gathering'] ?? 70); ?>" required class="adm-input">
          </div>
          <div class="adm-form-group">
            <label for="capacity_prewedding" class="adm-label">Sesi Foto / Prewedding (Slot Tim per Hari)</label>
            <input type="number" id="capacity_prewedding" name="capacity_prewedding" value="<?php echo (int)($settings['capacity']['prewedding_slots'] ?? 2); ?>" required class="adm-input">
          </div>
        </div>

        <div class="adm-form-group" style="margin-top: 20px;">
          <label for="blackout_dates" class="adm-label">
            Daftar Tanggal Libur Adat / Blackout Dates (1 baris per tanggal, format YYYY-MM-DD)
          </label>
          <textarea id="blackout_dates" name="blackout_dates" rows="7" class="adm-textarea" style="font-family: monospace; font-size: 0.95rem;"><?php 
            echo htmlspecialchars(implode("\n", $settings['blackout_dates'] ?? [])); 
          ?></textarea>
          <span style="font-size: 0.74rem; color: var(--adm-text-subtle); display: block; margin-top: 6px;">
            Pada tanggal-tanggal di atas, sistem kalender publik otomatis memblokir pemesanan kunjungan baru demi menjaga kesucian hari adat.
          </span>
        </div>

        <!-- Ringkasan Tanggal Terblokir -->
        <?php if (!empty($settings['blackout_dates'])): ?>
          <div style="background: var(--adm-surface-alt); padding: 14px 18px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border); margin-bottom: 20px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--adm-forest); margin-bottom: 6px;">
              Tanggal Terdaftar Libur Adat (<?php echo count($settings['blackout_dates']); ?> Hari):
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
              <?php foreach ($settings['blackout_dates'] as $bd): ?>
                <span class="adm-badge adm-badge-rejected" style="font-family: monospace;">
                  ⛔ <?php echo htmlspecialchars($bd); ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <button type="submit" class="adm-btn adm-btn-primary">
          Simpan Aturan Kapasitas &amp; Libur Adat &rarr;
        </button>
      </form>
    </div>
  <?php endif; ?>

  <!-- ========================================== -->
  <!-- TAB 4: KEAMANAN & KATA SANDI               -->
  <!-- ========================================== -->
  <?php if ($currentTab === 'security'): ?>
    <div class="adm-card">
      <div class="adm-card-header">
        <div>
          <h2 class="adm-card-title">
            <span>Keamanan Akun &amp; Kata Sandi Administrator</span>
          </h2>
          <div class="adm-card-subtitle">Perbarui kredensial autentikasi sistem dan pantau status proteksi CMS Omah Belumbungan</div>
        </div>
      </div>

      <div style="background: var(--adm-surface-alt); padding: 16px 20px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border); margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px;">
        <div style="color: var(--adm-forest); margin-top: 2px;">
          <?php echo ob_icon('shield-check', ['size' => 24, 'raw' => true]); ?>
        </div>
        <div>
          <div style="font-weight: 600; color: var(--adm-forest); font-size: 0.92rem; margin-bottom: 4px;">Proteksi Autentikasi Aktif</div>
          <div style="font-size: 0.82rem; color: var(--adm-text-subtle); line-height: 1.5;">
            Sistem dilengkapi dengan pembatasan percobaan login (Brute-Force Rate Limiting), verifikasi token CSRF pada seluruh operasi administratif, proteksi eksekusi skrip direktori uploads, serta enkripsi sandi menggunakan algoritma Blowfish BCRYPT.
          </div>
        </div>
      </div>

      <form action="settings.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tab" value="security">

        <div class="adm-form-group">
          <label for="old_password" class="adm-label">Kata Sandi Saat Ini *</label>
          <input type="password" id="old_password" name="old_password" required autocomplete="current-password" class="adm-input" placeholder="Masukkan kata sandi saat ini">
        </div>

        <div class="adm-form-row">
          <div class="adm-form-group">
            <label for="new_password" class="adm-label">Kata Sandi Baru * (Minimal 8 Karakter)</label>
            <input type="password" id="new_password" name="new_password" minlength="8" required autocomplete="new-password" class="adm-input" placeholder="Minimal 8 karakter unik">
          </div>
          <div class="adm-form-group">
            <label for="confirm_password" class="adm-label">Konfirmasi Kata Sandi Baru *</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required autocomplete="new-password" class="adm-input" placeholder="Ulangi kata sandi baru">
          </div>
        </div>

        <button type="submit" class="adm-btn adm-btn-primary">
          Perbarui Kata Sandi Administrator &rarr;
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
