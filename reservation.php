<?php
/**
 * OMAH BELUMBUNGAN - Formulir Pengajuan Reservasi Kunjungan
 * Mendukung: Kunjungan Edukasi Sekolah, Sesi Foto Prewedding, Seminar & Sarasehan
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Reservasi Jadwal Kunjungan';
$settings = get_settings();

$submittedBooking = null;
$preselectedType = $_GET['type'] ?? 'school_study';
$preselectedDate = sanitize($_GET['date'] ?? '');
$preselectedSession = sanitize($_GET['session'] ?? 'morning');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $institution = sanitize($_POST['institution'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $bookingType = sanitize($_POST['booking_type'] ?? 'school_study');
    $date = sanitize($_POST['date'] ?? '');
    $session = sanitize($_POST['session'] ?? 'morning');
    $participants = (int)($_POST['participants_count'] ?? 1);
    $notes = sanitize($_POST['notes'] ?? '');

    $typeLabels = [
        'school_study' => 'Kunjungan Edukasi Sekolah',
        'prewedding' => 'Sesi Foto Prewedding & Komersial',
        'seminar' => 'Seminar / Ruang Diskusi Budaya',
        'community' => 'Pertemuan Komunitas & Seni'
    ];

    $sessionLabels = [
        'morning' => 'Sesi Pagi (08:30 - 12:30 WITA)',
        'afternoon' => 'Sesi Sore (13:30 - 17:30 WITA)',
        'full_day' => 'Sehari Penuh (09:00 - 17:00 WITA)'
    ];

    $errors = [];

    if (empty($name)) $errors[] = 'Nama pemohon wajib diisi.';
    if (empty($phone)) $errors[] = 'Nomor WhatsApp wajib diisi untuk konfirmasi.';
    if (empty($date)) $errors[] = 'Pilih tanggal rencana kunjungan.';
    if (is_blackout_date($date)) {
        $errors[] = 'Tanggal yang dipilih bertepatan dengan Hari Raya / Upacara Adat di Sibang Kaja.';
    }

    if (empty($errors)) {
        $newBooking = [
            'id' => generate_booking_id(),
            'name' => $name,
            'institution' => $institution,
            'email' => $email,
            'phone' => $phone,
            'booking_type' => $bookingType,
            'type_label' => $typeLabels[$bookingType] ?? $bookingType,
            'date' => $date,
            'session' => $session,
            'session_label' => $sessionLabels[$session] ?? $session,
            'participants_count' => $participants,
            'notes' => $notes,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $bookings = get_bookings();
        $bookings[] = $newBooking;
        save_json_data('bookings.json', $bookings);

        $submittedBooking = $newBooking;
        set_flash('success', 'Pengajuan reservasi berhasil dikirim! Silakan konfirmasikan melalui WhatsApp untuk percepatan verifikasi jadwal.');
    } else {
        set_flash('error', implode('<br>', $errors));
    }
}

$flash = get_flash();
include __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 50px;">
  <div class="container">
    <div style="max-width: 800px; margin-bottom: 40px;">
      <span class="section-tag">Jadwal & Reservasi</span>
      <h1 class="section-title">Formulir Pengajuan Kunjungan</h1>
      <p class="section-desc">
        Untuk menjaga ketenangan, ketertiban ruang, dan kekhidmatan pekarangan adat, setiap kunjungan rombongan 
        sekolah, pemotretan, dan kegiatan komunitas di Omah Belumbungan wajib diajukan terlebih dahulu.
      </p>
    </div>

    <?php if ($flash): ?>
      <div style="padding: 16px 20px; border-radius: var(--radius-sm); margin-bottom: 30px; font-weight: 500; <?php echo ($flash['type'] === 'success') ? 'background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9;' : 'background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2;'; ?>">
        <?php echo $flash['text']; ?>
      </div>
    <?php endif; ?>

    <?php if ($submittedBooking): 
      $waConfirmMsg = "Om Swastyastu Pengelola Omah Belumbungan. Saya telah mengirim formulir reservasi via website:\n"
                    . "• Kode Booking: " . $submittedBooking['id'] . "\n"
                    . "• Nama: " . $submittedBooking['name'] . "\n"
                    . "• Instansi: " . ($submittedBooking['institution'] ?: '-') . "\n"
                    . "• Agenda: " . $submittedBooking['type_label'] . "\n"
                    . "• Tanggal: " . format_date_id($submittedBooking['date']) . "\n"
                    . "• Sesi: " . $submittedBooking['session_label'] . "\n"
                    . "• Jumlah: " . $submittedBooking['participants_count'] . " orang\n"
                    . "Mohon konfirmasi ketersediaan tempatnya. Suksma.";
      $waUrl = make_whatsapp_url($settings['whatsapp'] ?? '6281234567890', $waConfirmMsg);
    ?>
      <div style="background: var(--paras-card); border: 2px solid var(--leaf-green); border-radius: var(--radius-lg); padding: 36px; margin-bottom: 50px; text-align: center;">
        <div style="width: 60px; height: 60px; background: var(--leaf-light); color: var(--leaf-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
          <?php echo ob_icon('check', ['size' => 32, 'color' => 'forest', 'raw' => true, 'stroke_width' => 2.5]); ?>
        </div>
        <h2 style="font-size: 1.8rem; margin-bottom: 10px;">Pengajuan Anda Telah Diterima!</h2>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 20px;">
          Nomor Referensi Anda adalah: <strong style="font-size: 1.2rem; color: var(--terracotta);"><?php echo htmlspecialchars($submittedBooking['id']); ?></strong>.<br>
          Status saat ini: <span class="badge badge-pending">Menunggu Verifikasi Pengelola</span>
        </p>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
          <a href="<?php echo htmlspecialchars($waUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="background: #25D366; font-size: 1rem; padding: 12px 28px; display: inline-flex; align-items: center; gap: 8px;">
            <?php echo ob_icon('whatsapp', ['size' => 18, 'raw' => true]); ?>
            <span>Lanjutkan Konfirmasi ke WhatsApp Pengelola</span>
          </a>
          <a href="reservation.php" class="btn btn-secondary">Buat Pengajuan Baru</a>
        </div>
      </div>
    <?php endif; ?>

    <div class="reservation-layout">
      <!-- Formulir -->
      <div class="form-card">
        <form action="reservation.php" method="POST">
          <div class="form-group">
            <label for="booking_type">Jenis Kegiatan / Rombongan *</label>
            <select name="booking_type" id="booking_type" required>
              <option value="school_study" <?php echo ($preselectedType === 'school_study') ? 'selected' : ''; ?>>Kunjungan Edukasi Sekolah / Perguruan Tinggi</option>
              <option value="prewedding" <?php echo ($preselectedType === 'prewedding') ? 'selected' : ''; ?>>Sesi Foto Prewedding & Dokumentasi Adat</option>
              <option value="seminar" <?php echo ($preselectedType === 'seminar') ? 'selected' : ''; ?>>Seminar, Sarasehan & Lokakarya Kebudayaan</option>
              <option value="community" <?php echo ($preselectedType === 'community') ? 'selected' : ''; ?>>Pertemuan Komunitas & Diskusi Terbuka</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="name">Nama Lengkap Pemohon / Penanggung Jawab *</label>
              <input type="text" id="name" name="name" placeholder="Contoh: I Made Sujana" required>
            </div>
            <div class="form-group">
              <label for="institution">Asal Sekolah / Lembaga / Usaha</label>
              <input type="text" id="institution" name="institution" placeholder="Contoh: SMA Negeri 1 Abiansemal">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="phone">Nomor WhatsApp Aktif *</label>
              <input type="tel" id="phone" name="phone" placeholder="Contoh: 081234567890" required>
            </div>
            <div class="form-group">
              <label for="email">Alamat Email</label>
              <input type="email" id="email" name="email" placeholder="Contoh: nama@domain.com">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="bookingDate">Rencana Tanggal Kunjungan *</label>
              <input type="date" id="bookingDate" name="date" value="<?php echo htmlspecialchars($preselectedDate); ?>" required>
              <div id="dateFeedback" style="font-size: 0.85rem; margin-top: 6px;"></div>
            </div>
            <div class="form-group">
              <label for="session">Pilihan Sesi Waktu *</label>
              <select name="session" id="session" required>
                <option value="morning" <?php echo $preselectedSession === 'morning' ? 'selected' : ''; ?>>Sesi Pagi (08:30 - 12:30 WITA)</option>
                <option value="afternoon" <?php echo $preselectedSession === 'afternoon' ? 'selected' : ''; ?>>Sesi Sore (14:30 - 17:30 WITA)</option>
                <option value="full_day" <?php echo $preselectedSession === 'full_day' ? 'selected' : ''; ?>>Sehari Penuh (09:00 - 17:00 WITA)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="participants_count">Estimasi Jumlah Peserta / Rombongan (Orang) *</label>
            <input type="number" id="participants_count" name="participants_count" min="1" max="100" value="20" required>
            <small style="color: var(--text-muted); display: block; margin-top: 4px;">
              Maksimal rombongan sekolah 80 siswa/sesi; seminar 50 orang; prewedding maksimal 10 kru.
            </small>
          </div>

          <div class="form-group">
            <label for="notes">Catatan Khusus / Topik Pelajaran yang Ingin Dipelajari</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Sebutkan jika membutuhkan pendamping undagi untuk bedah materi arsitektur, kebutuhan tata suara ringan, atau konsumsi tradisional."></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%;">
            Kirimkan Pengajuan Reservasi &rarr;
          </button>
        </form>
      </div>

      <!-- Panduan & Tata Tertib Kawasan Adat -->
      <div class="guidelines-box">
        <h3>Tata Tertib & Etika Berkunjung</h3>
        <ul class="guidelines-list">
          <li><strong>Pakaian Beretika:</strong> Pengunjung wajib mengenakan pakaian sopan dan tertib. Saat memasuki area mendekati Pemerajan/Sanggah, diwajibkan menggunakan selendang (senteng).</li>
          <li><strong>Konfirmasi H-3:</strong> Reservasi disarankan diajukan paling lambat 3 hari sebelum rencana kunjungan untuk penyiapan ruang dan pemandu.</li>
          <li><strong>Menjaga Keaslian Fisik:</strong> Dilarang memanjat ornamen batu padas, melompat dari balai, atau merusak tanaman upakara di natah.</li>
          <li><strong>Kebersihan Kawasan:</strong> Omah Belumbungan adalah kawasan bebas sampah plastik sekali pakai. Sediakan tempat minum isi ulang (*tumbler*).</li>
        </ul>

        <div style="background: var(--paras-bg); border-radius: var(--radius-sm); padding: 18px; border-left: 3px solid var(--gold-accent);">
          <strong style="color: var(--wood-dark); display: block; margin-bottom: 6px;">Butuh Respons Cepat?</strong>
          <span style="font-size: 0.88rem; color: var(--text-muted);">
            Jika kunjungan Anda mendesak dalam kurun 1-2 hari ke depan, silakan hubungi pengelola langsung via WhatsApp di <strong><?php echo htmlspecialchars($settings['whatsapp'] ?? '081234567890'); ?></strong>.
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
