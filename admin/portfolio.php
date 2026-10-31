<?php
/**
 * OMAH BELUMBUNGAN - Admin CMS Portfolio Proyek
 * Mengelola karya arsitektur, restorasi, dan dokumentasi budaya
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$adminTitle = 'Kelola Portofolio Proyek';
require_once __DIR__ . '/header.php';

$allProjects = get_portfolio();
$editProject = null;

// Tangani edit melalui parameter GET
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($allProjects as $p) {
        if ($p['id'] === $editId) {
            $editProject = $p;
            break;
        }
    }
}

// Tangani toggle status publish/draft via GET
if (isset($_GET['toggle_status'])) {
    $toggleId = $_GET['toggle_status'];
    foreach ($allProjects as &$p) {
        if ($p['id'] === $toggleId) {
            $p['status'] = ($p['status'] === 'published') ? 'draft' : 'published';
            save_json_data('portfolio.json', $allProjects);
            set_flash('success', "Status proyek '{$p['title']}' diubah menjadi <strong>" . strtoupper($p['status']) . "</strong>.");
            break;
        }
    }
    header('Location: portfolio.php');
    exit;
}

// Tangani aksi POST (Simpan / Hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $action = $_POST['action'] ?? '';

    // 1. Aksi Hapus Proyek
    if ($action === 'delete') {
        $delId = $_POST['project_id'];
        $allProjects = array_values(array_filter($allProjects, function($p) use ($delId) {
            return $p['id'] !== $delId;
        }));
        save_json_data('portfolio.json', $allProjects);
        set_flash('success', "Proyek berhasil dihapus.");
        header('Location: portfolio.php');
        exit;
    }

    // 2. Aksi Simpan / Perbarui Proyek
    if ($action === 'save') {
        $projId = $_POST['project_id'] ?: ('proj_' . time());
        $title = sanitize($_POST['title'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $location = sanitize($_POST['location'] ?? 'Sibang Kaja, Abiansemal, Badung');
        $year = sanitize($_POST['year'] ?? date('Y'));
        $leadArchitect = sanitize($_POST['lead_architect'] ?? '');
        $materials = sanitize($_POST['materials'] ?? '');
        $shortDesc = sanitize($_POST['short_description'] ?? '');
        $fullDesc = sanitize($_POST['full_description'] ?? '');
        $status = sanitize($_POST['status'] ?? 'published');
        $isFeatured = isset($_POST['is_featured']);

        // Kelola foto lama / yang dipertahankan
        $existingPhotos = isset($_POST['existing_photos']) ? (array)$_POST['existing_photos'] : [];
        $coverPhoto = sanitize($_POST['selected_cover'] ?? '');

        // Upload Cover Baru (Jika Ada)
        if (isset($_FILES['cover_upload']) && $_FILES['cover_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = upload_image_secure($_FILES['cover_upload']['tmp_name'], $_FILES['cover_upload']['name'], 'portfolio');
            if ($uploadRes['success']) {
                $coverPhoto = $uploadRes['path'];
                if (!in_array($coverPhoto, $existingPhotos, true)) {
                    array_unshift($existingPhotos, $coverPhoto);
                }
            } else {
                set_flash('error', 'Gagal mengunggah foto sampul: ' . $uploadRes['error']);
            }
        }

        // Upload Foto Tambahan (Multi-file)
        if (isset($_FILES['supporting_photos']) && is_array($_FILES['supporting_photos']['name'])) {
            $fileCount = count($_FILES['supporting_photos']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['supporting_photos']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpPath = $_FILES['supporting_photos']['tmp_name'][$i];
                    $name = $_FILES['supporting_photos']['name'][$i];
                    $res = upload_image_secure($tmpPath, $name, 'portfolio');
                    if ($res['success']) {
                        $existingPhotos[] = $res['path'];
                    }
                }
            }
        }

        // Default cover jika belum terpilih
        if (empty($coverPhoto) && !empty($existingPhotos)) {
            $coverPhoto = $existingPhotos[0];
        }

        $projectData = [
            'id' => $projId,
            'title' => $title,
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))),
            'category' => $category,
            'location' => $location,
            'year' => $year,
            'cover_photo' => $coverPhoto,
            'short_description' => $shortDesc,
            'full_description' => $fullDesc,
            'lead_architect' => $leadArchitect,
            'materials' => $materials,
            'photos' => array_values(array_unique($existingPhotos)),
            'is_featured' => $isFeatured,
            'status' => $status,
            'created_at' => $editProject['created_at'] ?? date('Y-m-d H:i:s')
        ];

        // Jika featured, nonaktifkan featured pada proyek lain
        if ($isFeatured) {
            foreach ($allProjects as &$other) {
                if ($other['id'] !== $projId) {
                    $other['is_featured'] = false;
                }
            }
        }

        $found = false;
        foreach ($allProjects as &$existing) {
            if ($existing['id'] === $projId) {
                $existing = $projectData;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $allProjects[] = $projectData;
        }

        save_json_data('portfolio.json', $allProjects);
        set_flash('success', "Proyek '<strong>{$title}</strong>' berhasil disimpan.");
        header('Location: portfolio.php');
        exit;
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
  <div>
    <h2 style="font-family: var(--adm-font-serif); font-size: 1.4rem; color: var(--adm-forest); margin-bottom: 2px;">
      Arsip Karya &amp; Restorasi Arsitektur
    </h2>
    <span style="font-size: 0.85rem; color: var(--adm-text-muted);">
      Dokumentasi terukur karya pemugaran bale, tata spasial pekarangan, dan konservasi struktur tradisional.
    </span>
  </div>

  <div style="display: flex; gap: 10px;">
    <a href="../portfolio.php" target="_blank" rel="noopener noreferrer" class="adm-btn adm-btn-secondary adm-btn-sm">
      <?php echo ob_icon('external-link', ['size' => 14, 'raw' => true]); ?> Lihat Portofolio Publik &nearr;
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 28px; align-items: start;">
  <!-- FORMULIR PROYEK -->
  <div class="adm-card">
    <div class="adm-card-header">
      <h3 class="adm-card-title">
        <span><?php echo $editProject ? '✏️ Edit Proyek: ' . htmlspecialchars($editProject['title']) : '+ Tambah Proyek Portofolio Baru'; ?></span>
      </h3>
      <?php if ($editProject): ?>
        <a href="portfolio.php" class="adm-btn adm-btn-secondary adm-btn-sm">Batal</a>
      <?php endif; ?>
    </div>

    <form action="portfolio.php" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($editProject['id'] ?? ''); ?>">

      <div class="adm-form-group">
        <label for="title" class="adm-label">Nama / Judul Proyek *</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editProject['title'] ?? ''); ?>" required placeholder="Contoh: Konservasi & Revitalisasi Bale Meten Tradisional" class="adm-input">
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="category" class="adm-label">Kategori Proyek *</label>
          <select id="category" name="category" required class="adm-select">
            <?php 
            $cats = ['Restorasi Arsitektur', 'Desain Pekarangan Adat', 'Struktur Tradisional', 'Dokumentasi & Riset'];
            $currentCat = $editProject['category'] ?? 'Restorasi Arsitektur';
            foreach ($cats as $c): ?>
              <option value="<?php echo $c; ?>" <?php echo ($currentCat === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="adm-form-group">
          <label for="year" class="adm-label">Tahun Pelaksanaan *</label>
          <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($editProject['year'] ?? date('Y')); ?>" required placeholder="2025" class="adm-input">
        </div>
      </div>

      <div class="adm-form-row">
        <div class="adm-form-group">
          <label for="location" class="adm-label">Lokasi Proyek</label>
          <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($editProject['location'] ?? 'Sibang Kaja, Abiansemal, Badung'); ?>" class="adm-input">
        </div>
        <div class="adm-form-group">
          <label for="lead_architect" class="adm-label">Pimpinan Undagi / Kolaborator</label>
          <input type="text" id="lead_architect" name="lead_architect" value="<?php echo htmlspecialchars($editProject['lead_architect'] ?? 'I Ketut Wirawan & Tim Undagi'); ?>" class="adm-input">
        </div>
      </div>

      <div class="adm-form-group">
        <label for="materials" class="adm-label">Material Tradisional Utama</label>
        <input type="text" id="materials" name="materials" value="<?php echo htmlspecialchars($editProject['materials'] ?? ''); ?>" placeholder="Kayu nangka solid, bata merah gosok, batu paras tulikup" class="adm-input">
      </div>

      <div class="adm-form-group">
        <label for="short_description" class="adm-label">Deskripsi Ringkas (Tampil pada Kartu) *</label>
        <textarea id="short_description" name="short_description" rows="2" required placeholder="Ringkasan 1-2 kalimat..." class="adm-textarea"><?php echo htmlspecialchars($editProject['short_description'] ?? ''); ?></textarea>
      </div>

      <div class="adm-form-group">
        <label for="full_description" class="adm-label">Narasi Lengkap &amp; Konteks Kultural *</label>
        <textarea id="full_description" name="full_description" rows="4" required placeholder="Jelaskan proses rancang bangun dan filosofinya..." class="adm-textarea"><?php echo htmlspecialchars($editProject['full_description'] ?? ''); ?></textarea>
      </div>

      <!-- Unggah Foto Sampul -->
      <div class="adm-form-group" style="background: var(--adm-surface-alt); padding: 16px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border);">
        <label for="cover_upload" class="adm-label">Unggah Foto Sampul Baru</label>
        <input type="file" id="cover_upload" name="cover_upload" accept="image/*" class="adm-input" style="padding: 6px 10px;">
        <?php if (!empty($editProject['cover_photo'])): ?>
          <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
            <img src="../<?php echo htmlspecialchars($editProject['cover_photo']); ?>" alt="Sampul" style="width: 70px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--adm-border);">
            <small style="color: var(--adm-text-subtle);">Sampul saat ini: <?php echo htmlspecialchars(basename($editProject['cover_photo'])); ?></small>
          </div>
        <?php endif; ?>
      </div>

      <!-- Unggah Foto Galeri -->
      <div class="adm-form-group">
        <label for="supporting_photos" class="adm-label">Unggah Foto Pendukung (Multi-file)</label>
        <input type="file" id="supporting_photos" name="supporting_photos[]" accept="image/*" multiple class="adm-input" style="padding: 6px 10px;">
        <span style="font-size: 0.74rem; color: var(--adm-text-subtle); display: block; margin-top: 4px;">Dapat memilih beberapa foto sekaligus untuk galeri karya.</span>
      </div>

      <!-- Kelola Foto Tersimpan -->
      <?php if (!empty($editProject['photos'])): ?>
      <div class="adm-form-group">
        <label class="adm-label">Foto Tersimpan (Pilih Sampul &amp; Kelola):</label>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 8px;">
          <?php foreach ($editProject['photos'] as $idx => $ph): 
            $isCover = ($ph === ($editProject['cover_photo'] ?? ''));
          ?>
          <div style="position: relative; border: 2px solid <?php echo $isCover ? 'var(--adm-primary)' : 'var(--adm-border)'; ?>; border-radius: 8px; overflow: hidden; background: #FFF;">
            <img src="../<?php echo htmlspecialchars($ph); ?>" alt="Foto" style="width: 100%; height: 65px; object-fit: cover; display: block;">
            <input type="hidden" name="existing_photos[]" value="<?php echo htmlspecialchars($ph); ?>">
            <div style="padding: 4px 6px; text-align: center; font-size: 0.72rem; background: var(--adm-surface-alt);">
              <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 4px; margin: 0;">
                <input type="radio" name="selected_cover" value="<?php echo htmlspecialchars($ph); ?>" <?php echo $isCover ? 'checked' : ''; ?>>
                Sampul
              </label>
              <button type="button" onclick="this.closest('div').parentElement.remove();" style="color: var(--adm-red); border: none; background: none; font-size: 0.72rem; cursor: pointer; display: block; margin: 2px auto 0;">Hapus</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Status & Sorotan -->
      <div class="adm-form-row" style="background: var(--adm-surface-alt); padding: 14px; border-radius: var(--adm-radius-md); border: 1px solid var(--adm-border); margin: 20px 0;">
        <div class="adm-form-group" style="margin-bottom: 0;">
          <label for="status" class="adm-label">Status Publikasi</label>
          <select id="status" name="status" class="adm-select">
            <option value="published" <?php echo (($editProject['status'] ?? 'published') === 'published') ? 'selected' : ''; ?>>Dipublikasikan (Publik)</option>
            <option value="draft" <?php echo (($editProject['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft (Internal)</option>
          </select>
        </div>
        <div style="display: flex; align-items: center; padding-top: 18px;">
          <label class="adm-checkbox-label">
            <input type="checkbox" name="is_featured" value="1" <?php echo !empty($editProject['is_featured']) ? 'checked' : ''; ?>>
            <strong>Jadikan Sorotan Utama</strong>
          </label>
        </div>
      </div>

      <button type="submit" class="adm-btn adm-btn-primary" style="width: 100%;">
        <?php echo $editProject ? 'Simpan Perubahan Proyek' : 'Terbitkan Proyek Baru'; ?> &rarr;
      </button>
    </form>
  </div>

  <!-- DAFTAR PROYEK VISUAL CARDS -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div>
        <h3 class="adm-card-title">
          <span>Daftar Proyek Portofolio (<?php echo count($allProjects); ?>)</span>
        </h3>
        <div class="adm-card-subtitle">Karya arsitektur yang terdaftar di sistem</div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 14px;">
      <?php if (empty($allProjects)): ?>
        <p style="padding: 24px; text-align: center; color: var(--adm-text-muted);">Belum ada karya proyek.</p>
      <?php else: ?>
        <?php foreach ($allProjects as $proj): 
          $isPub = (($proj['status'] ?? '') === 'published');
          $cover = !empty($proj['cover_photo']) ? '../' . ltrim($proj['cover_photo'], '/') : '../assets/images/herobg.jpg';
        ?>
        <div style="background: var(--adm-surface-alt); border: 1px solid var(--adm-border); border-radius: var(--adm-radius-md); padding: 14px; display: flex; gap: 14px; align-items: center; justify-content: space-between;">
          <div style="display: flex; gap: 12px; align-items: center; min-width: 0;">
            <div style="width: 70px; height: 55px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: var(--adm-border);">
              <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($proj['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div style="min-width: 0;">
              <div style="display: flex; align-items: center; gap: 6px;">
                <span class="adm-badge adm-badge-category" style="font-size: 0.68rem; padding: 1px 6px;"><?php echo htmlspecialchars($proj['category']); ?></span>
                <?php if (!empty($proj['is_featured'])): ?>
                  <span class="adm-badge" style="background:#FEF3C7; color:#92400E; font-size:0.68rem; padding: 1px 6px;">★ Utama</span>
                <?php endif; ?>
              </div>
              <h4 style="font-family: var(--adm-font-serif); font-size: 0.98rem; color: var(--adm-forest); margin: 3px 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo htmlspecialchars($proj['title']); ?>
              </h4>
              <div style="font-size: 0.76rem; color: var(--adm-text-subtle);">
                Tahun <?php echo htmlspecialchars($proj['year']); ?> &bull; 📸 <?php echo count($proj['photos'] ?? []); ?> foto
              </div>
            </div>
          </div>

          <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
            <a href="../portfolio.php#<?php echo htmlspecialchars($proj['id']); ?>" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm" title="Pratinjau">
              Web &nearr;
            </a>
            <a href="portfolio.php?edit=<?php echo urlencode($proj['id']); ?>" class="adm-btn adm-btn-forest adm-btn-sm" title="Edit">
              Edit
            </a>
            <a href="portfolio.php?toggle_status=<?php echo urlencode($proj['id']); ?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Ubah Status">
              <?php echo $isPub ? 'Publik' : 'Draft'; ?>
            </a>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus permanen proyek \'<?php echo htmlspecialchars(addslashes($proj['title'])); ?>\'?');">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($proj['id']); ?>">
              <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Hapus">
                Hapus
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
