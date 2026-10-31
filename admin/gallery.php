<?php
/**
 * OMAH BELUMBUNGAN - Admin CMS Gallery Event
 * Mengelola dokumentasi fotografi kegiatan dengan Batch Upload Maksimal 10 Foto
 */
$adminTitle = 'Kelola Gallery Event (Batch 10 Foto)';
require_once __DIR__ . '/header.php';

$allGalleries = get_gallery_events();
$editGallery = null;

// Tangani edit melalui parameter GET
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($allGalleries as $g) {
        if ($g['id'] === $editId) {
            $editGallery = $g;
            break;
        }
    }
}

// Tangani toggle status publikasi via GET
if (isset($_GET['toggle_status'])) {
    $toggleId = $_GET['toggle_status'];
    foreach ($allGalleries as &$g) {
        if ($g['id'] === $toggleId) {
            $g['status'] = ($g['status'] === 'published') ? 'draft' : 'published';
            save_json_data('gallery.json', $allGalleries);
            set_flash('success', "Status galeri '{$g['title']}' diubah menjadi " . strtoupper($g['status']));
            break;
        }
    }
    header('Location: gallery.php');
    exit;
}

// Tangani aksi POST (Simpan / Hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $action = $_POST['action'] ?? '';

    // 1. Aksi Hapus Galeri
    if ($action === 'delete') {
        $delId = $_POST['gallery_id'];
        $allGalleries = array_values(array_filter($allGalleries, function($g) use ($delId) {
            return $g['id'] !== $delId;
        }));
        save_json_data('gallery.json', $allGalleries);
        set_flash('success', "Dokumentasi event berhasil dihapus.");
        header('Location: gallery.php');
        exit;
    }

    // 2. Aksi Simpan Galeri (Create / Update)
    if ($action === 'save') {
        $galId = $_POST['gallery_id'] ?: ('gal_' . time());
        $title = sanitize($_POST['title'] ?? '');
        $category = sanitize($_POST['category'] ?? 'Sarasehan Budaya');
        $eventDate = sanitize($_POST['event_date'] ?? date('Y-m-d'));
        $shortDesc = sanitize($_POST['short_description'] ?? '');
        $fullDesc = sanitize($_POST['full_description'] ?? '');
        $status = sanitize($_POST['status'] ?? 'draft');

        // Ambil array foto yang telah diunggah / dipertahankan
        $submittedPhotos = isset($_POST['photos']) ? (array)$_POST['photos'] : [];
        $submittedPhotos = array_values(array_filter(array_map('sanitize', $submittedPhotos)));

        // Tangani jika ada upload file langsung melalui form POST (fallback/non-ajax)
        if (isset($_FILES['direct_photos']) && is_array($_FILES['direct_photos']['name'])) {
            $directCount = count($_FILES['direct_photos']['name']);
            for ($i = 0; $i < $directCount; $i++) {
                if ($_FILES['direct_photos']['error'][$i] === UPLOAD_ERR_OK) {
                    // Validasi batas 10 foto di sisi server
                    if (count($submittedPhotos) >= 10) {
                        break;
                    }
                    $tmp = $_FILES['direct_photos']['tmp_name'][$i];
                    $name = $_FILES['direct_photos']['name'][$i];
                    $res = upload_image_secure($tmp, $name, 'gallery');
                    if ($res['success']) {
                        $submittedPhotos[] = $res['path'];
                    }
                }
            }
        }

        // ENFORCE SERVER-SIDE VALIDATION: Maksimal 10 foto per event gallery
        if (count($submittedPhotos) > 10) {
            set_flash('error', 'Validasi gagal: Jumlah foto melebihi batas maksimal 10 foto per event (terdeteksi ' . count($submittedPhotos) . ' foto).');
            header('Location: gallery.php' . ($editGallery ? '?edit=' . urlencode($galId) : ''));
            exit;
        }

        // Tentukan foto sampul (cover photo)
        $coverPhoto = sanitize($_POST['selected_cover'] ?? '');
        if (empty($coverPhoto) || !in_array($coverPhoto, $submittedPhotos, true)) {
            $coverPhoto = !empty($submittedPhotos) ? $submittedPhotos[0] : '';
        }

        $galleryData = [
            'id' => $galId,
            'title' => $title,
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))),
            'event_date' => $eventDate,
            'category' => $category,
            'cover_photo' => $coverPhoto,
            'short_description' => $shortDesc,
            'full_description' => $fullDesc,
            'photos' => $submittedPhotos,
            'status' => $status,
            'created_at' => $editGallery['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $found = false;
        foreach ($allGalleries as &$existing) {
            if ($existing['id'] === $galId) {
                $existing = $galleryData;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $allGalleries[] = $galleryData;
        }

        save_json_data('gallery.json', $allGalleries);
        set_flash('success', "Galeri '{$title}' berhasil disimpan dengan " . count($submittedPhotos) . " foto (" . strtoupper($status) . ").");
        header('Location: gallery.php');
        exit;
    }
}

$currentPhotos = $editGallery['photos'] ?? [];
$currentCover = $editGallery['cover_photo'] ?? ($currentPhotos[0] ?? '');
?>

<!-- Header Actions & Navigation -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-family: var(--adm-font-serif); font-size: 1.35rem; font-weight: 700; color: var(--adm-forest); margin-bottom: 4px;">Kelola Gallery Event</h2>
    <p style="font-size: 0.84rem; color: var(--adm-text-muted);">Dokumentasi fotografi kegiatan dengan Batch Upload hingga 10 foto per event.</p>
  </div>
  <div style="display: flex; gap: 10px; align-items: center;">
    <a href="../gallery.php" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn-secondary adm-btn-sm">
      <?php echo ob_icon('external-link', ['size' => 14, 'raw' => true]); ?> Lihat Galeri Publik &nearr;
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 28px; align-items: start;">
  <!-- Formulir Buat / Edit Gallery Event -->
  <div class="adm-card">
    <div class="adm-card-header" style="align-items: flex-start; flex-wrap: nowrap;">
      <div>
        <h3 class="adm-card-title">
          <span><?php echo $editGallery ? '✏️ Edit Dokumentasi Event' : '+ Tambah Dokumentasi Event Baru'; ?></span>
        </h3>
        <div class="adm-card-subtitle">Form pengisian informasi kegiatan &amp; unggah batch foto</div>
      </div>
      <?php if ($editGallery): ?>
        <a href="gallery.php" class="adm-btn adm-btn-secondary adm-btn-sm" style="flex-shrink: 0;">Batal Edit</a>
      <?php endif; ?>
    </div>

    <form action="gallery.php" method="POST" enctype="multipart/form-data" id="galleryForm">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="gallery_id" value="<?php echo htmlspecialchars($editGallery['id'] ?? ''); ?>">

      <div class="adm-form-group">
        <label for="title" class="adm-label">Nama Kegiatan / Event *</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editGallery['title'] ?? ''); ?>" required placeholder="Contoh: Sarasehan &amp; Praktik Pengukuran Asta Kosala Kosali Bersama Undagi" class="adm-input">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div class="adm-form-group">
          <label for="category" class="adm-label">Kategori Kegiatan *</label>
          <select id="category" name="category" required class="adm-select">
            <?php 
            $cats = ['Sarasehan Budaya', 'Workshop Arsitektur', 'Kunjungan Edukasi', 'Workshop Kriya Adat', 'Riset & Dokumentasi'];
            $curCat = $editGallery['category'] ?? 'Sarasehan Budaya';
            foreach ($cats as $c): ?>
              <option value="<?php echo $c; ?>" <?php echo ($curCat === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="adm-form-group">
          <label for="event_date" class="adm-label">Tanggal Pelaksanaan *</label>
          <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($editGallery['event_date'] ?? date('Y-m-d')); ?>" required class="adm-input">
        </div>
      </div>

      <div class="adm-form-group">
        <label for="short_description" class="adm-label">Deskripsi Ringkas Kegiatan (Untuk Kartu Indeks) *</label>
        <textarea id="short_description" name="short_description" rows="2" required placeholder="Ringkasan 1-2 kalimat mengenai fokus dan atmosfer kegiatan yang telah selesai." class="adm-textarea" style="min-height: 72px;"><?php echo htmlspecialchars($editGallery['short_description'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="full_description" class="adm-label">Catatan / Narasi Lengkap Kegiatan <span class="adm-label-hint">Opsional</span></label>
        <textarea id="full_description" name="full_description" rows="4" placeholder="Tuliskan dokumentasi jalannya sarasehan, nama pemateri undagi, temuan studi, dan pesan budaya." class="adm-textarea"><?php echo htmlspecialchars($editGallery['full_description'] ?? ''); ?></textarea>
      </div>

      <!-- ==================================================================
           CRITICAL FEATURE: BATCH PHOTO UPLOAD AREA (MAX 10 PHOTOS)
           ================================================================== -->
      <div class="adm-form-group">
        <label class="adm-label">Unggah Foto Dokumentasi Kegiatan <span class="adm-label-hint">Maksimal 10 Foto</span></label>
        <div id="dropzone" style="border: 2px dashed rgba(169, 71, 43, 0.4); border-radius: var(--adm-radius-lg); padding: 26px 20px; text-align: center; background: #FAF7F0; position: relative; transition: all 0.2s ease;">
          <div style="font-size: 2.2rem; margin-bottom: 8px;">📸</div>
          <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--adm-forest); margin-bottom: 6px;">Tarik dan lepas hingga 10 foto di sini</h4>
          <p style="font-size: 0.84rem; color: var(--adm-text-muted); margin-bottom: 16px;">
            Format didukung: JPG, PNG, WebP. Maksimal 10 foto untuk setiap event.
          </p>

          <!-- Live Photo Counter Badge -->
          <div style="display: inline-flex; align-items: center; gap: 8px; background: #FFFFFF; border: 1px solid var(--adm-border); padding: 5px 16px; border-radius: var(--adm-radius-pill); font-size: 0.82rem; margin-bottom: 16px; box-shadow: var(--adm-shadow-sm);">
            <span style="color: var(--adm-text-muted); font-weight: 600;">Status Kuota:</span>
            <span id="photoCountDisplay" style="color: var(--adm-primary); font-weight: 800; font-size: 0.95rem;"><?php echo count($currentPhotos); ?> / 10 foto</span>
          </div>

          <div>
            <label for="batchFileInput" class="adm-btn adm-btn-primary adm-btn-sm" id="selectFilesBtn" style="cursor: pointer; box-shadow: 0 2px 8px rgba(169, 71, 43, 0.25);">
              📁 Pilih Berkas Foto dari Komputer
            </label>
            <input type="file" id="batchFileInput" accept="image/jpeg,image/png,image/webp" multiple style="display: none;">
          </div>

          <!-- Upload Progress Indicator -->
          <div id="uploadProgressBox" style="display: none; margin-top: 16px; background: #FFFFFF; padding: 12px 16px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border); text-align: left;">
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 6px; font-weight: 600; color: var(--adm-forest);">
              <span id="uploadStatusText">Mengunggah foto...</span>
              <span id="uploadPercentText">0%</span>
            </div>
            <div style="background: var(--adm-border-light); height: 8px; border-radius: 4px; overflow: hidden;">
              <div id="uploadProgressBar" style="width: 0%; height: 100%; background: #2E7D32; transition: width 0.2s;"></div>
            </div>
          </div>

          <!-- Limit Alert Warning -->
          <div id="limitReachedAlert" style="display: <?php echo (count($currentPhotos) >= 10) ? 'block' : 'none'; ?>; margin-top: 14px; background: var(--adm-amber-light); color: #92400E; border: 1px solid var(--adm-amber-border); padding: 10px 14px; border-radius: var(--adm-radius-md); font-size: 0.82rem; font-weight: 600;">
            ⚠️ Batas maksimal 10 foto untuk event ini telah tercapai. Hapus salah satu foto terlebih dahulu untuk mengunggah foto pengganti.
          </div>
        </div>
      </div>

      <!-- Live Photo Preview & Reordering Grid -->
      <div class="adm-form-group">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <label class="adm-label" style="margin-bottom: 0;">Koleksi Foto Terpilih &amp; Sampul Utama</label>
          <span style="font-size: 0.74rem; color: var(--adm-text-subtle);">Pilih radio untuk foto sampul album</span>
        </div>
        
        <div id="photoPreviewGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 12px; margin-top: 10px;">
          <?php foreach ($currentPhotos as $idx => $ph): 
            $isCover = ($ph === $currentCover);
          ?>
          <div class="photo-preview-item" data-path="<?php echo htmlspecialchars($ph); ?>" style="position: relative; border: 2px solid <?php echo $isCover ? 'var(--adm-primary)' : 'var(--adm-border)'; ?>; border-radius: var(--adm-radius-md); overflow: hidden; background: #FFFFFF; padding: 4px; box-shadow: var(--adm-shadow-sm); transition: border-color 0.2s ease;">
            <input type="hidden" name="photos[]" value="<?php echo htmlspecialchars($ph); ?>">
            <img src="../<?php echo htmlspecialchars($ph); ?>" alt="Preview" style="width: 100%; height: 76px; object-fit: cover; border-radius: 4px;">
            
            <div style="padding: 6px 4px 4px; text-align: center; font-size: 0.72rem;">
              <label style="cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; font-weight: 700; color: <?php echo $isCover ? 'var(--adm-primary)' : 'var(--adm-text)'; ?>;">
                <input type="radio" name="selected_cover" value="<?php echo htmlspecialchars($ph); ?>" <?php echo $isCover ? 'checked' : ''; ?> onchange="updateCoverHighlight();">
                Sampul
              </label>

              <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 0 2px;">
                <button type="button" onclick="movePhoto(this, -1)" title="Geser ke kiri" class="adm-btn adm-btn-secondary" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 4px; height: 22px;">&larr;</button>
                <button type="button" onclick="removePhoto(this)" title="Hapus foto" style="background: none; border: none; color: var(--adm-red); font-weight: bold; cursor: pointer; font-size: 0.95rem; padding: 0 4px; line-height: 1;">&times;</button>
                <button type="button" onclick="movePhoto(this, 1)" title="Geser ke kanan" class="adm-btn adm-btn-secondary" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 4px; height: 22px;">&rarr;</button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Status Publikasi -->
      <div style="background: var(--adm-surface-alt); border: 1px solid var(--adm-border); padding: 16px; border-radius: var(--adm-radius-md); margin-bottom: 22px;">
        <label for="status" class="adm-label" style="margin-bottom: 6px;">Status Publikasi Galeri</label>
        <select id="status" name="status" class="adm-select" style="background: #FFFFFF;">
          <option value="published" <?php echo (($editGallery['status'] ?? '') === 'published') ? 'selected' : ''; ?>>
            ✓ Dipublikasikan (Tampil Terbuka di Website)
          </option>
          <option value="draft" <?php echo (($editGallery['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>
            🔒 Draf Internal (Hanya Terlihat di Admin)
          </option>
        </select>
        <div style="color: var(--adm-text-subtle); font-size: 0.76rem; margin-top: 6px;">
          Galeri berstatus Draf tidak akan muncul pada halaman publik <code>gallery.php</code>.
        </div>
      </div>

      <button type="submit" class="adm-btn adm-btn-primary" id="saveGalleryBtn" style="width: 100%; height: 44px; font-size: 0.92rem; justify-content: center; font-weight: 700;">
        <?php echo $editGallery ? 'Simpan Perubahan Galeri Event' : 'Terbitkan Dokumentasi Event'; ?> &rarr;
      </button>
    </form>
  </div>

  <!-- Daftar Event Gallery -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div>
        <h3 class="adm-card-title">Daftar Gallery Event</h3>
        <div class="adm-card-subtitle">Total: <?php echo count($allGalleries); ?> Dokumentasi tersimpan</div>
      </div>
    </div>

    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th style="width: 44%;">Event &amp; Sampul</th>
            <th style="white-space: nowrap;">Tanggal</th>
            <th style="text-align: center; white-space: nowrap;">Foto</th>
            <th style="text-align: center; white-space: nowrap;">Status</th>
            <th style="text-align: right; white-space: nowrap;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allGalleries)): ?>
          <tr>
            <td colspan="5" style="text-align: center; color: var(--adm-text-muted); padding: 32px;">Belum ada dokumentasi event yang dibuat.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($allGalleries as $gal): 
            $count = count($gal['photos'] ?? []);
            $cover = !empty($gal['cover_photo']) ? $gal['cover_photo'] : ($gal['photos'][0] ?? 'assets/images/photo-01.jpg');
          ?>
          <tr>
            <td>
              <div style="display: flex; gap: 12px; align-items: center;">
                <img src="../<?php echo htmlspecialchars($cover); ?>" alt="Thumb" style="width: 52px; height: 38px; object-fit: cover; border-radius: var(--adm-radius-sm); border: 1px solid var(--adm-border); flex-shrink: 0;">
                <div style="min-width: 0;">
                  <strong style="color: var(--adm-forest); font-size: 0.86rem; display: block; line-height: 1.35;"><?php echo htmlspecialchars($gal['title']); ?></strong>
                  <span class="adm-badge adm-badge-category" style="margin-top: 4px; font-size: 0.68rem; padding: 1px 7px;"><?php echo htmlspecialchars($gal['category']); ?></span>
                </div>
              </div>
            </td>
            <td style="white-space: nowrap; font-size: 0.8rem; color: var(--adm-text-muted);">
              <?php echo format_date_id($gal['event_date'], false); ?>
            </td>
            <td style="text-align: center; white-space: nowrap;">
              <span class="adm-badge" style="background: var(--adm-green-light); color: var(--adm-green); border: 1px solid var(--adm-green-border); font-weight: 700; font-size: 0.72rem;">
                <?php echo $count; ?> / 10 foto
              </span>
            </td>
            <td style="text-align: center; white-space: nowrap;">
              <?php if (($gal['status'] ?? '') === 'published'): ?>
                <a href="gallery.php?toggle_status=<?php echo urlencode($gal['id']); ?>" class="adm-badge adm-badge-published" title="Klik untuk ubah ke Draf" style="text-decoration: none;">Publik</a>
              <?php else: ?>
                <a href="gallery.php?toggle_status=<?php echo urlencode($gal['id']); ?>" class="adm-badge adm-badge-draft" title="Klik untuk Publikasikan" style="text-decoration: none;">Draf</a>
              <?php endif; ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                <a href="gallery.php?edit=<?php echo urlencode($gal['id']); ?>" class="adm-btn adm-btn-secondary" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem; border-radius: var(--adm-radius-sm);" title="Edit Galeri">✏️</a>
                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumentasi event ini?');">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="gallery_id" value="<?php echo htmlspecialchars($gal['id']); ?>">
                  <button type="submit" class="adm-btn adm-btn-danger" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem; border-radius: var(--adm-radius-sm);" title="Hapus Galeri">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================================================
     CLIENT-SIDE JAVASCRIPT CONTROLLER: BATCH PHOTO UPLOADER (MAX 10)
     ======================================================================== -->
<script>
(function() {
  const MAX_PHOTOS = 10;
  const dropzone = document.getElementById('dropzone');
  const batchFileInput = document.getElementById('batchFileInput');
  const selectFilesBtn = document.getElementById('selectFilesBtn');
  const previewGrid = document.getElementById('photoPreviewGrid');
  const countDisplay = document.getElementById('photoCountDisplay');
  const limitAlert = document.getElementById('limitReachedAlert');
  const progressBox = document.getElementById('uploadProgressBox');
  const progressBar = document.getElementById('uploadProgressBar');
  const statusText = document.getElementById('uploadStatusText');
  const percentText = document.getElementById('uploadPercentText');

  function getPhotoCount() {
    return previewGrid.querySelectorAll('.photo-preview-item').length;
  }

  function updateCounter() {
    const total = getPhotoCount();
    if (countDisplay) {
      countDisplay.textContent = total + ' / ' + MAX_PHOTOS + ' foto';
    }
    if (total >= MAX_PHOTOS) {
      if (limitAlert) limitAlert.style.display = 'block';
      if (selectFilesBtn) {
        selectFilesBtn.style.opacity = '0.5';
        selectFilesBtn.style.pointerEvents = 'none';
        selectFilesBtn.textContent = 'Batas 10 Foto Tercapai';
      }
    } else {
      if (limitAlert) limitAlert.style.display = 'none';
      if (selectFilesBtn) {
        selectFilesBtn.style.opacity = '1';
        selectFilesBtn.style.pointerEvents = 'auto';
        selectFilesBtn.innerHTML = '📁 Pilih Berkas Foto dari Komputer';
      }
    }
    updateCoverHighlight();
  }

  window.updateCoverHighlight = function() {
    const items = previewGrid.querySelectorAll('.photo-preview-item');
    let hasChecked = false;
    items.forEach(function(item) {
      const radio = item.querySelector('input[type="radio"]');
      const label = item.querySelector('label');
      if (radio && radio.checked) {
        hasChecked = true;
        item.style.borderColor = 'var(--adm-primary)';
        if (label) label.style.color = 'var(--adm-primary)';
      } else {
        item.style.borderColor = 'var(--adm-border)';
        if (label) label.style.color = 'var(--adm-text)';
      }
    });

    // Otomatis sarankan foto pertama sebagai sampul jika belum ada yang terpilih
    if (!hasChecked && items.length > 0) {
      const firstRadio = items[0].querySelector('input[type="radio"]');
      const firstLabel = items[0].querySelector('label');
      if (firstRadio) {
        firstRadio.checked = true;
        items[0].style.borderColor = 'var(--adm-primary)';
        if (firstLabel) firstLabel.style.color = 'var(--adm-primary)';
      }
    }
  };

  window.removePhoto = function(btn) {
    const item = btn.closest('.photo-preview-item');
    if (item) {
      item.remove();
      updateCounter();
    }
  };

  window.movePhoto = function(btn, direction) {
    const item = btn.closest('.photo-preview-item');
    if (!item) return;
    if (direction === -1 && item.previousElementSibling) {
      item.parentNode.insertBefore(item, item.previousElementSibling);
    } else if (direction === 1 && item.nextElementSibling) {
      item.parentNode.insertBefore(item.nextElementSibling, item);
    }
    updateCoverHighlight();
  };

  function addPhotoPreview(relPath, thumbPath) {
    const total = getPhotoCount();
    if (total >= MAX_PHOTOS) return;

    const div = document.createElement('div');
    div.className = 'photo-preview-item';
    div.setAttribute('data-path', relPath);
    div.style.cssText = 'position: relative; border: 2px solid var(--adm-border); border-radius: var(--adm-radius-md); overflow: hidden; background: #FFFFFF; padding: 4px; box-shadow: var(--adm-shadow-sm); transition: border-color 0.2s ease;';

    div.innerHTML = `
      <input type="hidden" name="photos[]" value="${relPath}">
      <img src="../${thumbPath}" alt="Preview" style="width: 100%; height: 76px; object-fit: cover; border-radius: 4px;">
      <div style="padding: 6px 4px 4px; text-align: center; font-size: 0.72rem;">
        <label style="cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; font-weight: 700; color: var(--adm-text);">
          <input type="radio" name="selected_cover" value="${relPath}" onchange="updateCoverHighlight();">
          Sampul
        </label>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 0 2px;">
          <button type="button" onclick="movePhoto(this, -1)" title="Geser ke kiri" class="adm-btn adm-btn-secondary" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 4px; height: 22px;">&larr;</button>
          <button type="button" onclick="removePhoto(this)" title="Hapus foto" style="background: none; border: none; color: var(--adm-red); font-weight: bold; cursor: pointer; font-size: 0.95rem; padding: 0 4px; line-height: 1;">&times;</button>
          <button type="button" onclick="movePhoto(this, 1)" title="Geser ke kanan" class="adm-btn adm-btn-secondary" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 4px; height: 22px;">&rarr;</button>
        </div>
      </div>
    `;

    previewGrid.appendChild(div);
    updateCounter();
  }

  // Handle files batch
  async function handleFiles(files) {
    if (!files || files.length === 0) return;

    let availableSlots = MAX_PHOTOS - getPhotoCount();
    if (availableSlots <= 0) {
      alert('Batas kuota maksimal 10 foto untuk event ini telah tercapai.');
      return;
    }

    if (files.length > availableSlots) {
      alert(`Anda memilih ${files.length} foto, namun hanya ${availableSlots} kuota tersisa. Hanya ${availableSlots} foto pertama yang akan diproses.`);
    }

    const filesToUpload = Array.from(files).slice(0, availableSlots);
    if (progressBox) progressBox.style.display = 'block';

    for (let i = 0; i < filesToUpload.length; i++) {
      const file = filesToUpload[i];
      const progressPercent = Math.round(((i) / filesToUpload.length) * 100);
      if (progressBar) progressBar.style.width = progressPercent + '%';
      if (percentText) percentText.textContent = progressPercent + '%';
      if (statusText) statusText.textContent = `Mengunggah foto ${i + 1} dari ${filesToUpload.length}: ${file.name}`;

      const formData = new FormData();
      formData.append('photo', file);
      formData.append('current_count', getPhotoCount());

      try {
        const response = await fetch('ajax_upload_gallery.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        if (result.success) {
          addPhotoPreview(result.path, result.thumb);
        } else {
          alert(`Gagal mengunggah ${file.name}: ${result.error}`);
        }
      } catch (err) {
        alert(`Kesalahan jaringan saat mengunggah ${file.name}.`);
      }
    }

    if (progressBar) progressBar.style.width = '100%';
    if (percentText) percentText.textContent = '100%';
    if (statusText) statusText.textContent = 'Semua berkas selesai diproses!';
    setTimeout(() => {
      if (progressBox) progressBox.style.display = 'none';
      if (progressBar) progressBar.style.width = '0%';
    }, 1200);

    // Reset file input
    if (batchFileInput) batchFileInput.value = '';
  }

  // Drag & Drop Listeners
  if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.style.borderColor = '#294522';
        dropzone.style.background = '#F0EADF';
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.style.borderColor = '#A84A29';
        dropzone.style.background = '#FAF6F0';
      }, false);
    });

    dropzone.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      handleFiles(files);
    }, false);
  }

  if (batchFileInput) {
    batchFileInput.addEventListener('change', function() {
      handleFiles(this.files);
    });
  }

  // Initial Counter sync
  updateCounter();
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
