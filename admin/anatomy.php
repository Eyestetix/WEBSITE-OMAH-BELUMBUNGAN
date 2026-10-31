<?php
/**
 * OMAH BELUMBUNGAN - Manajemen Anatomi Arsitektur Rumah Bali
 * Full Catalogue Editor, Display Order, Philosophy, Materials & Detail Links
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$adminTitle = 'Kelola Anatomi Bangunan';
require_once __DIR__ . '/header.php';

$anatomyList = get_anatomy();
$editItem = null;

if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($anatomyList as $item) {
        if ($item['id'] === $editId) {
            $editItem = $item;
            break;
        }
    }
}

// Tangani toggle status publikasi via GET
if (isset($_GET['toggle_status'])) {
    $toggleId = $_GET['toggle_status'];
    foreach ($anatomyList as &$item) {
        if ($item['id'] === $toggleId) {
            $currentStatus = $item['status'] ?? 'published';
            $item['status'] = ($currentStatus === 'published') ? 'draft' : 'published';
            save_json_data('anatomy.json', $anatomyList);
            set_flash('success', "Status elemen '{$item['name']}' diubah menjadi " . strtoupper($item['status']));
            break;
        }
    }
    header('Location: anatomy.php');
    exit;
}

// Tangani POST (Simpan / Hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $delId = $_POST['element_id'];
        $anatomyList = array_values(array_filter($anatomyList, function($item) use ($delId) {
            return $item['id'] !== $delId;
        }));
        save_json_data('anatomy.json', $anatomyList);
        set_flash('success', "Elemen anatomi berhasil dihapus.");
        header('Location: anatomy.php');
        exit;
    }

    if ($action === 'save') {
        $id = sanitize($_POST['id'] ?? '');
        $name = sanitize($_POST['name'] ?? '');
        $shortName = sanitize($_POST['short_name'] ?? $name);
        $slug = sanitize($_POST['slug'] ?? $id);
        $zone = sanitize($_POST['zone'] ?? '');
        $orientation = sanitize($_POST['orientation'] ?? '');
        $philosophy = sanitize($_POST['philosophy'] ?? '');
        $astaKosalaKosali = sanitize($_POST['asta_kosala_kosali'] ?? '');
        $triHitaKarana = sanitize($_POST['tri_hita_karana'] ?? '');
        $function = sanitize($_POST['function'] ?? '');
        $materials = sanitize($_POST['materials'] ?? '');
        $shortDesc = sanitize($_POST['short_desc'] ?? '');
        $detailArticle = sanitize($_POST['detail_article'] ?? '');
        $image = sanitize($_POST['image'] ?? 'assets/images/photo-01.jpg');
        $sortOrder = (int)($_POST['sort_order'] ?? 1);
        $status = sanitize($_POST['status'] ?? 'published');

        if (!empty($id) && !empty($name)) {
            $itemData = [
                'id' => $id,
                'name' => $name,
                'short_name' => $shortName,
                'slug' => $slug,
                'sort_order' => $sortOrder,
                'short_desc' => $shortDesc,
                'zone' => $zone,
                'orientation' => $orientation,
                'philosophy' => $philosophy,
                'asta_kosala_kosali' => $astaKosalaKosali,
                'tri_hita_karana' => $triHitaKarana,
                'function' => $function,
                'materials' => $materials,
                'detail_article' => $detailArticle,
                'image' => $image,
                'status' => $status
            ];

            $found = false;
            foreach ($anatomyList as &$existing) {
                if ($existing['id'] === $id) {
                    $existing = array_merge($existing, $itemData);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $anatomyList[] = $itemData;
            }

            // Urutkan kembali berdasarkan sort_order
            usort($anatomyList, function($a, $b) {
                return ($a['sort_order'] ?? 99) <=> ($b['sort_order'] ?? 99);
            });

            save_json_data('anatomy.json', $anatomyList);
            set_flash('success', "Elemen anatomi '<strong>{$name}</strong>' berhasil diperbarui.");
            header('Location: anatomy.php');
            exit;
        }
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
  <div>
    <h2 style="font-family: var(--adm-font-serif); font-size: 1.4rem; color: var(--adm-forest); margin-bottom: 2px;">
      Katalog Anatomi &amp; Arsitektur Pekarangan Bali
    </h2>
    <span style="font-size: 0.85rem; color: var(--adm-text-muted);">
      Mengelola narasi tata ruang, zonasi mandala, filosofi Asta Kosala Kosali, dan dokumentasi foto.
    </span>
  </div>

  <div style="display: flex; gap: 10px;">
    <a href="../about.php" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn-secondary adm-btn-sm">
      <?php echo ob_icon('external-link', ['size' => 14, 'raw' => true]); ?> Lihat Halaman Anatomi Publik &nearr;
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 28px; align-items: start;">
  <!-- FORM EDITOR -->
  <div class="adm-card">
    <div class="adm-card-header">
      <h3 class="adm-card-title">
        <span><?php echo $editItem ? '✏️ Edit Elemen: ' . htmlspecialchars($editItem['name']) : '+ Tambah / Perbarui Elemen Anatomi'; ?></span>
      </h3>
      <?php if ($editItem): ?>
        <div style="display: flex; gap: 8px;">
          <a href="../anatomy.php?id=<?php echo urlencode($editItem['slug'] ?? $editItem['id']); ?>" target="_blank" class="adm-btn adm-btn-forest adm-btn-sm" title="Pratinjau Halaman Detail">
            Pratinjau Detail &nearr;
          </a>
          <a href="anatomy.php" class="adm-btn adm-btn-secondary adm-btn-sm">Batal</a>
        </div>
      <?php endif; ?>
    </div>

    <form action="anatomy.php" method="POST">
      <input type="hidden" name="action" value="save">

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="id" class="adm-label">ID Elemen *</label>
          <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($editItem['id'] ?? ''); ?>" required placeholder="Contoh: angkul_angkul" class="adm-input" <?php echo $editItem ? 'readonly style="background:#FAF7F0;"' : ''; ?>>
          <span style="font-size: 0.72rem; color: var(--adm-text-subtle);">ID unik di sistem (#id).</span>
        </div>
        <div class="adm-form-group">
          <label for="slug" class="adm-label">Slug URL Publik *</label>
          <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($editItem['slug'] ?? ($editItem ? str_replace('_', '-', $editItem['id']) : '')); ?>" required placeholder="Contoh: angkul-angkul" class="adm-input">
          <span style="font-size: 0.72rem; color: var(--adm-text-subtle);">URL: anatomy.php?id=slug</span>
        </div>
        <div class="adm-form-group" style="max-width: 100px;">
          <label for="sort_order" class="adm-label">Urutan (#) *</label>
          <input type="number" id="sort_order" name="sort_order" value="<?php echo (int)($editItem['sort_order'] ?? (count($anatomyList) + 1)); ?>" required class="adm-input" min="1" max="99">
        </div>
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="name" class="adm-label">Nama Elemen Bangunan *</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>" required placeholder="Contoh: Angkul-angkul" class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="zone" class="adm-label">Zona / Kategori Mandala *</label>
          <input type="text" id="zone" name="zone" value="<?php echo htmlspecialchars($editItem['zone'] ?? 'Utama / Gerbang Utama'); ?>" required placeholder="Contoh: Utama Mandala / Natah" class="adm-input">
        </div>
      </div>

      <div class="adm-form-group">
        <label for="orientation" class="adm-label">Orientasi &amp; Letak Spasial *</label>
        <input type="text" id="orientation" name="orientation" value="<?php echo htmlspecialchars($editItem['orientation'] ?? ''); ?>" required placeholder="Contoh: Akses Masuk Depan / Sisi Kaja" class="adm-input">
      </div>

      <div class="adm-form-group">
        <label for="function" class="adm-label">Fungsi Utama *</label>
        <textarea id="function" name="function" rows="2" class="adm-textarea" required placeholder="Fungsi sirkulasi, ritual, atau kegiatan keluarga..."><?php echo htmlspecialchars($editItem['function'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="materials" class="adm-label">Material &amp; Karakter Fisik *</label>
        <input type="text" id="materials" name="materials" value="<?php echo htmlspecialchars($editItem['materials'] ?? ''); ?>" required placeholder="Bata merah gosok, batu paras kelating, kayu jati..." class="adm-input">
      </div>

      <div class="adm-form-group">
        <label for="philosophy" class="adm-label">Makna Filosofis *</label>
        <textarea id="philosophy" name="philosophy" rows="3" class="adm-textarea" required placeholder="Filosofi asta kosala kosali dan makna sakral..."><?php echo htmlspecialchars($editItem['philosophy'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="asta_kosala_kosali" class="adm-label">Kaidah Asta Kosala Kosali (Tata Ukur &amp; Proporsi Raga) <span class="adm-label-hint">Kultural</span></label>
        <textarea id="asta_kosala_kosali" name="asta_kosala_kosali" rows="2" class="adm-textarea" placeholder="Pedoman ukuran depa, rai, musti, atau elevasi bebataran..."><?php echo htmlspecialchars($editItem['asta_kosala_kosali'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="tri_hita_karana" class="adm-label">Hubungan dengan Tri Hita Karana <span class="adm-label-hint">Parahyangan / Pawongan / Palemahan</span></label>
        <input type="text" id="tri_hita_karana" name="tri_hita_karana" value="<?php echo htmlspecialchars($editItem['tri_hita_karana'] ?? ''); ?>" placeholder="Contoh: Pawongan &amp; Palemahan: Gerbang penyambutan..." class="adm-input">
      </div>

      <div class="adm-form-group">
        <label for="short_desc" class="adm-label">Ringkasan Singkat (Untuk Card Katalog)</label>
        <input type="text" id="short_desc" name="short_desc" value="<?php echo htmlspecialchars($editItem['short_desc'] ?? ''); ?>" placeholder="Ringkasan 1 kalimat yang tampil di katalog..." class="adm-input">
      </div>

      <div class="adm-form-group">
        <label for="image" class="adm-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span>Foto Utama Elemen (Path) *</span>
          <a href="media.php" target="_blank" style="font-size: 0.74rem; color: var(--adm-primary);">Buka Media Library &nearr;</a>
        </label>
        
        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 10px;">
          <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($editItem['image'] ?? 'assets/images/photo-01.jpg'); ?>" class="adm-input" oninput="updateImagePreview(this.value, 'anatomyImageThumb', 'anatomyImagePath')">
          
          <!-- Tombol Upload Gambar Langsung -->
          <label for="anatomyImageUploadFile" class="adm-btn adm-btn-primary adm-btn-sm" style="cursor: pointer; flex-shrink: 0; height: 38px;" title="Unggah gambar baru langsung dari perangkat">
            📤 Upload Gambar
          </label>
          <input type="file" id="anatomyImageUploadFile" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="handleSingleImageUpload(this, 'image', 'anatomy', 'anatomyImageThumb', 'anatomyImagePath', 'anatomyImageStatus')">
          
          <!-- Tombol Pilih dari Pustaka Media -->
          <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" style="flex-shrink: 0; height: 38px;" onclick="openMediaPickerModal('image', 'anatomyImageThumb', 'anatomyImagePath')" title="Pilih foto dari pustaka media">
            🖼️ Pilih Media
          </button>
        </div>

        <!-- Pratinjau Visual Gambar Terpilih -->
        <div style="display: flex; align-items: center; gap: 14px; background: var(--adm-surface-alt); border: 1px solid var(--adm-border); border-radius: var(--adm-radius-md); padding: 10px 14px;">
          <img id="anatomyImageThumb" src="../<?php echo htmlspecialchars($editItem['image'] ?? 'assets/images/photo-01.jpg'); ?>" alt="Pratinjau Elemen" style="width: 72px; height: 50px; object-fit: cover; border-radius: var(--adm-radius-sm); border: 1px solid var(--adm-border); background: #FFFFFF; flex-shrink: 0;" onerror="this.src='../assets/images/photo-01.jpg';">
          <div style="flex-grow: 1; min-width: 0;">
            <div id="anatomyImagePath" style="font-size: 0.82rem; font-weight: 700; color: var(--adm-forest); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              <?php echo htmlspecialchars($editItem['image'] ?? 'assets/images/photo-01.jpg'); ?>
            </div>
            <div id="anatomyImageStatus" style="font-size: 0.74rem; color: var(--adm-text-subtle); margin-top: 2px;">
              Foto yang tampil di katalog anatomi dan galeri detail bangunan
            </div>
          </div>
        </div>
      </div>

      <div class="adm-form-group">
        <label for="detail_article" class="adm-label">Artikel Ulasan Mendalam <span class="adm-label-hint">Opsional</span></label>
        <textarea id="detail_article" name="detail_article" rows="4" class="adm-textarea" placeholder="Pembahasan mendalam seputar sejarah, kaidah asta kosala kosali, dan restorasi..."><?php echo htmlspecialchars($editItem['detail_article'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="status" class="adm-label">Status Publikasi</label>
        <select id="status" name="status" class="adm-select">
          <option value="published" <?php echo (($editItem['status'] ?? 'published') === 'published') ? 'selected' : ''; ?>>Terpublikasi (Aktif di Website &amp; Detail Page)</option>
          <option value="draft" <?php echo (($editItem['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft (Sembunyikan Sementara)</option>
        </select>
      </div>

      <button type="submit" class="adm-btn adm-btn-primary" style="width: 100%;">
        <?php echo $editItem ? 'Simpan Perubahan Elemen' : 'Tambah Elemen Anatomi'; ?> &rarr;
      </button>
    </form>
  </div>

  <!-- DAFTAR KATALOG ELEMEN -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div>
        <h3 class="adm-card-title">
          <span>Daftar Elemen Terdaftar (<?php echo count($anatomyList); ?>)</span>
        </h3>
        <div class="adm-card-subtitle">Urutan 1–9 mencerminkan alur sirkulasi pekarangan</div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 12px;">
      <?php foreach ($anatomyList as $item): 
        $isPub = (($item['status'] ?? 'published') === 'published');
        $imgSrc = !empty($item['image']) ? '../' . ltrim($item['image'], '/') : '../assets/images/photo-01.jpg';
      ?>
      <div style="background: var(--adm-surface-alt); border: 1px solid var(--adm-border); border-radius: var(--adm-radius-md); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
          <div style="width: 54px; height: 54px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: var(--adm-border);">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div style="min-width: 0;">
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px;">
              <span class="adm-badge adm-badge-category" style="font-size: 0.68rem; padding: 1px 6px;">
                #0<?php echo (int)($item['sort_order'] ?? 1); ?>
              </span>
              <strong style="color: var(--adm-forest); font-size: 0.96rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo htmlspecialchars($item['name']); ?>
              </strong>
            </div>
            <div style="font-size: 0.78rem; color: var(--adm-text-subtle);">
              <?php echo htmlspecialchars($item['zone'] ?? '-'); ?> &bull; <?php echo htmlspecialchars($item['orientation'] ?? '-'); ?>
            </div>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
          <a href="../anatomy.php?id=<?php echo urlencode($item['slug'] ?? $item['id']); ?>" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm" title="Lihat Halaman Detail Publik">
            Detail &nearr;
          </a>
          <a href="anatomy.php?edit=<?php echo urlencode($item['id']); ?>" class="adm-btn adm-btn-forest adm-btn-sm" title="Edit Elemen">
            Edit
          </a>
          <a href="anatomy.php?toggle_status=<?php echo urlencode($item['id']); ?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Ubah Status">
            <?php echo $isPub ? 'Publik' : 'Draft'; ?>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
