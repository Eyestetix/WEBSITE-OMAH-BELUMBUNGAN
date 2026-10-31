<?php
/**
 * OMAH BELUMBUNGAN - Header Admin CMS
 * Modular, scalable content management and visitor operations system
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();

$currentUser = get_logged_in_user();
$currentPage = basename($_SERVER['PHP_SELF']);
$currentTab = $_GET['tab'] ?? '';
$settings = get_settings();

// Dynamic counters for badge indicators
$allBookings = get_bookings();
$pendingBookingsCount = count(array_filter($allBookings, function($b) {
    return ($b['status'] ?? '') === 'pending';
}));
$upcomingEventsCount = count(get_events('upcoming'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($adminTitle) ? htmlspecialchars($adminTitle) . ' — ' : ''; ?>Admin CMS | Omah Belumbungan</title>
  <link rel="icon" type="image/png" href="../assets/images/LogoOmah.png">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- Mobile Drawer Backdrop -->
<div class="adm-drawer-backdrop" id="admDrawerBackdrop" aria-hidden="true"></div>

<!-- 1. PERSISTENT SIDEBAR -->
<aside class="adm-sidebar" id="admSidebar" aria-label="Navigasi Utama Admin CMS">
  <!-- Brand Area -->
  <div class="adm-sidebar-brand">
    <div class="adm-brand-badge" aria-hidden="true" style="padding: 2px;">
      <img src="../assets/images/LogoOmah.png" alt="OB" style="width: 28px; height: 26px; object-fit: contain;">
    </div>
    <div class="adm-brand-info">
      <h2>Omah Belumbungan</h2>
      <span>Living Heritage CMS</span>
    </div>
  </div>

  <!-- Navigation Groups -->
  <nav class="adm-sidebar-nav">
    <!-- GROUP 1: WORKSPACE -->
    <div class="adm-nav-group">
      <span class="adm-group-title">WORKSPACE</span>
      <ul class="adm-nav-list">
        <li>
          <a href="index.php" class="adm-nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('bar-chart', ['size' => 17, 'raw' => true]); ?></span>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="bookings.php" class="adm-nav-link <?php echo ($currentPage === 'bookings.php' || $currentPage === 'calendar.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('calendar', ['size' => 17, 'raw' => true]); ?></span>
            <span>Reservasi</span>
            <?php if ($pendingBookingsCount > 0): ?>
              <span class="adm-nav-badge badge-alert" title="<?php echo $pendingBookingsCount; ?> menunggu tinjauan"><?php echo $pendingBookingsCount; ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>
    </div>

    <!-- GROUP 2: KONTEN WEBSITE -->
    <div class="adm-nav-group">
      <span class="adm-group-title">KONTEN WEBSITE</span>
      <ul class="adm-nav-list">
        <li>
          <a href="events.php" class="adm-nav-link <?php echo ($currentPage === 'events.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('ticket', ['size' => 17, 'raw' => true]); ?></span>
            <span>Agenda &amp; Workshop</span>
            <?php if ($upcomingEventsCount > 0): ?>
              <span class="adm-nav-badge"><?php echo $upcomingEventsCount; ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a href="anatomy.php" class="adm-nav-link <?php echo ($currentPage === 'anatomy.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('bale', ['size' => 17, 'raw' => true]); ?></span>
            <span>Anatomi Bangunan</span>
          </a>
        </li>
        <li>
          <a href="portfolio.php" class="adm-nav-link <?php echo ($currentPage === 'portfolio.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('sparkles', ['size' => 17, 'raw' => true]); ?></span>
            <span>Portofolio Proyek</span>
          </a>
        </li>
        <li>
          <a href="gallery.php" class="adm-nav-link <?php echo ($currentPage === 'gallery.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('camera', ['size' => 17, 'raw' => true]); ?></span>
            <span>Galeri Event</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- GROUP 3: KONFIGURASI -->
    <div class="adm-nav-group">
      <span class="adm-group-title">KONFIGURASI</span>
      <ul class="adm-nav-list">
        <li>
          <a href="media.php" class="adm-nav-link <?php echo ($currentPage === 'media.php') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('camera', ['size' => 17, 'raw' => true]); ?></span>
            <span>Media</span>
          </a>
        </li>
        <li>
          <a href="settings.php?tab=contact" class="adm-nav-link <?php echo ($currentPage === 'settings.php' && $currentTab === 'contact') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('map-pin', ['size' => 17, 'raw' => true]); ?></span>
            <span>Kontak &amp; Lokasi</span>
          </a>
        </li>
        <li>
          <a href="settings.php" class="adm-nav-link <?php echo ($currentPage === 'settings.php' && $currentTab !== 'contact') ? 'active' : ''; ?>">
            <span class="adm-nav-icon"><?php echo ob_icon('settings', ['size' => 17, 'raw' => true]); ?></span>
            <span>Pengaturan Website</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Sidebar Footer User Profile & Public Site Link -->
  <div class="adm-sidebar-footer">
    <div class="adm-user-status">
      <div class="adm-user-avatar" aria-hidden="true">
        <?php echo strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)); ?>
      </div>
      <div class="adm-user-details">
        <div class="adm-user-name"><?php echo htmlspecialchars($currentUser['name'] ?? 'Admin'); ?></div>
        <div class="adm-user-role"><?php echo htmlspecialchars($currentUser['role'] ?? 'Super Admin'); ?></div>
      </div>
    </div>
    <a href="../index.php" target="_blank" rel="noopener noreferrer" class="adm-public-link">
      <span>Lihat Website Publik</span>
      <span aria-hidden="true">&nearr;</span>
    </a>
  </div>
</aside>

<!-- 2. MAIN CONTENT WRAPPER -->
<div class="adm-main-wrapper">
  <!-- Topbar Header -->
  <header class="adm-topbar">
    <div class="adm-topbar-left">
      <button type="button" class="adm-mobile-toggle" id="admMobileToggle" aria-label="Buka navigasi menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>
      <div>
        <h1 class="adm-topbar-title"><?php echo isset($adminTitle) ? htmlspecialchars($adminTitle) : 'Panel Administrasi'; ?></h1>
        <?php if (isset($adminBreadcrumb)): ?>
          <nav class="adm-breadcrumbs" aria-label="Breadcrumb">
            <?php echo $adminBreadcrumb; ?>
          </nav>
        <?php else: ?>
          <nav class="adm-breadcrumbs" aria-label="Breadcrumb">
            <a href="index.php">Admin</a> &rsaquo; <span><?php echo isset($adminTitle) ? htmlspecialchars($adminTitle) : 'Dasbor'; ?></span>
          </nav>
        <?php endif; ?>
      </div>
    </div>

    <div class="adm-topbar-right">
      <div class="adm-time-pill" title="Waktu Operasional Kawasan">
        <span class="adm-time-icon"><?php echo ob_icon('clock', ['size' => 14, 'color' => 'warm', 'raw' => true]); ?></span>
        <span>Sibang Kaja, Bali</span>
      </div>
      <a href="logout.php" class="adm-btn adm-btn-secondary adm-btn-sm" onclick="return confirm('Apakah Anda yakin ingin keluar dari sesi admin?');">
        <span>Keluar</span>
        <span aria-hidden="true">&rarr;</span>
      </a>
    </div>
  </header>

  <!-- Content Container -->
  <main class="adm-content-area">
    <?php 
    $flash = get_flash();
    if ($flash): 
      $alertClass = 'adm-alert-info';
      if ($flash['type'] === 'success') $alertClass = 'adm-alert-success';
      if ($flash['type'] === 'error') $alertClass = 'adm-alert-error';
      if ($flash['type'] === 'warning') $alertClass = 'adm-alert-warning';
    ?>
      <div class="adm-alert <?php echo $alertClass; ?>" role="alert">
        <span class="adm-alert-icon">
          <?php 
            if ($flash['type'] === 'success') echo ob_icon('check-circle', ['size' => 18, 'raw' => true]);
            elseif ($flash['type'] === 'error') echo ob_icon('alert-triangle', ['size' => 18, 'raw' => true]);
            else echo ob_icon('info', ['size' => 18, 'raw' => true]);
          ?>
        </span>
        <div class="adm-alert-text"><?php echo $flash['text']; ?></div>
      </div>
    <?php endif; ?>
