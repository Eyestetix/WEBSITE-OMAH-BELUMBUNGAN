<?php
/**
 * OMAH BELUMBUNGAN - Footer Component
 * Forest Green Theme with Cultural Motif
 */
$waNumber = $settings['whatsapp'] ?? '6281234567890';
$waLink = make_whatsapp_url($waNumber, "Om Swastyastu, salam hangat dari website Omah Blumbungan. Saya ingin bertanya seputar jadwal kunjungan/workshop arsitektur.");
?>

<footer class="site-footer">
  <div class="container footer-grid">
    <!-- Brand & Mission Column -->
    <div class="footer-brand-col">
      <a href="index.php" class="footer-logo" aria-label="Beranda Omah Blumbungan">
        <img src="assets/images/LogoOmah.png" alt="Omah Blumbungan Logo" width="60" height="54" class="footer-logo-img" style="filter: brightness(0) invert(1); opacity: 0.95;">
      </a>
      <p class="footer-mission">
        Ruang belajar dan sanggraloka budaya untuk menjaga warisan arsitektur Bali agar tetap hidup bagi generasi mendatang.
      </p>
      <div class="footer-meta-pill">
        <span>Kawasan Pelestarian Budaya Sibang Kaja</span>
      </div>
    </div>

    <!-- Navigation Column -->
    <div class="footer-col">
      <h4 class="footer-heading">Menu Utama</h4>
      <ul class="footer-links">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="events.php">Agenda &amp; Workshop</a></li>
        <li><a href="portfolio.php">Portfolio Proyek</a></li>
        <li><a href="gallery.php">Gallery Event</a></li>
        <li><a href="about.php">Tentang Kami</a></li>
        <li><a href="reservation.php">Reservasi Kunjungan</a></li>
        <li><a href="contact.php">Kontak &amp; Lokasi</a></li>
      </ul>
    </div>

    <!-- Contact Column -->
    <div class="footer-col">
      <h4 class="footer-heading">Kontak</h4>
      <ul class="footer-contact-list">
        <li>
          <span class="contact-icon"><?php echo ob_icon('map-pin', ['size' => 16, 'color' => 'light', 'raw' => true]); ?></span>
          <span><?php echo htmlspecialchars($settings['address'] ?? 'Sibang Kaja, Abiansemal, Bali'); ?></span>
        </li>
        <li>
          <span class="contact-icon"><?php echo ob_icon('phone', ['size' => 16, 'color' => 'light', 'raw' => true]); ?></span>
          <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $waNumber); ?>" target="_blank" rel="noopener noreferrer">
            +62 812 3456 7890
          </a>
        </li>
        <li>
          <span class="contact-icon"><?php echo ob_icon('mail', ['size' => 16, 'color' => 'light', 'raw' => true]); ?></span>
          <a href="mailto:<?php echo htmlspecialchars($settings['email'] ?? 'omah.blumbungan@gmail.com'); ?>">
            <?php echo htmlspecialchars($settings['email'] ?? 'omah.blumbungan@gmail.com'); ?>
          </a>
        </li>
        <li>
          <span class="contact-icon"><?php echo ob_icon('clock', ['size' => 16, 'color' => 'light', 'raw' => true]); ?></span>
          <span><?php echo htmlspecialchars($settings['opening_hours'] ?? 'Buka setiap hari (berdasarkan jadwal)'); ?></span>
        </li>
      </ul>
    </div>

    <!-- Social Media & Culture Column -->
    <div class="footer-col">
      <h4 class="footer-heading">Ikuti Kami</h4>
      <p class="footer-social-desc">
        Ikuti dokumentasi aktivitas, arsip visual, dan sarasehan budaya terkini kami:
      </p>
      <div class="footer-socials">
        <a href="<?php echo htmlspecialchars($settings['instagram'] ?? 'https://instagram.com/omahblumbungan'); ?>" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Instagram Omah Blumbungan">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
          </svg>
        </a>
        <a href="<?php echo htmlspecialchars($settings['youtube'] ?? 'https://youtube.com/@omahblumbungan'); ?>" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="YouTube Omah Blumbungan">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
          </svg>
        </a>
        <a href="<?php echo htmlspecialchars($settings['facebook'] ?? 'https://facebook.com/omahblumbungan'); ?>" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Facebook Omah Blumbungan">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>

  <div class="container footer-bottom">
    <div class="footer-copy">
      &copy; <?php echo date('Y'); ?> <strong>Omah Belumbungan</strong>. Seluruh Hak Cipta Dilindungi. &bull; <a href="admin/login.php" style="color: inherit; text-decoration: none; opacity: 0.65; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.65'">Panel Pengelola</a>
    </div>
    <div class="footer-location-tag">
      Desa Sibang Kaja, Kecamatan Abiansemal, Kabupaten Badung, Bali — 80352
    </div>
  </div>
</footer>

<!-- Floating WhatsApp CTA -->
<a href="<?php echo htmlspecialchars($waLink); ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-float" aria-label="Hubungi kami melalui WhatsApp">
  <span class="wa-icon">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.053-1.077-.089-.47-.151-1.077-.42-1.85-1.089-.99-.857-1.636-1.923-1.884-2.349-.247-.426-.027-.656.12-.862.13-.182.288-.376.432-.564.116-.151.154-.257.23-.427.078-.171.039-.32-.02-.439-.058-.119-.526-1.268-.72-1.737-.189-.456-.381-.394-.526-.402-.135-.007-.289-.009-.443-.009-.154 0-.404.058-.615.289-.211.231-.808.789-.808 1.923s.827 2.231.942 2.385c.115.154 1.627 2.485 3.942 3.484 2.316.999 2.316.666 2.738.627.423-.039 1.365-.558 1.558-1.097.192-.539.192-1.001.135-1.098-.058-.096-.212-.154-.442-.269z"/>
      <path d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.661 1.435 5.176L2 22l4.981-1.393A9.957 9.957 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.062a8.04 8.04 0 0 1-4.328-1.258l-.31-.186-2.955.827.838-2.883-.203-.324A8.026 8.026 0 0 1 3.938 12c0-4.446 3.616-8.062 8.062-8.062s8.062 3.616 8.062 8.062-3.616 8.062-8.062 8.062z"/>
    </svg>
  </span>
  <span class="wa-text">Tanya via WhatsApp</span>
</a>

<!-- Anatomy Element Detail Modal -->
<div class="modal-overlay" id="anatomyModal" role="dialog" aria-modal="true" aria-labelledby="modalAnatomyTitle">
  <div class="modal-content modal-editorial">
    <button class="modal-close" id="modalAnatomyClose" aria-label="Tutup jendela rincian">&times;</button>
    <div class="modal-body-grid">
      <div class="modal-img-wrap">
        <img src="assets/images/photo-01.jpg" alt="Foto Elemen Arsitektur" id="modalAnatomyImg" class="modal-img">
        <div class="modal-order-tag" id="modalAnatomyOrder">#01</div>
      </div>
      <div class="modal-text-content">
        <span class="section-tag modal-zone-tag" id="modalAnatomyZone">UTAMA / GERBANG UTAMA</span>
        <h3 id="modalAnatomyTitle" class="modal-title">Angkul-angkul</h3>
        <p id="modalAnatomyShort" class="modal-subtitle">Gerbang utama tradisional dengan batas selebar depa pemilik rumah.</p>
        
        <!-- Structured Information Panel (Stacked Label & Description) -->
        <div class="modal-meta-box">
          <div class="meta-group-item">
            <h4 class="meta-group-label">Orientasi &amp; Letak</h4>
            <p class="meta-group-val" id="modalAnatomyOrientation">Akses Masuk Depan</p>
          </div>
          <div class="meta-group-item">
            <h4 class="meta-group-label">Fungsi Utama</h4>
            <p class="meta-group-val" id="modalAnatomyFunction">Akses sirkulasi utama keluar-masuk pekarangan sekaligus lambang keramahan dan penyambutan.</p>
          </div>
          <div class="meta-group-item">
            <h4 class="meta-group-label">Material &amp; Karakter</h4>
            <p class="meta-group-val" id="modalAnatomyMaterials">Bata merah gosok, batu paras kelating/tulikup, kayu jati/kamper, dan atap alang-alang.</p>
          </div>
        </div>

        <!-- Philosophy Section -->
        <div class="modal-philosophy">
          <h4 class="philosophy-label">Makna Filosofis &amp; Tri Hita Karana</h4>
          <p id="modalAnatomyPhilosophy">Gerbang utama tradisional Bali beratap alang-alang atau genteng dengan ukiran batu padas dan bata merah. Memiliki batas lebar selebar depa (rentang tangan) pemilik rumah menurut ukuran asta kosala kosali.</p>
        </div>

        <!-- Dynamic Action Button: Links to Element's Detail Route -->
        <div class="modal-action-row">
          <a href="about.php#angkul_angkul" id="modalAnatomyLink" class="btn btn-terracotta btn-anatomy-cta">
            <span>Selengkapnya</span>
            <span class="btn-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search Overlay Modal -->
<div class="modal-overlay" id="searchModal" role="dialog" aria-modal="true" aria-label="Pencarian Cepat">
  <div class="modal-content modal-search-box">
    <button class="modal-close" id="searchModalClose" aria-label="Tutup pencarian">&times;</button>
    <h3 class="search-modal-title">Pencarian Arsip Arsitektur & Agenda</h3>
    <p class="search-modal-desc">Ketik nama elemen rumah (misal: <em>Angkul-angkul</em>, <em>Natah</em>) atau topik workshop.</p>
    <div class="search-input-wrap">
      <input type="text" id="siteSearchInput" placeholder="Cari elemen arsitektur, filosofi, atau kegiatan..." autocomplete="off">
    </div>
    <div class="search-results-list" id="searchResultsList">
      <!-- Hasil pencarian dinamis diisi JS -->
    </div>
  </div>
</div>

<!-- Blackout Dates and Core JS -->
<script>
  window.omahBlackouts = <?php echo json_encode($settings['blackout_dates'] ?? []); ?>;
</script>
<script src="assets/js/main.js"></script>
</body>
</html>
