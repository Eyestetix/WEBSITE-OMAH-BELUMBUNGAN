<?php
/**
 * OMAH BELUMBUNGAN - Gallery Event
 * Dokumentasi visual kegiatan budaya, sarasehan undagi, lokakarya, dan kunjungan yang telah selesai.
 */
require_once __DIR__ . '/includes/functions.php';

$allGalleries = get_gallery_events('published');
$galleryId = sanitize($_GET['id'] ?? '');

$activeGallery = null;
if (!empty($galleryId)) {
    foreach ($allGalleries as $gal) {
        if ($gal['id'] === $galleryId || ($gal['slug'] ?? '') === $galleryId) {
            $activeGallery = $gal;
            break;
        }
    }
}

$pageTitle = $activeGallery ? $activeGallery['title'] : 'Gallery Event & Dokumentasi Kegiatan';
include __DIR__ . '/includes/header.php';
?>

<?php if ($activeGallery): ?>
<!-- ========================================================================
     DETAIL VIEW: INDIVIDUAL EVENT GALLERY & LIGHTBOX
     ======================================================================== -->
<section class="section gallery-detail-section" style="padding-top: 50px; padding-bottom: 70px;">
  <div class="container">
    <!-- Return Navigation -->
    <div style="margin-bottom: 24px;">
      <a href="gallery.php" class="link-arrow" style="font-size: 0.88rem;">
        <span>&larr;</span>
        <span>Kembali ke Seluruh Gallery Event</span>
      </a>
    </div>

    <!-- Gallery Header -->
    <div class="gallery-detail-header" style="max-width: 860px; margin-bottom: 36px;">
      <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
        <span class="badge-agenda"><?php echo htmlspecialchars($activeGallery['category'] ?? 'Dokumentasi'); ?></span>
        <span style="font-size: 0.85rem; color: var(--stone-text); display: inline-flex; align-items: center; gap: 4px;"><?php echo ob_icon('calendar', ['size' => 14, 'color' => 'warm']); ?> <?php echo format_date_id($activeGallery['event_date']); ?></span>
        <span style="font-size: 0.85rem; color: var(--stone-text);">&bull;</span>
        <span style="font-size: 0.85rem; color: var(--stone-text); display: inline-flex; align-items: center; gap: 4px;"><?php echo ob_icon('camera', ['size' => 14, 'color' => 'warm']); ?> <?php echo count($activeGallery['photos'] ?? []); ?> Foto Terdokumentasi</span>
      </div>
      <h1 class="section-heading-editorial">
        <?php echo htmlspecialchars($activeGallery['title']); ?>
      </h1>
      <p class="section-subtitle-editorial">
        <?php echo nl2br(htmlspecialchars($activeGallery['full_description'] ?? $activeGallery['short_description'])); ?>
      </p>
    </div>

    <!-- Photo Grid (Accessible Lightbox Triggers) -->
    <?php $photos = $activeGallery['photos'] ?? []; ?>
    <?php if (empty($photos)): ?>
      <div style="background: #FFFFFF; border: 1px dashed var(--sand-border); padding: 40px; border-radius: var(--radius-md); text-align: center; color: var(--stone-text);">
        Belum ada foto yang diunggah untuk event ini.
      </div>
    <?php else: ?>
      <div class="gallery-photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        <?php foreach ($photos as $idx => $photo): ?>
        <div class="gallery-item-card" 
             style="background: #FFFFFF; border: 1px solid var(--sand-border); border-radius: var(--radius-md); overflow: hidden; cursor: pointer; box-shadow: var(--shadow-subtle); transition: transform var(--transition-fast), box-shadow var(--transition-fast);"
             data-index="<?php echo $idx; ?>"
             data-src="<?php echo htmlspecialchars($photo); ?>"
             data-title="<?php echo htmlspecialchars($activeGallery['title']); ?>"
             tabindex="0"
             role="button"
             aria-label="Lihat foto <?php echo $idx + 1; ?> dalam ukuran penuh">
          <div style="height: 220px; overflow: hidden; position: relative; background: var(--sand-tone);">
            <img src="<?php echo htmlspecialchars($photo); ?>" alt="Dokumentasi <?php echo htmlspecialchars($activeGallery['title']); ?> - Foto <?php echo $idx + 1; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform var(--transition-smooth);" loading="lazy">
            <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(30,24,20,0.75); color: #FFF; padding: 4px 8px; border-radius: var(--radius-xs); font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 5px;">
              <?php echo ob_icon('zoom-in', ['size' => 13, 'color' => 'white', 'raw' => true]); ?>
              <span>Perbesar</span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Bottom Actions & Inquiries -->
    <div style="border-top: 1px solid var(--sand-border); margin-top: 50px; padding-top: 36px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
      <a href="gallery.php" class="btn btn-outline-wood btn-sm">
        <span>&larr;</span>
        <span>Kembali ke Galeri Event</span>
      </a>
      <div style="display: flex; gap: 14px; align-items: center;">
        <span style="font-size: 0.88rem; color: var(--stone-text);">Tertarik mengikuti kegiatan serupa?</span>
        <a href="events.php" class="btn btn-terracotta btn-sm">
          <span>Lihat Agenda Terdekat</span>
          <span>&rarr;</span>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Accessible Image Lightbox Modal -->
<div class="lightbox-overlay" id="galleryLightbox" role="dialog" aria-modal="true" aria-label="Penampil Galeri Foto">
  <button class="lightbox-btn lightbox-close" id="lightboxClose" aria-label="Tutup penampil gambar">&times;</button>
  
  <button class="lightbox-btn lightbox-prev" id="lightboxPrev" aria-label="Foto sebelumnya">&#10094;</button>
  <button class="lightbox-btn lightbox-next" id="lightboxNext" aria-label="Foto selanjutnya">&#10095;</button>

  <div class="lightbox-content-box">
    <div class="lightbox-image-wrapper">
      <img src="" alt="Tampilan Penuh Galeri" id="lightboxImg" class="lightbox-img">
    </div>
    <div class="lightbox-caption-bar">
      <div id="lightboxCaption" class="lightbox-caption">Judul Foto</div>
      <div id="lightboxCounter" class="lightbox-counter">1 / 10</div>
    </div>
  </div>
</div>

<script>
  window.galleryPhotos = <?php echo json_encode($photos); ?>;
  window.galleryTitle = <?php echo json_encode($activeGallery['title']); ?>;
</script>

<?php else: ?>
<!-- ========================================================================
     INDEX VIEW: EVENT GALLERY DIRECTORY
     ======================================================================== -->
<section class="section gallery-index-section" style="padding-top: 60px; padding-bottom: 70px;">
  <div class="container">
    <!-- Editorial Header -->
    <div style="max-width: 820px; margin-bottom: 46px;">
      <span class="eyebrow">DOKUMENTASI KEGIATAN &amp; ARSIP VISUAL</span>
      <h1 class="section-heading-editorial">
        Gallery Event &amp; Rekam Jejak Budaya
      </h1>
      <p class="section-subtitle-editorial">
        Kumpulan dokumentasi fotografi dari sarasehan undagi, lokakarya arsitektur, 
        kunjungan riset akademis, dan pertemuan komunitas yang telah terselenggara di Omah Belumbungan.
      </p>
    </div>

    <!-- Gallery Grid -->
    <?php if (empty($allGalleries)): ?>
      <div style="background: #FFFFFF; border: 1px dashed var(--sand-border); padding: 60px 20px; border-radius: var(--radius-md); text-align: center; color: var(--stone-text);">
        <p style="font-size: 1.1rem; margin-bottom: 10px;">Belum ada dokumentasi event yang dipublikasikan.</p>
        <p style="font-size: 0.9rem;">Dokumentasi kegiatan terbaru akan segera hadir di halaman ini.</p>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 30px; margin-bottom: 60px;">
        <?php foreach ($allGalleries as $event): 
          $photoCount = count($event['photos'] ?? []);
          $cover = !empty($event['cover_photo']) ? $event['cover_photo'] : ($event['photos'][0] ?? 'assets/images/photo-01.jpg');
        ?>
        <article class="gallery-card" style="background: #FFFFFF; border: 1px solid var(--sand-border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-subtle); transition: transform var(--transition-fast), box-shadow var(--transition-fast);">
          <div>
            <!-- Cover Photo with Badge -->
            <div style="height: 230px; position: relative; overflow: hidden; background: var(--sand-tone);">
              <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
              
              <div style="position: absolute; top: 12px; left: 12px; display: flex; gap: 8px;">
                <span style="background: rgba(30, 24, 20, 0.85); color: #FFF; padding: 4px 10px; border-radius: var(--radius-xs); font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                  <?php echo htmlspecialchars($event['category'] ?? 'Event'); ?>
                </span>
              </div>

              <div style="position: absolute; bottom: 12px; right: 12px; background: rgba(255, 255, 255, 0.92); color: var(--dark-wood); padding: 4px 10px; border-radius: var(--radius-pill); font-size: 0.76rem; font-weight: 700; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <?php echo ob_icon('camera', ['size' => 14, 'color' => 'forest', 'raw' => true]); ?>
                <span><?php echo $photoCount; ?> Foto</span>
              </div>
            </div>

            <!-- Content Area -->
            <div style="padding: 24px 22px 14px;">
              <div style="font-size: 0.82rem; color: var(--stone-text); margin-bottom: 8px; display: flex; align-items: center; gap: 5px;">
                <?php echo ob_icon('calendar', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?>
                <span><?php echo format_date_id($event['event_date']); ?></span>
              </div>
              <h2 style="font-size: 1.28rem; line-height: 1.35; margin-bottom: 12px;">
                <a href="gallery.php?id=<?php echo urlencode($event['id']); ?>" style="color: var(--dark-wood); text-decoration: none;">
                  <?php echo htmlspecialchars($event['title']); ?>
                </a>
              </h2>
              <p style="font-size: 0.92rem; color: var(--stone-text); line-height: 1.6;">
                <?php echo htmlspecialchars($event['short_description']); ?>
              </p>
            </div>
          </div>

          <!-- Card Footer Action -->
          <div style="padding: 14px 22px 20px; border-top: 1px solid rgba(226, 214, 192, 0.5);">
            <a href="gallery.php?id=<?php echo urlencode($event['id']); ?>" class="link-arrow" style="font-size: 0.9rem;">
              <span>Lihat Seluruh Galeri (<?php echo $photoCount; ?> Foto)</span>
              <span>&rarr;</span>
            </a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Bottom Workshop Callout -->
    <div style="background: var(--cream-surface); border: 1px solid var(--sand-border); border-radius: var(--radius-lg); padding: 44px; text-align: center;">
      <h3 style="font-size: 1.7rem; margin-bottom: 12px;">Ingin Terlibat dalam Dokumentasi atau Lokakarya Berikutnya?</h3>
      <p style="color: var(--stone-text); max-width: 650px; margin: 0 auto 24px; font-size: 1rem;">
        Cek jadwal agenda yang akan datang atau ajukan sesi kunjungan belajar bagi komunitas dan rombongan Anda.
      </p>
      <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
        <a href="events.php" class="btn btn-terracotta">
          <span>Lihat Agenda &amp; Workshop</span>
          <span>&rarr;</span>
        </a>
        <a href="reservation.php" class="btn btn-outline-wood">
          <span>Reservasi Jadwal Kunjungan</span>
        </a>
      </div>
    </div>

  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
