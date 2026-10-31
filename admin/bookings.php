<?php
/**
 * OMAH BELUMBUNGAN - Manajemen Reservasi Kunjungan
 * Table & Calendar Views, Status Lifecycle, Capacity & Blackout Protections
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$adminTitle = 'Kelola Reservasi Kunjungan';
require_once __DIR__ . '/header.php';

$settings = get_settings();
$blackouts = $settings['blackout_dates'] ?? [];
$capacities = $settings['capacity'] ?? [];
$allBookings = get_bookings();

// Tangani aksi status / hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['booking_id'])) {
    require_csrf_token();
    $action = $_POST['action'];
    $bookingId = $_POST['booking_id'];
    $adminNotes = isset($_POST['admin_notes']) ? sanitize($_POST['admin_notes']) : null;

    if ($action === 'delete') {
        $allBookings = array_values(array_filter($allBookings, function($b) use ($bookingId) {
            return $b['id'] !== $bookingId;
        }));
        save_json_data('bookings.json', $allBookings);
        set_flash('success', "Data reservasi <strong>{$bookingId}</strong> berhasil dihapus.");
    } else {
        foreach ($allBookings as &$b) {
            if ($b['id'] === $bookingId) {
                if ($action === 'confirm') {
                    $b['status'] = 'confirmed';
                    set_flash('success', "Reservasi <strong>{$bookingId}</strong> atas nama {$b['name']} berhasil <strong>disetujui</strong>.");
                } elseif ($action === 'reject') {
                    $b['status'] = 'rejected';
                    set_flash('error', "Reservasi <strong>{$bookingId}</strong> atas nama {$b['name']} telah <strong>ditolak</strong>.");
                } elseif ($action === 'pending') {
                    $b['status'] = 'pending';
                    set_flash('warning', "Status reservasi <strong>{$bookingId}</strong> dikembalikan ke <strong>menunggu review</strong>.");
                }

                if ($adminNotes !== null) {
                    $b['admin_notes'] = $adminNotes;
                }
                break;
            }
        }
        save_json_data('bookings.json', $allBookings);
    }
    header('Location: bookings.php' . (isset($_GET['view']) ? '?view=' . urlencode($_GET['view']) : ''));
    exit;
}

// Tampilan: Tabel atau Kalender
$view = $_GET['view'] ?? 'table';

// Filter status pada tabel
$filterStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$filteredBookings = $allBookings;
if ($filterStatus !== 'all') {
    $filteredBookings = array_values(array_filter($filteredBookings, function($b) use ($filterStatus) {
        return ($b['status'] ?? '') === $filterStatus;
    }));
}
if ($searchQuery !== '') {
    $qLower = strtolower($searchQuery);
    $filteredBookings = array_values(array_filter($filteredBookings, function($b) use ($qLower) {
        return str_contains(strtolower($b['id'] ?? ''), $qLower) ||
               str_contains(strtolower($b['name'] ?? ''), $qLower) ||
               str_contains(strtolower($b['institution'] ?? ''), $qLower) ||
               str_contains(strtolower($b['phone'] ?? ''), $qLower) ||
               str_contains(strtolower($b['notes'] ?? ''), $qLower);
    }));
}

// Data untuk Kalender
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDayTimestamp = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int)date('t', $firstDayTimestamp);
$startDayOfWeek = (int)date('w', $firstDayTimestamp); // 0 = Minggu

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$dateMap = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $cDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $dateMap[$cDate] = [
        'is_blackout' => in_array($cDate, $blackouts, true),
        'bookings' => []
    ];
}
foreach ($allBookings as $b) {
    $bDate = $b['date'] ?? '';
    if (isset($dateMap[$bDate])) {
        $dateMap[$bDate]['bookings'][] = $b;
    }
}
?>

<!-- View Switcher & Action Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div class="adm-tabs-bar" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
    <a href="bookings.php?view=table" class="adm-tab-btn <?php echo ($view === 'table') ? 'active' : ''; ?>">
      <?php echo ob_icon('bar-chart', ['size' => 15, 'raw' => true]); ?>
      <span>Tabel Reservasi</span>
      <span class="tab-count"><?php echo count($allBookings); ?></span>
    </a>
    <a href="bookings.php?view=calendar" class="adm-tab-btn <?php echo ($view === 'calendar') ? 'active' : ''; ?>">
      <?php echo ob_icon('calendar', ['size' => 15, 'raw' => true]); ?>
      <span>Kalender Kunjungan</span>
    </a>
  </div>

  <div style="display: flex; gap: 10px; align-items: center;">
    <a href="../reservation.php" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn-secondary adm-btn-sm">
      <?php echo ob_icon('external-link', ['size' => 14, 'raw' => true]); ?> Buka Form Publik &nearr;
    </a>
  </div>
</div>

<?php if ($view === 'table'): ?>
  <!-- ========================================== -->
  <!-- TABEL VIEW                                 -->
  <!-- ========================================== -->
  <div class="adm-card">
    <div class="adm-card-header">
      <!-- Status Filters -->
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="bookings.php?view=table&status=all" class="adm-tab-btn <?php echo ($filterStatus === 'all') ? 'active' : ''; ?>">
          Semua (<?php echo count($allBookings); ?>)
        </a>
        <a href="bookings.php?view=table&status=pending" class="adm-tab-btn <?php echo ($filterStatus === 'pending') ? 'active' : ''; ?>">
          Menunggu Review (<?php echo count(array_filter($allBookings, function($b){return ($b['status']??'')==='pending';})); ?>)
        </a>
        <a href="bookings.php?view=table&status=confirmed" class="adm-tab-btn <?php echo ($filterStatus === 'confirmed') ? 'active' : ''; ?>">
          Disetujui (<?php echo count(array_filter($allBookings, function($b){return ($b['status']??'')==='confirmed';})); ?>)
        </a>
        <a href="bookings.php?view=table&status=rejected" class="adm-tab-btn <?php echo ($filterStatus === 'rejected') ? 'active' : ''; ?>">
          Ditolak (<?php echo count(array_filter($allBookings, function($b){return ($b['status']??'')==='rejected';})); ?>)
        </a>
      </div>

      <!-- Search Form -->
      <form action="bookings.php" method="GET" style="display: flex; gap: 8px; align-items: center;">
        <input type="hidden" name="view" value="table">
        <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
        <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari nama, kode, instansi..." class="adm-input" style="width: 240px; padding: 7px 12px; font-size: 0.85rem;">
        <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">Cari</button>
        <?php if ($searchQuery !== ''): ?>
          <a href="bookings.php?view=table&status=<?php echo htmlspecialchars($filterStatus); ?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Reset pencarian">&times;</a>
        <?php endif; ?>
      </form>
    </div>

    <?php if (empty($filteredBookings)): ?>
      <div style="padding: 40px 20px; text-align: center; color: var(--adm-text-muted);">
        <p>Tidak ada data reservasi yang cocok dengan kriteria pencarian.</p>
      </div>
    <?php else: ?>
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Pemohon &amp; Kontak</th>
              <th>Agenda</th>
              <th>Rencana Tanggal</th>
              <th>Peserta</th>
              <th>Status</th>
              <th>Catatan Pemohon</th>
              <th>Tindakan Operasional</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filteredBookings as $b): 
              $bStatus = $b['status'] ?? 'pending';
              $badgeClass = 'adm-badge-pending';
              $statusLabel = 'Menunggu';
              if ($bStatus === 'confirmed') {
                  $badgeClass = 'adm-badge-confirmed';
                  $statusLabel = 'Disetujui';
              } elseif ($bStatus === 'rejected') {
                  $badgeClass = 'adm-badge-rejected';
                  $statusLabel = 'Ditolak';
              }

              $isBlackout = in_array($b['date'] ?? '', $blackouts, true);

              $waConfirmMsg = "Om Swastyastu Bapak/Ibu " . ($b['name'] ?? '') . ", salam dari Pengelola Omah Belumbungan. "
                            . "Jadwal kunjungan Anda (" . ($b['type_label'] ?? '') . ") "
                            . "pada tanggal " . format_date_id($b['date'] ?? '') . " (" . ($b['session_label'] ?? '') . ") "
                            . "telah DISETUJUI. Silakan konfirmasi bila ada penyesuaian rombongan. Suksma.";
              $waUrl = make_whatsapp_url($b['phone'] ?? '', $waConfirmMsg);
            ?>
            <tr>
              <td style="white-space: nowrap;">
                <strong style="color: var(--adm-forest); font-family: monospace; font-size: 0.82rem; letter-spacing: -0.2px;">
                  <?php echo htmlspecialchars($b['id']); ?>
                </strong>
              </td>
              <td>
                <strong style="color: var(--adm-text);"><?php echo htmlspecialchars($b['name']); ?></strong><br>
                <span style="font-size: 0.78rem; color: var(--adm-text-subtle);"><?php echo htmlspecialchars($b['institution'] ?: 'Pribadi'); ?></span><br>
                <a href="<?php echo htmlspecialchars($waUrl); ?>" target="_blank" rel="noopener noreferrer" style="font-size: 0.78rem; color: #1EBE5D; text-decoration: none; font-weight: 600;">
                  <?php echo ob_icon('whatsapp', ['size' => 12, 'raw' => true]); ?> <?php echo htmlspecialchars($b['phone'] ?? '-'); ?>
                </a>
              </td>
              <td>
                <span class="adm-badge adm-badge-category"><?php echo htmlspecialchars($b['type_label'] ?? '-'); ?></span>
              </td>
              <td>
                <strong><?php echo format_date_id($b['date'] ?? '', false); ?></strong><br>
                <span style="font-size: 0.78rem; color: var(--adm-text-subtle);"><?php echo htmlspecialchars($b['session_label'] ?? '-'); ?></span>
                <?php if ($isBlackout): ?>
                  <span class="adm-badge adm-badge-rejected" style="font-size: 0.68rem; margin-top: 3px; display: inline-block;">⚠️ Hari Libur Adat</span>
                <?php endif; ?>
              </td>
              <td>
                <strong><?php echo (int)($b['participants_count'] ?? 1); ?></strong> org
              </td>
              <td>
                <span class="adm-badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
              </td>
              <td>
                <div style="max-width: 200px; font-size: 0.82rem; color: var(--adm-text-muted); line-height: 1.4;">
                  <?php echo htmlspecialchars($b['notes'] ?: '—'); ?>
                  <?php if (!empty($b['admin_notes'])): ?>
                    <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed var(--adm-border-light); font-size: 0.76rem; color: var(--adm-forest);">
                      <em>Catatan Admin: <?php echo htmlspecialchars($b['admin_notes']); ?></em>
                    </div>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                  <?php if ($bStatus !== 'confirmed'): ?>
                    <form method="POST" style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                      <input type="hidden" name="action" value="confirm">
                      <button type="submit" class="adm-btn adm-btn-success adm-btn-sm" title="Setujui Kunjungan" onclick="return confirm('Setujui reservasi <?php echo htmlspecialchars($b['id']); ?>?');">
                        Setujui
                      </button>
                    </form>
                  <?php endif; ?>

                  <?php if ($bStatus !== 'rejected'): ?>
                    <form method="POST" style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                      <input type="hidden" name="action" value="reject">
                      <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Tolak Pengajuan" onclick="return confirm('Tolak permohonan <?php echo htmlspecialchars($b['id']); ?>?');">
                        Tolak
                      </button>
                    </form>
                  <?php endif; ?>

                  <?php if ($bStatus !== 'pending'): ?>
                    <form method="POST" style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                      <input type="hidden" name="action" value="pending">
                      <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm" title="Kembalikan status ke Pending">
                        Reset
                      </button>
                    </form>
                  <?php endif; ?>

                  <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus permanen data reservasi <?php echo htmlspecialchars($b['id']); ?>? Tindakan ini tidak dapat dibatalkan.');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm" style="color: var(--adm-red);" title="Hapus Data">
                      Hapus
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

<?php else: ?>
  <!-- ========================================== -->
  <!-- KALENDER VIEW                              -->
  <!-- ========================================== -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div style="display: flex; align-items: center; gap: 14px;">
        <h2 class="adm-card-title" style="margin-bottom: 0;">
          <span><?php echo $monthNames[$month]; ?> <?php echo $year; ?></span>
        </h2>
        <div style="display: flex; gap: 6px;">
          <a href="bookings.php?view=calendar&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="adm-btn adm-btn-secondary adm-btn-sm">&larr; Bulan Lalu</a>
          <a href="bookings.php?view=calendar&month=<?php echo (int)date('n'); ?>&year=<?php echo (int)date('Y'); ?>" class="adm-btn adm-btn-secondary adm-btn-sm">Bulan Ini</a>
          <a href="bookings.php?view=calendar&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="adm-btn adm-btn-secondary adm-btn-sm">Bulan Depan &rarr;</a>
        </div>
      </div>

      <div style="display: flex; gap: 12px; font-size: 0.8rem; align-items: center;">
        <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; background: #2E7D32; border-radius: 50%;"></span> Disetujui</span>
        <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; background: #D97706; border-radius: 50%;"></span> Menunggu</span>
        <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; background: #DC2626; border-radius: 50%;"></span> Libur Adat</span>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
      <?php foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayLabel): ?>
        <div style="padding: 10px; text-align: center; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; color: var(--adm-forest); background: var(--adm-surface-alt); border-radius: 6px;">
          <?php echo $dayLabel; ?>
        </div>
      <?php endforeach; ?>

      <!-- Padding hari kosong sebelum tanggal 1 -->
      <?php for ($i = 0; $i < $startDayOfWeek; $i++): ?>
        <div style="min-height: 100px; background: #FAF8F4; border-radius: 8px; opacity: 0.4;"></div>
      <?php endfor; ?>

      <!-- Tanggal dalam bulan -->
      <?php for ($d = 1; $d <= $daysInMonth; $d++): 
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $dayData = $dateMap[$dateStr] ?? ['is_blackout' => false, 'bookings' => []];
        $isToday = ($dateStr === date('Y-m-d'));
      ?>
        <div style="min-height: 110px; background: #FFFFFF; border: 1px solid <?php echo $isToday ? 'var(--adm-primary)' : 'var(--adm-border)'; ?>; border-radius: 8px; padding: 8px; display: flex; flex-direction: column; gap: 4px; position: relative; <?php echo $dayData['is_blackout'] ? 'background-color: #FFF5F5;' : ''; ?>">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <strong style="font-size: 0.95rem; color: <?php echo $isToday ? 'var(--adm-primary)' : 'var(--adm-text)'; ?>;">
              <?php echo $d; ?>
            </strong>
            <?php if ($dayData['is_blackout']): ?>
              <span style="font-size: 0.65rem; color: #DC2626; font-weight: 700;" title="Hari Libur Adat">⛔ Adat</span>
            <?php endif; ?>
          </div>

          <!-- Bookings for this date -->
          <div style="display: flex; flex-direction: column; gap: 3px; margin-top: 4px;">
            <?php foreach ($dayData['bookings'] as $bk): 
              $isConf = (($bk['status'] ?? '') === 'confirmed');
              $bgC = $isConf ? '#E8F5E9' : '#FEF3C7';
              $textC = $isConf ? '#1B5E20' : '#B45309';
            ?>
              <div style="font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; background: <?php echo $bgC; ?>; color: <?php echo $textC; ?>; font-weight: 600; line-height: 1.2; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo htmlspecialchars($bk['name'] . ' (' . ($bk['participants_count'] ?? 1) . ' org) - ' . ($bk['type_label'] ?? '')); ?>">
                <?php echo htmlspecialchars($bk['name']); ?> (<?php echo (int)($bk['participants_count'] ?? 1); ?>)
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
