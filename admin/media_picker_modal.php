<?php
/**
 * OMAH BELUMBUNGAN - Reusable Media Picker Modal
 * Shared component for selecting media and uploading images across Admin CMS
 */
$allPickerMedia = get_all_media_files();
?>
<!-- Media Picker Modal Component -->
<div class="adm-modal-overlay" id="admMediaPickerModal" style="display: none; position: fixed; inset: 0; background: rgba(20, 32, 18, 0.65); backdrop-filter: blur(4px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
  <div class="adm-modal-card" style="background: var(--adm-surface); border-radius: var(--adm-radius-lg); border: 1px solid var(--adm-border); box-shadow: var(--adm-shadow-lg); width: 100%; max-width: 860px; max-height: 88vh; display: flex; flex-direction: column; overflow: hidden; animation: admModalIn 0.22s ease-out;">
    
    <!-- Modal Header -->
    <div style="padding: 18px 24px; border-bottom: 1px solid var(--adm-border); display: flex; align-items: center; justify-content: space-between; background: var(--adm-surface);">
      <div>
        <h3 style="font-family: var(--adm-font-serif); font-size: 1.25rem; font-weight: 700; color: var(--adm-forest); margin-bottom: 2px;">
          🖼️ Pilih Media dari Pustaka
        </h3>
        <p style="font-size: 0.8rem; color: var(--adm-text-muted); margin: 0;">
          Pilih salah satu berkas yang sudah ada di pustaka media website.
        </p>
      </div>
      <button type="button" onclick="closeMediaPickerModal()" style="background: none; border: none; font-size: 1.6rem; color: var(--adm-text-muted); cursor: pointer; padding: 4px 8px; line-height: 1; border-radius: 4px;" title="Tutup Modal">&times;</button>
    </div>

    <!-- Filter & Search Toolbar -->
    <div style="padding: 14px 24px; background: var(--adm-surface-alt); border-bottom: 1px solid var(--adm-border); display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
      <div style="display: flex; gap: 6px; flex-wrap: wrap;" id="mediaCategoryFilter">
        <button type="button" class="adm-tab-btn active" data-cat="all" onclick="filterMediaCategory('all', this)" style="padding: 5px 12px; font-size: 0.78rem;">
          Semua (<?php echo count($allPickerMedia); ?>)
        </button>
        <button type="button" class="adm-tab-btn" data-cat="Aset Utama" onclick="filterMediaCategory('Aset Utama', this)" style="padding: 5px 12px; font-size: 0.78rem;">
          Aset Utama
        </button>
        <button type="button" class="adm-tab-btn" data-cat="Galeri Event" onclick="filterMediaCategory('Galeri Event', this)" style="padding: 5px 12px; font-size: 0.78rem;">
          Galeri Event
        </button>
        <button type="button" class="adm-tab-btn" data-cat="Portofolio" onclick="filterMediaCategory('Portofolio', this)" style="padding: 5px 12px; font-size: 0.78rem;">
          Portofolio
        </button>
        <button type="button" class="adm-tab-btn" data-cat="Event & Edukasi" onclick="filterMediaCategory('Event & Edukasi', this)" style="padding: 5px 12px; font-size: 0.78rem;">
          Event &amp; Edukasi
        </button>
      </div>

      <div style="flex-grow: 1; max-width: 260px;">
        <input type="text" id="mediaSearchInput" placeholder="Cari nama berkas..." class="adm-input" style="padding: 6px 12px; font-size: 0.82rem;" oninput="filterMediaSearch(this.value)">
      </div>
    </div>

    <!-- Media Thumbnails Grid -->
    <div style="padding: 20px 24px; overflow-y: auto; flex-grow: 1; max-height: calc(88vh - 190px);" id="mediaGridScroll">
      <div id="mediaGridContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 14px;">
        <?php foreach ($allPickerMedia as $m): 
          $thumbUrl = '../' . htmlspecialchars($m['rel_path']);
        ?>
        <div class="media-picker-card" 
             data-category="<?php echo htmlspecialchars($m['category']); ?>" 
             data-filename="<?php echo htmlspecialchars(strtolower($m['filename'])); ?>"
             data-path="<?php echo htmlspecialchars($m['rel_path']); ?>"
             onclick="selectMediaItem('<?php echo htmlspecialchars($m['rel_path']); ?>')"
             style="border: 1px solid var(--adm-border); border-radius: var(--adm-radius-md); overflow: hidden; background: #FFFFFF; cursor: pointer; transition: all 0.18s ease; display: flex; flex-direction: column;"
             onmouseover="this.style.borderColor='var(--adm-primary)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(169, 71, 43, 0.15)';"
             onmouseout="this.style.borderColor='var(--adm-border)'; this.style.transform='none'; this.style.boxShadow='none';">
          
          <div style="position: relative; width: 100%; height: 95px; background: #FAF7F0; overflow: hidden;">
            <img src="<?php echo $thumbUrl; ?>" alt="<?php echo htmlspecialchars($m['filename']); ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" onerror="this.src='../assets/images/photo-01.jpg';">
            <span style="position: absolute; bottom: 4px; right: 4px; background: rgba(20, 32, 18, 0.75); color: #FFF; font-size: 0.65rem; padding: 2px 5px; border-radius: 3px; font-weight: 600;">
              <?php echo strtoupper($m['ext']); ?>
            </span>
          </div>

          <div style="padding: 8px 10px; font-size: 0.72rem; display: flex; flex-direction: column; gap: 2px;">
            <div style="font-weight: 700; color: var(--adm-forest); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($m['filename']); ?>">
              <?php echo htmlspecialchars($m['filename']); ?>
            </div>
            <div style="color: var(--adm-text-subtle); display: flex; justify-content: space-between; font-size: 0.68rem;">
              <span><?php echo htmlspecialchars($m['category']); ?></span>
              <span><?php echo $m['size_formatted']; ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div id="mediaEmptyNotice" style="display: none; text-align: center; padding: 40px 20px; color: var(--adm-text-muted);">
        <div style="font-size: 2rem; margin-bottom: 8px;">🔍</div>
        <p style="font-size: 0.9rem; margin: 0;">Tidak ada berkas media yang cocok dengan filter atau kata kunci pencarian.</p>
      </div>
    </div>

    <!-- Modal Footer -->
    <div style="padding: 14px 24px; border-top: 1px solid var(--adm-border); background: var(--adm-surface-alt); display: flex; justify-content: space-between; align-items: center;">
      <span style="font-size: 0.78rem; color: var(--adm-text-muted);">
        Klik salah satu foto untuk langsung menggunakannya.
      </span>
      <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" onclick="closeMediaPickerModal()">
        Batal / Tutup
      </button>
    </div>

  </div>
</div>

<style>
@keyframes admModalIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
</style>

<script>
(function() {
  let activeTargetInputId = null;
  let activePreviewThumbId = null;
  let activePreviewPathId = null;
  let activeCurrentCat = 'all';
  let activeSearchQuery = '';

  const modal = document.getElementById('admMediaPickerModal');
  const searchInput = document.getElementById('mediaSearchInput');
  const mediaGrid = document.getElementById('mediaGridContainer');
  const emptyNotice = document.getElementById('mediaEmptyNotice');

  window.openMediaPickerModal = function(targetInputId, previewThumbId, previewPathId) {
    activeTargetInputId = targetInputId || 'image';
    activePreviewThumbId = previewThumbId || (activeTargetInputId + 'PreviewThumb');
    activePreviewPathId = previewPathId || (activeTargetInputId + 'PreviewPath');

    if (modal) {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      if (searchInput) {
        searchInput.value = '';
        activeSearchQuery = '';
        applyMediaFilters();
        setTimeout(() => searchInput.focus(), 150);
      }
    }
  };

  window.closeMediaPickerModal = function() {
    if (modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    }
  };

  // Close modal when clicking backdrop
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeMediaPickerModal();
      }
    });
  }

  window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
      closeMediaPickerModal();
    }
  });

  window.selectMediaItem = function(relPath) {
    if (activeTargetInputId) {
      const targetInput = document.getElementById(activeTargetInputId);
      if (targetInput) {
        targetInput.value = relPath;
        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    if (activePreviewThumbId) {
      const thumb = document.getElementById(activePreviewThumbId);
      if (thumb) {
        thumb.src = '../' + relPath;
      }
    }

    if (activePreviewPathId) {
      const pathLabel = document.getElementById(activePreviewPathId);
      if (pathLabel) {
        pathLabel.textContent = relPath;
      }
    }

    closeMediaPickerModal();
  };

  window.filterMediaCategory = function(cat, btn) {
    activeCurrentCat = cat;
    const buttons = document.querySelectorAll('#mediaCategoryFilter .adm-tab-btn');
    buttons.forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    applyMediaFilters();
  };

  window.filterMediaSearch = function(query) {
    activeSearchQuery = (query || '').toLowerCase().trim();
    applyMediaFilters();
  };

  function applyMediaFilters() {
    const cards = document.querySelectorAll('.media-picker-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const itemCat = card.getAttribute('data-category');
      const itemFile = card.getAttribute('data-filename');

      const matchesCat = (activeCurrentCat === 'all' || itemCat === activeCurrentCat);
      const matchesSearch = (!activeSearchQuery || itemFile.includes(activeSearchQuery));

      if (matchesCat && matchesSearch) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyNotice) {
      emptyNotice.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
  }

  // General Single Image Upload Handler
  window.handleSingleImageUpload = async function(fileInput, targetInputId, folder, previewThumbId, previewPathId, statusId) {
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
    const file = fileInput.files[0];

    targetInputId = targetInputId || 'image';
    previewThumbId = previewThumbId || (targetInputId + 'PreviewThumb');
    previewPathId = previewPathId || (targetInputId + 'PreviewPath');
    statusId = statusId || (targetInputId + 'UploadStatus');
    folder = folder || 'events';

    const statusElem = document.getElementById(statusId);
    if (statusElem) {
      statusElem.textContent = `Mengunggah "${file.name}"...`;
      statusElem.style.color = 'var(--adm-primary)';
    }

    const formData = new FormData();
    formData.append('image', file);
    formData.append('folder', folder);
    formData.append('csrf_token', '<?php echo csrf_token(); ?>');

    try {
      const response = await fetch('ajax_upload_media.php', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();

      if (result.success) {
        const inputElem = document.getElementById(targetInputId);
        if (inputElem) {
          inputElem.value = result.path;
          inputElem.dispatchEvent(new Event('input', { bubbles: true }));
          inputElem.dispatchEvent(new Event('change', { bubbles: true }));
        }

        const thumbElem = document.getElementById(previewThumbId);
        if (thumbElem) {
          thumbElem.src = '../' + result.path;
        }

        const pathElem = document.getElementById(previewPathId);
        if (pathElem) {
          pathElem.textContent = result.path;
        }

        if (statusElem) {
          statusElem.textContent = `✅ Berhasil diunggah: ${result.path}`;
          statusElem.style.color = 'var(--adm-green)';
        }
      } else {
        alert('Gagal mengunggah gambar: ' + (result.error || 'Terjadi kesalahan'));
        if (statusElem) {
          statusElem.textContent = '❌ ' + (result.error || 'Gagal mengunggah');
          statusElem.style.color = 'var(--adm-red)';
        }
      }
    } catch (err) {
      alert('Kesalahan jaringan saat mengunggah gambar.');
      if (statusElem) {
        statusElem.textContent = '❌ Gagal mengunggah (kesalahan jaringan)';
        statusElem.style.color = 'var(--adm-red)';
      }
    } finally {
      fileInput.value = '';
    }
  };

  // Live input sync helper
  window.updateImagePreview = function(val, thumbId, pathId) {
    thumbId = thumbId || 'imagePreviewThumb';
    pathId = pathId || 'imagePreviewPath';

    const cleanPath = (val || '').trim();
    const thumbElem = document.getElementById(thumbId);
    const pathElem = document.getElementById(pathId);

    if (thumbElem) {
      thumbElem.src = cleanPath ? ('../' + cleanPath) : '../assets/images/photo-01.jpg';
    }
    if (pathElem) {
      pathElem.textContent = cleanPath || 'Belum ada foto yang dipilih';
    }
  };
})();
</script>
