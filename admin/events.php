<?php
/**
 * OMAH BELUMBUNGAN - Manajemen Event, Lokakarya & Sarasehan
 * Full Structured Editor, Category Filtering, Status Lifecycle & Media Selection
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$adminTitle = 'Kelola Agenda & Workshop';
require_once __DIR__ . '/header.php';

$allEvents = get_events();
$editEvent = null;

// Tangani edit melalui parameter GET
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($allEvents as $e) {
        if ($e['id'] === $editId) {
            $editEvent = $e;
            break;
        }
    }
}

// Tangani toggle status langsung via GET (Publish / Draft / Archive)
if (isset($_GET['toggle_status'], $_GET['to'])) {
    $toggleId = $_GET['toggle_status'];
    $toStatus = sanitize($_GET['to']);
    foreach ($allEvents as &$e) {
        if ($e['id'] === $toggleId) {
            $e['status'] = $toStatus;
            save_json_data('events.json', $allEvents);
            set_flash('success', "Status agenda '{$e['title']}' diubah menjadi <strong>" . strtoupper($toStatus) . "</strong>.");
            break;
        }
    }
    header('Location: events.php');
    exit;
}

// Tangani aksi POST (Simpan / Hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Aksi Hapus Event
    if ($action === 'delete') {
        $delId = $_POST['event_id'];
        $allEvents = array_values(array_filter($allEvents, function($e) use ($delId) {
            return $e['id'] !== $delId;
        }));
        save_json_data('events.json', $allEvents);
        set_flash('success', "Event berhasil dihapus.");
        header('Location: events.php');
        exit;
    }

    // Aksi Simpan Event (Create / Update)
    if ($action === 'save') {
        $eventId = $_POST['event_id'] ?: ('evt_' . time());
        $title = sanitize($_POST['title'] ?? '');
        $shortTitle = sanitize($_POST['short_title'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        }
        $category = strtoupper(sanitize($_POST['category'] ?? 'LOKAKARYA'));
        $date = sanitize($_POST['date'] ?? '');
        $time = sanitize($_POST['time'] ?? '09:00 - 15:00 WITA');
        $location = sanitize($_POST['location'] ?? 'Omah Belumbungan, Sibang Kaja');
        $speaker = sanitize($_POST['speaker'] ?? '');
        $speakerRole = sanitize($_POST['speaker_role'] ?? '');
        $shortDesc = sanitize($_POST['short_description'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $quota = (int)($_POST['quota'] ?? 30);
        $registered = (int)($_POST['registered'] ?? 0);
        $fee = sanitize($_POST['fee'] ?? 'Gratis');
        $image = sanitize($_POST['image'] ?? 'assets/images/photo-01.jpg');
        $status = sanitize($_POST['status'] ?? 'upcoming');
        $isFeatured = isset($_POST['is_featured']);
        $showPopup = isset($_POST['show_popup']);

        // Date badge string formatting (e.g. 21 NOV 2026)
        $badgeDate = '';
        if (!empty($date)) {
            $t = strtotime($date);
            $months = ['', 'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
            $badgeDate = date('j', $t) . ' ' . ($months[(int)date('n', $t)] ?? '') . ' ' . date('Y', $t);
        }

        $eventData = [
            'id' => $eventId,
            'title' => $title,
            'short_title' => $shortTitle ?: $title,
            'slug' => $slug,
            'category' => $category,
            'date' => $date,
            'badge_date' => $badgeDate,
            'time' => $time,
            'location' => $location,
            'speaker' => $speaker,
            'speaker_role' => $speakerRole,
            'short_description' => $shortDesc,
            'description' => $description,
            'quota' => $quota,
            'registered' => $registered,
            'fee' => $fee,
            'image' => $image,
            'is_featured' => $isFeatured,
            'show_popup' => $showPopup,
            'status' => $status
        ];

        // Jika event ini dijadikan featured countdown, matikan featured event lain
        if ($isFeatured) {
            foreach ($allEvents as &$other) {
                if ($other['id'] !== $eventId) {
                    $other['is_featured'] = false;
                }
            }
        }

        $found = false;
        foreach ($allEvents as &$existing) {
            if ($existing['id'] === $eventId) {
                $existing = $eventData;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $allEvents[] = $eventData;
        }

        save_json_data('events.json', $allEvents);
        set_flash('success', "Data agenda '<strong>{$title}</strong>' berhasil disimpan.");
        header('Location: events.php');
        exit;
    }
}

// Filter status dan pencarian
$filterStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$filteredEvents = $allEvents;
if ($filterStatus !== 'all') {
    $filteredEvents = array_values(array_filter($filteredEvents, function($e) use ($filterStatus) {
        return ($e['status'] ?? '') === $filterStatus;
    }));
}
if ($searchQuery !== '') {
    $qLower = strtolower($searchQuery);
    $filteredEvents = array_values(array_filter($filteredEvents, function($e) use ($qLower) {
        return str_contains(strtolower($e['title'] ?? ''), $qLower) ||
               str_contains(strtolower($e['speaker'] ?? ''), $qLower) ||
               str_contains(strtolower($e['category'] ?? ''), $qLower);
    }));
}
?>

<!-- Header Actions & Search -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <!-- Status Tabs -->
  <div class="adm-tabs-bar" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
    <a href="events.php?status=all" class="adm-tab-btn <?php echo ($filterStatus === 'all') ? 'active' : ''; ?>">
      Semua Agenda <span class="tab-count"><?php echo count($allEvents); ?></span>
    </a>
    <a href="events.php?status=upcoming" class="adm-tab-btn <?php echo ($filterStatus === 'upcoming') ? 'active' : ''; ?>">
      Mendatang / Terjadwal <span class="tab-count"><?php echo count(array_filter($allEvents, function($e){return ($e['status']??'')==='upcoming';})); ?></span>
    </a>
    <a href="events.php?status=completed" class="adm-tab-btn <?php echo ($filterStatus === 'completed') ? 'active' : ''; ?>">
      Selesai (Arsip) <span class="tab-count"><?php echo count(array_filter($allEvents, function($e){return ($e['status']??'')==='completed';})); ?></span>
    </a>
    <a href="events.php?status=draft" class="adm-tab-btn <?php echo ($filterStatus === 'draft') ? 'active' : ''; ?>">
      Draft <span class="tab-count"><?php echo count(array_filter($allEvents, function($e){return ($e['status']??'')==='draft';})); ?></span>
    </a>
  </div>

  <div style="display: flex; gap: 10px; align-items: center;">
    <form action="events.php" method="GET" style="display: flex; gap: 6px;">
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
      <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari topik agenda..." class="adm-input" style="width: 200px; padding: 7px 12px; font-size: 0.85rem;">
      <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">Cari</button>
    </form>
    <a href="../events.php" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn-secondary adm-btn-sm">
      <?php echo ob_icon('external-link', ['size' => 14, 'raw' => true]); ?> Lihat Halaman Publik &nearr;
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 28px; align-items: start;">
  <!-- KOLOM 1: Form Tambah / Edit Event Terstruktur -->
  <div class="adm-card">
    <div class="adm-card-header">
      <h2 class="adm-card-title">
        <span><?php echo $editEvent ? '✏️ Edit Agenda Kegiatan' : '+ Tambah Agenda Baru'; ?></span>
      </h2>
      <?php if ($editEvent): ?>
        <a href="events.php" class="adm-btn adm-btn-secondary adm-btn-sm">Batal Edit</a>
      <?php endif; ?>
    </div>

    <form action="events.php" method="POST">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($editEvent['id'] ?? ''); ?>">

      <div class="adm-form-group">
        <label for="title" class="adm-label">Judul Lengkap Agenda *</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>" required placeholder="Contoh: Lokakarya Terbuka: Membaca Anatomi Rumah Bali & Asta Kosala Kosali" class="adm-input">
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="short_title" class="adm-label">Judul Ringkas (Card)<span class="adm-label-hint">Opsional</span></label>
          <input type="text" id="short_title" name="short_title" value="<?php echo htmlspecialchars($editEvent['short_title'] ?? ''); ?>" placeholder="Contoh: Lokakarya Anatomi Rumah Bali" class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="slug" class="adm-label">Slug URL</label>
          <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($editEvent['slug'] ?? ''); ?>" placeholder="lokakarya-anatomi-rumah-bali" class="adm-input">
        </div>
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="category" class="adm-label">Kategori *</label>
          <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($editEvent['category'] ?? 'LOKAKARYA'); ?>" required placeholder="LOKAKARYA / SARASEHAN / KELAS SENI" class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="status" class="adm-label">Status Publikasi *</label>
          <select id="status" name="status" class="adm-select">
            <option value="upcoming" <?php echo (($editEvent['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>>Mendatang (Aktif Terbuka)</option>
            <option value="completed" <?php echo (($editEvent['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Telah Selesai (Arsip)</option>
            <option value="draft" <?php echo (($editEvent['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft (Konsep Internal)</option>
          </select>
        </div>
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="date" class="adm-label">Tanggal Pelaksanaan *</label>
          <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($editEvent['date'] ?? ''); ?>" required class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="time" class="adm-label">Waktu / Jam (WITA) *</label>
          <input type="text" id="time" name="time" value="<?php echo htmlspecialchars($editEvent['time'] ?? '09:00 - 15:30 WITA'); ?>" required class="adm-input">
        </div>
      </div>

      <div class="adm-form-group">
        <label for="location" class="adm-label">Lokasi / Tempat *</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($editEvent['location'] ?? 'Omah Belumbungan, Sibang Kaja, Abiansemal'); ?>" required class="adm-input">
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="speaker" class="adm-label">Narasumber / Undagi</label>
          <input type="text" id="speaker" name="speaker" value="<?php echo htmlspecialchars($editEvent['speaker'] ?? ''); ?>" placeholder="Contoh: I Ketut Wirawan" class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="speaker_role" class="adm-label">Peran / Gelar Pemateri</label>
          <input type="text" id="speaker_role" name="speaker_role" value="<?php echo htmlspecialchars($editEvent['speaker_role'] ?? ''); ?>" placeholder="Contoh: Undagi & Praktisi Arsitektur Bali" class="adm-input">
        </div>
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="quota" class="adm-label">Kapasitas Kursi (Kuota)</label>
          <input type="number" id="quota" name="quota" value="<?php echo (int)($editEvent['quota'] ?? 30); ?>" required class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="registered" class="adm-label">Jumlah Pendaftar Saat Ini</label>
          <input type="number" id="registered" name="registered" value="<?php echo (int)($editEvent['registered'] ?? 0); ?>" required class="adm-input">
        </div>
      </div>

      <div class="adm-form-group">
        <label for="fee" class="adm-label">Biaya / Investasi</label>
        <input type="text" id="fee" name="fee" value="<?php echo htmlspecialchars($editEvent['fee'] ?? 'Gratis'); ?>" placeholder="Contoh: Rp 150.000 / orang (termasuk konsumsi & modul materi)" class="adm-input">
      </div>

      <div class="adm-form-group">
        <label for="image" class="adm-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span>Foto Sampul (Path Berkas) *</span>
          <a href="media.php" target="_blank" style="font-size: 0.74rem; color: var(--adm-primary); font-weight: 500;">Buka Media Library &nearr;</a>
        </label>
        
        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 10px;">
          <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($editEvent['image'] ?? 'assets/images/photo-01.jpg'); ?>" class="adm-input" placeholder="assets/images/photo-01.jpg" oninput="updateImagePreview(this.value, 'eventImageThumb', 'eventImagePath')">
          
          <!-- Tombol Upload Gambar Langsung -->
          <label for="eventImageUploadFile" class="adm-btn adm-btn-primary adm-btn-sm" style="cursor: pointer; flex-shrink: 0; height: 38px;" title="Unggah gambar baru langsung dari perangkat">
            📤 Upload Gambar
          </label>
          <input type="file" id="eventImageUploadFile" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="handleSingleImageUpload(this, 'image', 'events', 'eventImageThumb', 'eventImagePath', 'eventImageStatus')">
          
          <!-- Tombol Pilih dari Pustaka Media -->
          <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" style="flex-shrink: 0; height: 38px;" onclick="openMediaPickerModal('image', 'eventImageThumb', 'eventImagePath')" title="Pilih foto dari pustaka media">
            🖼️ Pilih Media
          </button>
        </div>

        <!-- Pratinjau Visual Gambar Terpilih -->
        <div style="display: flex; align-items: center; gap: 14px; background: var(--adm-surface-alt); border: 1px solid var(--adm-border); border-radius: var(--adm-radius-md); padding: 10px 14px;">
          <img id="eventImageThumb" src="../<?php echo htmlspecialchars($editEvent['image'] ?? 'assets/images/photo-01.jpg'); ?>" alt="Pratinjau Sampul" style="width: 72px; height: 50px; object-fit: cover; border-radius: var(--adm-radius-sm); border: 1px solid var(--adm-border); background: #FFFFFF; flex-shrink: 0;" onerror="this.src='../assets/images/photo-01.jpg';">
          <div style="flex-grow: 1; min-width: 0;">
            <div id="eventImagePath" style="font-size: 0.82rem; font-weight: 700; color: var(--adm-forest); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              <?php echo htmlspecialchars($editEvent['image'] ?? 'assets/images/photo-01.jpg'); ?>
            </div>
            <div id="eventImageStatus" style="font-size: 0.74rem; color: var(--adm-text-subtle); margin-top: 2px;">
              Foto sampul yang tampil di kartu agenda dan hitung mundur
            </div>
          </div>
        </div>
      </div>

      <div class="adm-form-group">
        <label for="short_description" class="adm-label">Deskripsi Singkat (Ringkasan 1-2 Kalimat)</label>
        <textarea id="short_description" name="short_description" rows="2" class="adm-textarea"><?php echo htmlspecialchars($editEvent['short_description'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="description" class="adm-label">Deskripsi Lengkap Materi / Rincian Acara</label>
        <textarea id="description" name="description" rows="4" class="adm-textarea"><?php echo htmlspecialchars($editEvent['description'] ?? ''); ?></textarea>
      </div>

      <!-- Fitur Unggulan -->
      <div style="background: var(--adm-surface-alt); padding: 16px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border); margin-bottom: 22px;">
        <label class="adm-checkbox-label" style="margin-bottom: 8px;">
          <input type="checkbox" name="is_featured" value="1" <?php echo !empty($editEvent['is_featured']) ? 'checked' : ''; ?>>
          <strong>Jadikan Countdown Unggulan Beranda</strong>
        </label>
        <label class="adm-checkbox-label">
          <input type="checkbox" name="show_popup" value="1" <?php echo !empty($editEvent['show_popup']) ? 'checked' : ''; ?>>
          <span>Munculkan sebagai pop-up pengumuman saat pengunjung membuka website</span>
        </label>
      </div>

      <button type="submit" class="adm-btn adm-btn-primary" style="width: 100%;">
        <?php echo $editEvent ? 'Simpan Perubahan Agenda' : 'Terbitkan Agenda Baru'; ?> &rarr;
      </button>
    </form>
  </div>

  <!-- KOLOM 2: Daftar Event Terdaftar -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div>
        <h2 class="adm-card-title">
          <span>Daftar Agenda (<?php echo count($filteredEvents); ?>)</span>
        </h2>
        <div class="adm-card-subtitle">Menampilkan seluruh agenda sesuai filter status</div>
      </div>
    </div>

    <?php if (empty($filteredEvents)): ?>
      <p style="padding: 30px; text-align: center; color: var(--adm-text-muted);">Tidak ada agenda yang cocok dengan filter ini.</p>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($filteredEvents as $evt): 
          $quota = (int)($evt['quota'] ?? 0);
          $reg = (int)($evt['registered'] ?? 0);
          $rem = max(0, $quota - $reg);
          $status = $evt['status'] ?? 'upcoming';
          
          $statusBadge = 'adm-badge-confirmed';
          $statusLabel = 'Mendatang';
          if ($status === 'completed') {
              $statusBadge = 'adm-badge-completed';
              $statusLabel = 'Selesai';
          } elseif ($status === 'draft') {
              $statusBadge = 'adm-badge-draft';
              $statusLabel = 'Draft';
          }
        ?>
        <div style="background: var(--adm-surface-alt); border: 1px solid var(--adm-border); border-radius: var(--adm-radius-md); padding: 16px; display: flex; flex-direction: column; gap: 10px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
            <div>
              <span class="adm-badge adm-badge-category" style="font-size: 0.7rem; margin-bottom: 4px;"><?php echo htmlspecialchars($evt['category']); ?></span>
              <h3 style="font-size: 1rem; color: var(--adm-forest); font-family: var(--adm-font-serif); margin-bottom: 2px;">
                <?php echo htmlspecialchars($evt['title']); ?>
              </h3>
              <div style="font-size: 0.8rem; color: var(--adm-text-subtle);">
                <?php echo format_date_id($evt['date'], false); ?> &bull; <?php echo htmlspecialchars($evt['time']); ?>
              </div>
            </div>
            <span class="adm-badge <?php echo $statusBadge; ?>"><?php echo $statusLabel; ?></span>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; color: var(--adm-text-muted); border-top: 1px solid var(--adm-border-light); padding-top: 8px;">
            <div>
              Pemateri: <strong><?php echo htmlspecialchars($evt['speaker'] ?: '-'); ?></strong> | Kuota: <strong><?php echo $reg; ?>/<?php echo $quota; ?></strong> (sisa <?php echo $rem; ?>)
            </div>
            <div style="display: flex; gap: 6px;">
              <a href="../events.php#<?php echo htmlspecialchars($evt['id']); ?>" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm" title="Pratinjau di Web Publik">
                Pratinjau &nearr;
              </a>
              <a href="events.php?edit=<?php echo urlencode($evt['id']); ?>" class="adm-btn adm-btn-forest adm-btn-sm" title="Edit">
                Edit
              </a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus permanen agenda \'<?php echo htmlspecialchars(addslashes($evt['title'])); ?>\'?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($evt['id']); ?>">
                <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Hapus">
                  Hapus
                </button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
