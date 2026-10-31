<?php
/**
 * OMAH BELUMBUNGAN - Pustaka Media Terpadu (Media Library)
 * Reusable Media Explorer, Upload Management, Dimensions & Copyable Asset Paths
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
$adminTitle = 'Pustaka Media';
require_once __DIR__ . '/header.php';

// Tangani upload media baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $targetFolder = sanitize($_POST['target_folder'] ?? 'gallery');
    if (!in_array($targetFolder, ['gallery', 'portfolio'], true)) {
        $targetFolder = 'gallery';
    }

    if ($_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $res = upload_image_secure($_FILES['media_file']['tmp_name'], $_FILES['media_file']['name'], $targetFolder);
        if ($res['success']) {
            set_flash('success', "Berkas '<strong>" . htmlspecialchars($_FILES['media_file']['name']) . "</strong>' berhasil diunggah ke <code>{$res['path']}</code>.");
        } else {
            set_flash('error', "Gagal mengunggah berkas: " . $res['error']);
        }
    } else {
        set_flash('error', "Terjadi kesalahan saat mengunggah berkas (Error code: " . $_FILES['media_file']['error'] . ").");
    }
    header('Location: media.php');
    exit;
}

// Kumpulkan seluruh aset media dari direktori
$baseDir = dirname(__DIR__);
$directories = [
    'Aset Utama' => $baseDir . '/assets/images',
    'Portofolio' => $baseDir . '/uploads/portfolio',
    'Galeri Event' => $baseDir . '/uploads/gallery'
];

$mediaFiles = [];
$allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

foreach ($directories as $catName => $dirPath) {
    if (!is_dir($dirPath)) continue;

    $files = scandir($dirPath);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..' || $f === '.DS_Store' || $f === 'thumbs') continue;
        $fullPath = $dirPath . '/' . $f;
        if (!is_file($fullPath)) continue;

        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) continue;

        $relPath = str_replace($baseDir . '/', '', $fullPath);
        $sizeBytes = filesize($fullPath);
        $modTime = filemtime($fullPath);

        $width = 0;
        $height = 0;
        if ($ext !== 'svg') {
            $imgInfo = @getimagesize($fullPath);
            if ($imgInfo) {
                $width = $imgInfo[0];
                $height = $imgInfo[1];
            }
        }

        $mediaFiles[] = [
            'filename' => $f,
            'rel_path' => $relPath,
            'full_path' => $fullPath,
            'category' => $catName,
            'ext' => $ext,
            'size' => $sizeBytes,
            'size_formatted' => ($sizeBytes > 1048576) ? (round($sizeBytes / 1048576, 2) . ' MB') : (round($sizeBytes / 1024, 1) . ' KB'),
            'width' => $width,
            'height' => $height,
            'mod_time' => $modTime
        ];
    }
}

// Urutkan berdasarkan waktu modifikasi terbaru
usort($mediaFiles, function($a, $b) {
    return $b['mod_time'] <=> $a['mod_time'];
});

// Filter pencarian dan kategori
$searchQ = trim($_GET['q'] ?? '');
$filterCat = $_GET['cat'] ?? 'all';

$filteredMedia = $mediaFiles;
if ($filterCat !== 'all') {
    $filteredMedia = array_values(array_filter($filteredMedia, function($m) use ($filterCat) {
        return $m['category'] === $filterCat;
    }));
}
if ($searchQ !== '') {
    $qLower = strtolower($searchQ);
    $filteredMedia = array_values(array_filter($filteredMedia, function($m) use ($qLower) {
        return str_contains(strtolower($m['filename']), $qLower) ||
               str_contains(strtolower($m['rel_path']), $qLower);
    }));
}
?>

<!-- Header Actions & Search -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <!-- Category Tabs -->
  <div class="adm-tabs-bar" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
    <a href="media.php?cat=all" class="adm-tab-btn <?php echo ($filterCat === 'all') ? 'active' : ''; ?>">
      Semua Berkas <span class="tab-count"><?php echo count($mediaFiles); ?></span>
    </a>
    <a href="media.php?cat=Aset+Utama" class="adm-tab-btn <?php echo ($filterCat === 'Aset Utama') ? 'active' : ''; ?>">
      Aset Utama
    </a>
    <a href="media.php?cat=Galeri+Event" class="adm-tab-btn <?php echo ($filterCat === 'Galeri Event') ? 'active' : ''; ?>">
      Galeri Event
    </a>
    <a href="media.php?cat=Portofolio" class="adm-tab-btn <?php echo ($filterCat === 'Portofolio') ? 'active' : ''; ?>">
      Portofolio
    </a>
  </div>

  <!-- Search & Upload Trigger -->
  <div style="display: flex; gap: 10px; align-items: center;">
    <form action="media.php" method="GET" style="display: flex; gap: 6px;">
      <input type="hidden" name="cat" value="<?php echo htmlspecialchars($filterCat); ?>">
      <input type="text" name="q" value="<?php echo htmlspecialchars($searchQ); ?>" placeholder="Cari nama berkas..." class="adm-input" style="width: 220px; padding: 7px 12px; font-size: 0.85rem;">
      <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">Cari</button>
      <?php if ($searchQ !== ''): ?>
        <a href="media.php?cat=<?php echo htmlspecialchars($filterCat); ?>" class="adm-btn adm-btn-secondary adm-btn-sm">&times;</a>
      <?php endif; ?>
    </form>
    <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" onclick="document.getElementById('uploadModal').style.display='flex';">
      + Unggah Media
    </button>
  </div>
</div>

<!-- MEDIA GRID -->
<div class="adm-card">
  <div class="adm-card-header">
    <div>
      <h2 class="adm-card-title">
        <span>Katalog Media (<?php echo count($filteredMedia); ?> Berkas)</span>
      </h2>
      <div class="adm-card-subtitle">Klik gambar untuk melihat detail dimensi, pratinjau penuh, atau salin path berkas</div>
    </div>
  </div>

  <?php if (empty($filteredMedia)): ?>
    <div style="padding: 48px 20px; text-align: center; color: var(--adm-text-muted);">
      <p>Tidak ada berkas media yang cocok.</p>
    </div>
  <?php else: ?>
    <div class="adm-media-grid">
      <?php foreach ($filteredMedia as $item): ?>
      <div class="adm-media-item" onclick="openMediaDetail(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)" style="cursor: pointer;">
        <div class="adm-media-thumb">
          <?php if ($item['ext'] === 'svg'): ?>
            <img src="../<?php echo htmlspecialchars($item['rel_path']); ?>" alt="<?php echo htmlspecialchars($item['filename']); ?>" style="width: 60%; height: 60%; object-fit: contain;">
          <?php else: ?>
            <img src="../<?php echo htmlspecialchars($item['rel_path']); ?>" alt="<?php echo htmlspecialchars($item['filename']); ?>" loading="lazy">
          <?php endif; ?>
        </div>
        <div class="adm-media-info">
          <div class="adm-media-name" title="<?php echo htmlspecialchars($item['filename']); ?>">
            <?php echo htmlspecialchars($item['filename']); ?>
          </div>
          <div class="adm-media-meta">
            <span><?php echo $item['size_formatted']; ?></span>
            <span><?php echo htmlspecialchars($item['category']); ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- MODAL UPLOAD MEDIA -->
<div id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(16, 24, 15, 0.65); backdrop-filter: blur(2px); z-index: 110; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFFFFF; border-radius: var(--adm-radius-lg); max-width: 480px; width: 100%; padding: 28px; box-shadow: var(--adm-shadow-lg); border: 1px solid var(--adm-border);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="font-family: var(--adm-font-serif); font-size: 1.25rem; color: var(--adm-forest); margin: 0;">
        Unggah Berkas Media Baru
      </h3>
      <button type="button" onclick="document.getElementById('uploadModal').style.display='none';" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--adm-text-subtle);">&times;</button>
    </div>

    <form action="media.php" method="POST" enctype="multipart/form-data">
      <div class="adm-form-group">
        <label for="media_file" class="adm-label">Pilih Berkas Foto (JPG, PNG, WEBP) *</label>
        <input type="file" id="media_file" name="media_file" accept="image/jpeg,image/png,image/webp" required class="adm-input" style="padding: 8px 12px;">
      </div>

      <div class="adm-form-group">
        <label for="target_folder" class="adm-label">Direktori Penyimpanan *</label>
        <select id="target_folder" name="target_folder" class="adm-select">
          <option value="gallery">uploads/gallery/ (Galeri Dokumentasi)</option>
          <option value="portfolio">uploads/portfolio/ (Karya & Proyek)</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
        <button type="button" class="adm-btn adm-btn-secondary" onclick="document.getElementById('uploadModal').style.display='none';">Batal</button>
        <button type="submit" class="adm-btn adm-btn-primary">Unggah Berkas</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL DETAIL MEDIA -->
<div id="detailModal" style="display: none; position: fixed; inset: 0; background: rgba(16, 24, 15, 0.65); backdrop-filter: blur(2px); z-index: 110; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFFFFF; border-radius: var(--adm-radius-lg); max-width: 640px; width: 100%; padding: 26px; box-shadow: var(--adm-shadow-lg); border: 1px solid var(--adm-border); max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h3 id="dtlTitle" style="font-family: var(--adm-font-serif); font-size: 1.15rem; color: var(--adm-forest); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 450px;">
        Detail Media
      </h3>
      <button type="button" onclick="document.getElementById('detailModal').style.display='none';" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--adm-text-subtle);">&times;</button>
    </div>

    <!-- Preview Image -->
    <div style="width: 100%; height: 260px; background: var(--adm-surface-alt); border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 18px; border: 1px solid var(--adm-border-light);">
      <img id="dtlImg" src="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
    </div>

    <!-- Metadata Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; font-size: 0.85rem;">
      <div style="background: var(--adm-surface-alt); padding: 8px 12px; border-radius: 6px;">
        <span style="color: var(--adm-text-subtle); font-size: 0.74rem; display: block;">KATEGORI / FOLDER</span>
        <strong id="dtlCat" style="color: var(--adm-forest);">—</strong>
      </div>
      <div style="background: var(--adm-surface-alt); padding: 8px 12px; border-radius: 6px;">
        <span style="color: var(--adm-text-subtle); font-size: 0.74rem; display: block;">UKURAN BERKAS</span>
        <strong id="dtlSize" style="color: var(--adm-forest);">—</strong>
      </div>
      <div style="background: var(--adm-surface-alt); padding: 8px 12px; border-radius: 6px;">
        <span style="color: var(--adm-text-subtle); font-size: 0.74rem; display: block;">DIMENSI PIKSEL</span>
        <strong id="dtlDim" style="color: var(--adm-forest);">—</strong>
      </div>
      <div style="background: var(--adm-surface-alt); padding: 8px 12px; border-radius: 6px;">
        <span style="color: var(--adm-text-subtle); font-size: 0.74rem; display: block;">FORMAT</span>
        <strong id="dtlExt" style="color: var(--adm-forest);">—</strong>
      </div>
    </div>

    <!-- Copyable Path Input -->
    <div class="adm-form-group">
      <label class="adm-label">Path Berkas Relatif (Gunakan di Agenda / Portofolio / Anatomi):</label>
      <div style="display: flex; gap: 8px;">
        <input type="text" id="dtlPath" readonly class="adm-input" style="font-family: monospace; font-size: 0.84rem; background: var(--adm-surface-alt);">
        <button type="button" class="adm-btn adm-btn-forest adm-btn-sm" onclick="copyDtlPath()" id="btnCopyPath">
          Salin Path
        </button>
      </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
      <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" onclick="document.getElementById('detailModal').style.display='none';">
        Tutup
      </button>
    </div>
  </div>
</div>

<script>
  function openMediaDetail(item) {
    document.getElementById('dtlTitle').textContent = item.filename;
    document.getElementById('dtlImg').src = '../' + item.rel_path;
    document.getElementById('dtlCat').textContent = item.category;
    document.getElementById('dtlSize').textContent = item.size_formatted;
    document.getElementById('dtlDim').textContent = (item.width && item.height) ? (item.width + ' × ' + item.height + ' px') : 'Vektor SVG';
    document.getElementById('dtlExt').textContent = item.ext.toUpperCase();
    document.getElementById('dtlPath').value = item.rel_path;

    const btn = document.getElementById('btnCopyPath');
    btn.textContent = 'Salin Path';
    btn.classList.remove('adm-btn-success');
    btn.classList.add('adm-btn-forest');

    document.getElementById('detailModal').style.display = 'flex';
  }

  function copyDtlPath() {
    const input = document.getElementById('dtlPath');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
      const btn = document.getElementById('btnCopyPath');
      btn.textContent = '✓ Tersalin!';
      btn.classList.remove('adm-btn-forest');
      btn.classList.add('adm-btn-success');
      setTimeout(() => {
        btn.textContent = 'Salin Path';
        btn.classList.remove('adm-btn-success');
        btn.classList.add('adm-btn-forest');
      }, 2000);
    });
  }

  window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.getElementById('uploadModal').style.display = 'none';
      document.getElementById('detailModal').style.display = 'none';
    }
  });
</script>

<?php include __DIR__ . '/footer.php'; ?>
