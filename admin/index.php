<?php
/**
 * OMAH BELUMBUNGAN - Dasbor Ringkasan Admin CMS
 * Operational KPIs & Quick Management Actions
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$adminTitle = 'Dashboard Operasional';
require_once __DIR__ . '/header.php';

$allBookings = get_bookings();
$pendingBookings = array_values(array_filter($allBookings, function($b) { 
    return ($b['status'] ?? '') === 'pending'; 
}));

$today = date('Y-m-d');
$confirmedUpcomingVisits = array_values(array_filter($allBookings, function($b) use ($today) { 
    return ($b['status'] ?? '') === 'confirmed' && ($b['date'] ?? '') >= $today; 
}));

$upcomingEvents = get_events('upcoming');

// Konten yang telah dipublikasikan
$publishedPortfolio = count(array_filter(get_portfolio(), function($p) {
    return ($p['status'] ?? '') === 'published';
}));
$publishedGallery = count(array_filter(get_gallery_events(), function($g) {
    return ($g['status'] ?? '') === 'published';
}));
$totalAnatomy = count(get_anatomy());
$totalPublishedContent = $publishedPortfolio + $publishedGallery + $totalAnatomy;

// Tangani aksi cepat dari dasbor (Konfirmasi / Tolak)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['booking_id'])) {
    require_csrf_token();
    $action = $_POST['action'];
    $bookingId = $_POST['booking_id'];
    
    foreach ($allBookings as &$b) {
        if ($b['id'] === $bookingId) {
            if ($action === 'confirm') {
                $b['status'] = 'confirmed';
                set_flash('success', "Reservasi <strong>{$bookingId}</strong> atas nama {$b['name']} berhasil <strong>disetujui</strong>.");
            } elseif ($action === 'reject') {
                $b['status'] = 'rejected';
                set_flash('error', "Reservasi <strong>{$bookingId}</strong> atas nama {$b['name']} telah <strong>ditolak</strong>.");
            }
            break;
        }
    }
    save_json_data('bookings.json', $allBookings);
    header('Location: index.php');
    exit;
}
?>

<!-- Operational Alerts -->
<?php if (!empty($pendingBookings)): ?>
  <div class="adm-alert adm-alert-warning" role="alert">
    <span class="adm-alert-icon"><?php echo ob_icon('alert-triangle', ['size' => 20, 'raw' => true]); ?></span>
    <div style="flex-grow: 1;">
      <strong>Tindakan Diperlukan:</strong> Terdapat <strong><?php echo count($pendingBookings); ?> pengajuan reservasi baru</strong> yang menunggu konfirmasi pengelola.
    </div>
    <a href="bookings.php?status=pending" class="adm-btn adm-btn-primary adm-btn-sm">
      Tinjau Sekarang &rarr;
    </a>
  </div>
<?php endif; ?>

<!-- 1. OPERATIONAL KPI CARDS -->
<div class="adm-stats-grid">
  <!-- KPI 1: Reservasi Menunggu Tinjauan -->
  <div class="adm-stat-card stat-amber">
    <div class="adm-stat-header">
      <span class="adm-stat-label">Menunggu Tinjauan</span>
      <span class="adm-stat-icon"><?php echo ob_icon('clock', ['size' => 16, 'raw' => true]); ?></span>
    </div>
    <div class="adm-stat-val" style="color: var(--adm-amber);"><?php echo count($pendingBookings); ?></div>
    <div class="adm-stat-desc">Pengajuan reservasi baru</div>
  </div>

  <!-- KPI 2: Kunjungan Mendatang -->
  <div class="adm-stat-card stat-green">
    <div class="adm-stat-header">
      <span class="adm-stat-label">Kunjungan Mendatang</span>
      <span class="adm-stat-icon"><?php echo ob_icon('calendar', ['size' => 16, 'raw' => true]); ?></span>
    </div>
    <div class="adm-stat-val" style="color: var(--adm-green);"><?php echo count($confirmedUpcomingVisits); ?></div>
    <div class="adm-stat-desc">Jadwal kunjungan terkonfirmasi</div>
  </div>

  <!-- KPI 3: Agenda Aktif -->
  <div class="adm-stat-card stat-terracotta">
    <div class="adm-stat-header">
      <span class="adm-stat-label">Agenda Aktif</span>
      <span class="adm-stat-icon"><?php echo ob_icon('ticket', ['size' => 16, 'raw' => true]); ?></span>
    </div>
    <div class="adm-stat-val" style="color: var(--adm-primary);"><?php echo count($upcomingEvents); ?></div>
    <div class="adm-stat-desc">Lokakarya &amp; kelas budaya</div>
  </div>

  <!-- KPI 4: Konten Terpublikasi -->
  <div class="adm-stat-card stat-forest">
    <div class="adm-stat-header">
      <span class="adm-stat-label">Konten Publik</span>
      <span class="adm-stat-icon"><?php echo ob_icon('sparkles', ['size' => 16, 'raw' => true]); ?></span>
    </div>
    <div class="adm-stat-val" style="color: var(--adm-forest);"><?php echo $totalPublishedContent; ?></div>
    <div class="adm-stat-desc"><?php echo $totalAnatomy; ?> anatomi, <?php echo $publishedPortfolio; ?> karya, <?php echo $publishedGallery; ?> galeri</div>
  </div>
</div>

<!-- 2. QUICK ACTIONS ROW -->
<div class="adm-card" style="padding: 18px 24px;">
  <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="font-weight: 700; color: var(--adm-forest); font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
      <span>⚡ Aksi Cepat Pengelola:</span>
    </div>
    <div class="adm-quick-actions" style="margin-bottom: 0;">
      <a href="bookings.php" class="adm-btn adm-btn-secondary adm-btn-sm">
        <?php echo ob_icon('calendar', ['size' => 14, 'raw' => true]); ?> Kelola Reservasi
      </a>
      <a href="events.php?action=new" class="adm-btn adm-btn-secondary adm-btn-sm">
        <?php echo ob_icon('ticket', ['size' => 14, 'raw' => true]); ?> + Tambah Agenda
      </a>
      <a href="gallery.php" class="adm-btn adm-btn-secondary adm-btn-sm">
        <?php echo ob_icon('camera', ['size' => 14, 'raw' => true]); ?> + Upload Galeri
      </a>
      <a href="media.php" class="adm-btn adm-btn-secondary adm-btn-sm">
        <?php echo ob_icon('camera', ['size' => 14, 'raw' => true]); ?> Pustaka Media
      </a>
      <a href="settings.php" class="adm-btn adm-btn-secondary adm-btn-sm">
        <?php echo ob_icon('settings', ['size' => 14, 'raw' => true]); ?> Pengaturan Kawasan
      </a>
    </div>
  </div>
</div>

<!-- 3. RECENT RESERVATIONS TABLE -->
<div class="adm-card">
  <div class="adm-card-header">
    <div>
      <h2 class="adm-card-title">
        <span><?php echo ob_icon('calendar', ['size' => 18, 'raw' => true]); ?> Pengajuan Kunjungan Menunggu Konfirmasi</span>
      </h2>
      <div class="adm-card-subtitle">Pengajuan dari publik yang memerlukan tinjauan administratif</div>
    </div>
    <a href="bookings.php" class="adm-btn adm-btn-secondary adm-btn-sm">Lihat Seluruh Reservasi &rarr;</a>
  </div>

  <?php if (empty($pendingBookings)): ?>
    <div style="padding: 36px 20px; text-align: center; color: var(--adm-text-muted); background: var(--adm-surface-alt); border-radius: var(--adm-radius-md);">
      <p style="font-size: 0.95rem; margin-bottom: 6px;">
        ✓ <strong>Seluruh pengajuan reservasi telah ditinjau.</strong>
      </p>
      <span style="font-size: 0.82rem; color: var(--adm-text-subtle);">Tidak ada pengajuan tertunda saat ini.</span>
    </div>
  <?php else: ?>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Pemohon &amp; Instansi</th>
            <th>Tipe Agenda</th>
            <th>Jadwal Kunjungan</th>
            <th>Peserta</th>
            <th>Kontak WhatsApp</th>
            <th>Tindakan Cepat</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingBookings as $b): 
            $confirmText = "Om Swastyastu Bapak/Ibu " . $b['name'] . ", salam hangat dari Pengelola Omah Belumbungan Sibang Kaja. "
                         . "Kami mengonfirmasi bahwa jadwal kunjungan Anda (" . ($b['type_label'] ?? 'Kunjungan Edukasi') . ") "
                         . "pada tanggal " . format_date_id($b['date']) . " (" . ($b['session_label'] ?? 'Sesi Kunjungan') . ") "
                         . "telah DISETUJUI dan dijadwalkan. Suksma.";
            $waUrl = make_whatsapp_url($b['phone'] ?? '', $confirmText);
          ?>
          <tr>
            <td>
              <strong style="color: var(--adm-forest); font-family: monospace; font-size: 0.9rem;">
                <?php echo htmlspecialchars($b['id']); ?>
              </strong>
            </td>
            <td>
              <strong style="color: var(--adm-text);"><?php echo htmlspecialchars($b['name']); ?></strong><br>
              <span style="font-size: 0.78rem; color: var(--adm-text-subtle);"><?php echo htmlspecialchars($b['institution'] ?: 'Pribadi / Independen'); ?></span>
            </td>
            <td>
              <span class="adm-badge adm-badge-category"><?php echo htmlspecialchars($b['type_label'] ?? 'Edukasi'); ?></span>
            </td>
            <td>
              <strong><?php echo format_date_id($b['date'], false); ?></strong><br>
              <span style="font-size: 0.78rem; color: var(--adm-text-subtle);"><?php echo htmlspecialchars($b['session_label'] ?? '-'); ?></span>
            </td>
            <td>
              <strong><?php echo (int)($b['participants_count'] ?? 1); ?></strong> orang
            </td>
            <td>
              <a href="<?php echo htmlspecialchars($waUrl); ?>" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn-whatsapp adm-btn-sm" title="Chat WhatsApp Pemohon">
                <?php echo ob_icon('whatsapp', ['size' => 14, 'raw' => true]); ?> Chat WA
              </a>
            </td>
            <td>
              <div style="display: flex; gap: 6px;">
                <form method="POST" style="display:inline;">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                  <input type="hidden" name="action" value="confirm">
                  <button type="submit" class="adm-btn adm-btn-success adm-btn-sm" onclick="return confirm('Setujui reservasi <?php echo htmlspecialchars($b['id']); ?>?');">
                    Setujui
                  </button>
                </form>
                <form method="POST" style="display:inline;">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                  <input type="hidden" name="action" value="reject">
                  <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Tolak permohonan <?php echo htmlspecialchars($b['id']); ?>?');">
                    Tolak
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- 4. UPCOMING EVENTS OVERVIEW -->
<div class="adm-card">
  <div class="adm-card-header">
    <div>
      <h2 class="adm-card-title">
        <span><?php echo ob_icon('ticket', ['size' => 18, 'raw' => true]); ?> Agenda Workshop &amp; Edukasi Terdekat</span>
      </h2>
      <div class="adm-card-subtitle">Kegiatan budaya aktif yang sedang dibuka pendaftarannya</div>
    </div>
    <a href="events.php" class="adm-btn adm-btn-primary adm-btn-sm">+ Kelola Seluruh Agenda</a>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Nama Agenda</th>
          <th>Kategori</th>
          <th>Tanggal &amp; Waktu</th>
          <th>Narasumber</th>
          <th>Pendaftar / Kuota</th>
          <th>Status Publikasi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($upcomingEvents as $evt): 
          $quota = (int)($evt['quota'] ?? 0);
          $registered = (int)($evt['registered'] ?? 0);
          $rem = max(0, $quota - $registered);
        ?>
        <tr>
          <td>
            <strong style="color: var(--adm-forest);"><?php echo htmlspecialchars($evt['title']); ?></strong>
          </td>
          <td>
            <span class="adm-badge adm-badge-category"><?php echo htmlspecialchars($evt['category']); ?></span>
          </td>
          <td>
            <strong><?php echo format_date_id($evt['date'], false); ?></strong><br>
            <span style="font-size: 0.78rem; color: var(--adm-text-subtle);"><?php echo htmlspecialchars($evt['time']); ?></span>
          </td>
          <td><?php echo htmlspecialchars($evt['speaker'] ?: '-'); ?></td>
          <td>
            <strong><?php echo $registered; ?></strong> / <?php echo $quota; ?> peserta
            <span style="font-size: 0.75rem; color: var(--adm-text-subtle); display: block;">(sisa <?php echo $rem; ?>)</span>
          </td>
          <td>
            <?php if (!empty($evt['is_featured'])): ?>
              <span class="adm-badge" style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">★ Pop-up Utama</span>
            <?php else: ?>
              <span class="adm-badge adm-badge-published">Publik Aktif</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
