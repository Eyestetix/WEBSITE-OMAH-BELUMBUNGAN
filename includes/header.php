<?php
/**
 * OMAH BELUMBUNGAN - Header Component
 * Editorial Cultural Sanctuary & Living Balinese Architecture Archive
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
require_once __DIR__ . '/functions.php';
$settings = get_settings();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : ''; ?><?php echo htmlspecialchars($settings['site_name'] ?? 'Omah Belumbungan'); ?> | Ruang Budaya & Edukasi Arsitektur Bali</title>
  <meta name="description" content="<?php echo htmlspecialchars($settings['description'] ?? 'Pelestarian warisan arsitektur tradisional Bali berlandaskan filosofi Tri Hita Karana dan Asta Kosala Kosali di Sibang Kaja, Abiansemal.'); ?>">
  
  <!-- Open Graph Metadata -->
  <meta property="og:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : ''; ?><?php echo htmlspecialchars($settings['site_name'] ?? 'Omah Belumbungan'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($settings['description'] ?? ''); ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="id_ID">
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/images/LogoOmah.png">

  <!-- Typography: Editorial Serif & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@600;700;800&display=swap" rel="stylesheet">
  
  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="container navbar">
    <!-- 1. Official Logo (Linked to Homepage) -->
    <a href="index.php" class="brand-logo" aria-label="Beranda Omah Belumbungan">
      <img src="assets/images/LogoOmah.png" alt="Logo Omah Belumbungan" width="56" height="50" class="brand-logo-img">
    </a>

    <!-- Mobile Header Actions (Compact CTA & Hamburger) -->
    <div class="mobile-header-actions">
      <a href="reservation.php" class="btn btn-terracotta btn-mobile-cta" aria-label="Rencanakan Kunjungan">
        <span>Kunjungan</span>
        <span aria-hidden="true">&rarr;</span>
      </a>
      <button class="menu-toggle" id="menuToggle" aria-label="Buka navigasi menu" aria-expanded="false" aria-controls="navContainer">
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
      </button>
    </div>

    <!-- Desktop & Mobile Drawer Navigation Container -->
    <nav class="nav-container" id="navContainer" aria-label="Navigasi Utama">
      <!-- 2–5. Four Concise Single-Word Menu Labels -->
      <ul class="nav-menu" id="navMenu">
        <li>
          <a href="events.php" class="nav-link <?php echo ($currentPage === 'events.php') ? 'active' : ''; ?>">Agenda</a>
        </li>
        <li>
          <a href="portfolio.php" class="nav-link <?php echo ($currentPage === 'portfolio.php') ? 'active' : ''; ?>">Karya</a>
        </li>
        <li>
          <a href="gallery.php" class="nav-link <?php echo ($currentPage === 'gallery.php') ? 'active' : ''; ?>">Galeri</a>
        </li>
        <li>
          <a href="about.php" class="nav-link <?php echo ($currentPage === 'about.php') ? 'active' : ''; ?>">Tentang</a>
        </li>
      </ul>

      <!-- 6–7. Search Icon & Primary Terracotta CTA -->
      <div class="nav-actions">
        <button class="nav-search-btn" id="searchBtn" aria-label="Cari arsip arsitektur & kegiatan">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
        <a href="reservation.php" class="btn btn-terracotta btn-nav-cta">
          <span>Rencanakan Kunjungan</span>
          <span class="cta-arrow" aria-hidden="true">&rarr;</span>
        </a>
      </div>
    </nav>
  </div>
</header>
