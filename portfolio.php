<?php
/**
 * OMAH BELUMBUNGAN - Portfolio Proyek
 * Menampilkan karya arsitektur, restorasi adat, revitalisasi pekarangan, dan dokumentasi spasial.
 */
require_once __DIR__ . '/includes/functions.php';

$allProjects = get_portfolio('published');
$projectId = sanitize($_GET['id'] ?? '');

$activeProject = null;
if (!empty($projectId)) {
    foreach ($allProjects as $proj) {
        if ($proj['id'] === $projectId || ($proj['slug'] ?? '') === $projectId) {
            $activeProject = $proj;
            break;
        }
    }
}

$pageTitle = $activeProject ? $activeProject['title'] : 'Portfolio Proyek & Dokumentasi';
include __DIR__ . '/includes/header.php';
?>

<?php if ($activeProject): ?>
<!-- ========================================================================
     DETAIL VIEW: PORTFOLIO PROYEK
     ======================================================================== -->
<section class="section project-detail-section" style="padding-top: 50px;">
  <div class="container">
    <!-- Breadcrumb & Back Link -->
    <div style="margin-bottom: 24px;">
      <a href="portfolio.php" class="link-arrow" style="font-size: 0.88rem;">
        <span>&larr;</span>
        <span>Kembali ke Seluruh Portfolio Proyek</span>
      </a>
    </div>

    <!-- Project Header -->
    <div class="project-header" style="max-width: 900px; margin-bottom: 32px;">
      <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
        <span class="badge-agenda"><?php echo htmlspecialchars($activeProject['category'] ?? 'Proyek'); ?></span>
        <span style="font-size: 0.85rem; color: var(--stone-text);">Tahun <?php echo htmlspecialchars($activeProject['year'] ?? '-'); ?></span>
        <span style="font-size: 0.85rem; color: var(--stone-text);">&bull;</span>
        <span style="font-size: 0.85rem; color: var(--stone-text); display: inline-flex; align-items: center; gap: 4px;"><?php echo ob_icon('map-pin', ['size' => 14, 'color' => 'warm']); ?> <?php echo htmlspecialchars($activeProject['location'] ?? 'Sibang Kaja, Bali'); ?></span>
      </div>
      <h1 class="section-heading-editorial">
        <?php echo htmlspecialchars($activeProject['title']); ?>
      </h1>
      <p class="section-subtitle-editorial">
        <?php echo htmlspecialchars($activeProject['short_description']); ?>
      </p>
    </div>

    <!-- Main Hero Image -->
    <div class="project-hero-image-wrap" style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-card); margin-bottom: 40px; max-height: 540px; background: var(--sand-tone);">
      <img src="<?php echo htmlspecialchars($activeProject['cover_photo']); ?>" alt="<?php echo htmlspecialchars($activeProject['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- Project Metadata & Narrative Grid -->
    <div class="project-body-grid" style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 48px; margin-bottom: 60px;">
      <!-- Narrative Left Column -->
      <div class="project-narrative">
        <h2 style="font-size: 1.6rem; margin-bottom: 16px;">Narasi &amp; Konteks Kultural</h2>
        <div style="font-size: 1.02rem; line-height: 1.8; color: var(--dark-wood); margin-bottom: 32px;">
          <p><?php echo nl2br(htmlspecialchars($activeProject['full_description'])); ?></p>
        </div>

        <!-- Additional Supporting Photographs -->
        <?php if (!empty($activeProject['photos']) && count($activeProject['photos']) > 1): ?>
        <h3 style="font-size: 1.35rem; margin-bottom: 16px;">Dokumentasi Visual Proyek</h3>
        <div class="project-gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px;">
          <?php foreach ($activeProject['photos'] as $idx => $photo): ?>
          <div class="project-gallery-item" style="border-radius: var(--radius-sm); overflow: hidden; height: 180px; box-shadow: var(--shadow-subtle);">
            <img src="<?php echo htmlspecialchars($photo); ?>" alt="Dokumentasi <?php echo htmlspecialchars($activeProject['title']); ?> #<?php echo $idx + 1; ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Specs & Metadata Sidebar -->
      <aside class="project-sidebar">
        <div class="sidebar-box" style="background: #FFFFFF; border: 1px solid var(--sand-border); border-radius: var(--radius-md); padding: 28px; box-shadow: var(--shadow-subtle);">
          <h3 style="font-size: 1.15rem; margin-bottom: 18px; border-bottom: 1px solid var(--sand-border); padding-bottom: 10px;">Rincian Proyek</h3>

          <div style="margin-bottom: 16px;">
            <strong style="display: block; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--terracotta);">Kategori</strong>
            <span style="font-size: 0.95rem; color: var(--dark-wood);"><?php echo htmlspecialchars($activeProject['category'] ?? '-'); ?></span>
          </div>

          <div style="margin-bottom: 16px;">
            <strong style="display: block; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--terracotta);">Lokasi</strong>
            <span style="font-size: 0.95rem; color: var(--dark-wood);"><?php echo htmlspecialchars($activeProject['location'] ?? 'Sibang Kaja, Badung, Bali'); ?></span>
          </div>

          <div style="margin-bottom: 16px;">
            <strong style="display: block; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--terracotta);">Pimpinan Undagi / Arsitek</strong>
            <span style="font-size: 0.95rem; color: var(--dark-wood);"><?php echo htmlspecialchars($activeProject['lead_architect'] ?? 'Undagi Omah Belumbungan'); ?></span>
          </div>

          <div style="margin-bottom: 24px;">
            <strong style="display: block; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--terracotta);">Material Tradisional Kunci</strong>
            <span style="font-size: 0.92rem; color: var(--stone-text); line-height: 1.5; display: block;"><?php echo htmlspecialchars($activeProject['materials'] ?? '-'); ?></span>
          </div>

          <a href="reservation.php" class="btn btn-terracotta" style="width: 100%; font-size: 0.88rem;">
            <span>Konsultasi &amp; Kunjungan</span>
            <span>&rarr;</span>
          </a>
        </div>
      </aside>
    </div>

    <!-- Related Projects Section -->
    <?php 
    $relatedProjects = array_filter($allProjects, function($p) use ($activeProject) {
        return $p['id'] !== $activeProject['id'];
    });
    $relatedProjects = array_slice($relatedProjects, 0, 3);
    if (!empty($relatedProjects)):
    ?>
    <div style="border-top: 1px solid var(--sand-border); padding-top: 48px; margin-top: 40px;">
      <h2 style="font-size: 1.8rem; margin-bottom: 24px;">Proyek Terkait Lainnya</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
        <?php foreach ($relatedProjects as $rel): ?>
        <a href="portfolio.php?id=<?php echo urlencode($rel['id']); ?>" class="portfolio-card" style="background: #FFFFFF; border: 1px solid var(--sand-border); border-radius: var(--radius-md); overflow: hidden; text-decoration: none; color: inherit; box-shadow: var(--shadow-subtle); display: flex; flex-direction: column;">
          <div style="height: 190px; overflow: hidden; background: var(--sand-tone);">
            <img src="<?php echo htmlspecialchars($rel['cover_photo']); ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <span style="font-size: 0.76rem; font-weight: 700; color: var(--terracotta); text-transform: uppercase; letter-spacing: 1px;"><?php echo htmlspecialchars($rel['category']); ?></span>
              <h3 style="font-size: 1.15rem; margin: 6px 0 8px;"><?php echo htmlspecialchars($rel['title']); ?></h3>
              <p style="font-size: 0.86rem; color: var(--stone-text); line-height: 1.5;"><?php echo htmlspecialchars($rel['short_description']); ?></p>
            </div>
            <span class="link-arrow" style="margin-top: 14px; font-size: 0.86rem;">
              <span>Lihat Rincian</span>
              <span>&rarr;</span>
            </span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php else: ?>
<!-- ========================================================================
     INDEX VIEW: PORTFOLIO PROYEK DIRECTORY
     ======================================================================== -->
<section class="section portfolio-hero-section" style="padding-top: 60px; padding-bottom: 30px;">
  <div class="container">
    <div style="max-width: 820px; margin-bottom: 40px;">
      <span class="eyebrow">ARSIP KARYA &amp; RESTORASI</span>
      <h1 class="section-heading-editorial">
        Portfolio Proyek &amp; Dokumentasi Arsitektur
      </h1>
      <p class="section-subtitle-editorial">
        Menampilkan karya pemugaran fisik, revitalisasi pekarangan adat Sanga Mandala, konstruksi 
        bale tradisional, dan studi terukur yang dirancang dan didokumentasikan bersama para undagi ahli di Omah Belumbungan.
      </p>
    </div>

    <!-- Featured Project Banner (If available) -->
    <?php 
    $featuredProject = null;
    foreach ($allProjects as $p) {
        if (!empty($p['is_featured'])) {
            $featuredProject = $p;
            break;
        }
    }
    if (!$featuredProject && !empty($allProjects)) {
        $featuredProject = $allProjects[0];
    }
    ?>
    <?php if ($featuredProject): ?>
    <div class="featured-project-card" style="background: #FFFFFF; border: 1px solid var(--sand-border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-card); margin-bottom: 60px;">
      <div style="display: grid; grid-template-columns: 1.25fr 1fr; align-items: stretch;">
        <div style="height: 380px; overflow: hidden; background: var(--sand-tone);">
          <img src="<?php echo htmlspecialchars($featuredProject['cover_photo']); ?>" alt="<?php echo htmlspecialchars($featuredProject['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div style="padding: 40px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px;">
              <span class="badge-agenda">Proyek Sorotan</span>
              <span style="font-size: 0.8rem; color: var(--stone-text);"><?php echo htmlspecialchars($featuredProject['category']); ?> &bull; <?php echo htmlspecialchars($featuredProject['year']); ?></span>
            </div>
            <h2 style="font-size: 1.85rem; line-height: 1.25; margin-bottom: 14px;">
              <a href="portfolio.php?id=<?php echo urlencode($featuredProject['id']); ?>" style="color: var(--dark-wood); text-decoration: none;">
                <?php echo htmlspecialchars($featuredProject['title']); ?>
              </a>
            </h2>
            <p style="color: var(--stone-text); font-size: 0.96rem; line-height: 1.6; margin-bottom: 20px;">
              <?php echo htmlspecialchars($featuredProject['short_description']); ?>
            </p>
            <div style="font-size: 0.85rem; color: var(--dark-wood); margin-bottom: 10px;">
              <strong>Lokasi:</strong> <?php echo htmlspecialchars($featuredProject['location'] ?? 'Sibang Kaja, Bali'); ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--stone-text);">
              <strong>Pimpinan:</strong> <?php echo htmlspecialchars($featuredProject['lead_architect'] ?? 'Undagi Omah Belumbungan'); ?>
            </div>
          </div>

          <div style="margin-top: 24px;">
            <a href="portfolio.php?id=<?php echo urlencode($featuredProject['id']); ?>" class="btn btn-terracotta btn-sm">
              <span>Eksplorasi Rincian Proyek</span>
              <span>&rarr;</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Portfolio Grid Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
      <h2 style="font-size: 1.8rem; margin: 0;">Seluruh Karya Arsitektur &amp; Restorasi</h2>
      <span style="font-size: 0.9rem; color: var(--stone-text);">Total <?php echo count($allProjects); ?> Proyek Terdokumentasi</span>
    </div>

    <!-- Grid of Projects -->
    <?php if (empty($allProjects)): ?>
      <div style="background: #FFFFFF; border: 1px dashed var(--sand-border); padding: 50px 20px; border-radius: var(--radius-md); text-align: center; color: var(--stone-text);">
        Belum ada proyek yang dipublikasikan saat ini.
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px; margin-bottom: 70px;">
        <?php foreach ($allProjects as $proj): ?>
        <article class="portfolio-card" style="background: #FFFFFF; border: 1px solid var(--sand-border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-subtle); transition: transform var(--transition-fast), box-shadow var(--transition-fast);">
          <div>
            <div style="height: 220px; position: relative; overflow: hidden; background: var(--sand-tone);">
              <img src="<?php echo htmlspecialchars($proj['cover_photo']); ?>" alt="<?php echo htmlspecialchars($proj['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
              <span style="position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.92); padding: 4px 10px; border-radius: var(--radius-xs); font-size: 0.74rem; font-weight: 700; color: var(--dark-wood);">
                <?php echo htmlspecialchars($proj['year'] ?? '-'); ?>
              </span>
            </div>

            <div style="padding: 24px 22px 14px;">
              <span style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--terracotta);">
                <?php echo htmlspecialchars($proj['category']); ?>
              </span>
              <h3 style="font-size: 1.25rem; line-height: 1.35; margin: 8px 0 10px;">
                <a href="portfolio.php?id=<?php echo urlencode($proj['id']); ?>" style="color: var(--dark-wood); text-decoration: none;">
                  <?php echo htmlspecialchars($proj['title']); ?>
                </a>
              </h3>
              <p style="font-size: 0.9rem; color: var(--stone-text); line-height: 1.55; margin-bottom: 12px;">
                <?php echo htmlspecialchars($proj['short_description']); ?>
              </p>
              <div style="font-size: 0.8rem; color: var(--stone-light); display: inline-flex; align-items: center; gap: 4px;">
                <?php echo ob_icon('map-pin', ['size' => 13, 'color' => 'warm']); ?>
                <span><?php echo htmlspecialchars($proj['location'] ?? 'Sibang Kaja, Bali'); ?></span>
              </div>
            </div>
          </div>

          <div style="padding: 14px 22px 20px; border-top: 1px solid rgba(226, 214, 192, 0.5);">
            <a href="portfolio.php?id=<?php echo urlencode($proj['id']); ?>" class="link-arrow" style="font-size: 0.88rem;">
              <span>Lihat Rincian &amp; Dokumentasi</span>
              <span>&rarr;</span>
            </a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Consultation CTA -->
    <div style="background: var(--cream-surface); border: 1px solid var(--sand-border); border-radius: var(--radius-lg); padding: 44px; text-align: center; margin-bottom: 40px;">
      <h3 style="font-size: 1.7rem; margin-bottom: 12px;">Memiliki Rencana Konservasi atau Pembangunan Rumah Adat?</h3>
      <p style="color: var(--stone-text); max-width: 650px; margin: 0 auto 24px; font-size: 1rem;">
        Diskusikan langsung prinsip tata ruang Asta Kosala Kosali, pemilihan material alami, dan proporsi raga manusia bersama para undagi di Omah Belumbungan.
      </p>
      <a href="reservation.php" class="btn btn-terracotta">
        <span>Rencanakan Konsultasi / Kunjungan</span>
        <span>&rarr;</span>
      </a>
    </div>

  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
