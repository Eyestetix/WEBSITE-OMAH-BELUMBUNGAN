# Omah Belumbungan — Living Balinese Heritage Architecture & Cultural Sanctuary

> **Living Cultural Compound & Traditional Balinese Architecture Archive**  
> Lokasi: Desa Sibang Kaja, Kecamatan Abiansemal, Kabupaten Badung, Bali.  
> *Dilandasi oleh filosofi Tri Hita Karana dan proporsi kosmologis Asta Kosala Kosali.*

---

## 🏛️ Tentang Proyek

**Omah Belumbungan** adalah platform web representatif dan arsip digital untuk kawasan rumah tradisional Bali hidup (*living heritage sanctuary*). Website ini dirancang dengan standar **Luxury Heritage Editorial Design**, memadukan estetika arsitektur Bali klasik dengan fungsionalitas web modern, cepat, dan aman tanpa ketergantungan library monolitik yang berat.

Platform ini mengintegrasikan profil kawasan, penjelajah anatomi pekarangan rumah Bali (*Sanga Mandala*), agenda lokakarya budaya, pameran galeri dokumentasi berkala, serta sistem pemesanan jadwal kunjungan edukasi dan reservasi pemotretan budaya.

---

## ✨ Fitur Utama

### 1. Pengalaman Publik (Public Experience)
- **Hero Slideshow Dinamis**: Latar belakang slideshow otomatis dengan efek crossfade lembut, jeda durasi yang dapat diatur fleksibel melalui CMS Admin.
- **Katalog Interaktif Anatomi Pekarangan Bali (`anatomy.php`)**: Penjelasan komprehensif elemen bangunan tradisional (*Angkul-Angkul, Aling-Aling, Natah, Bale Meten, Bale Dangin, Bale Dauh, Paon, Jineng, Pemerajan*) lengkap dengan filosofi orientasi mata angin (*kaja-kelod, kangin-kauh*) dan material alami.
- **Agenda & Lokakarya Budaya (`events.php`)**: Kalender sarasehan arsitektur, workshop kriya tradisional, dan penulisan lontar dengan countdown otomatis serta status kuota.
- **Portofolio Konservasi & Proyek (`portfolio.php`)**: Dokumentasi restorasi struktur saka kayu, ukiran paras keliki, dan penataan natah.
- **Galeri Dokumentasi Event (`gallery.php`)**: Dokumentasi fotografi beresolusi tinggi dengan sistem kurasi batch per kegiatan.
- **Formulir Reservasi Cerdas (`reservation.php`)**: Pendaftaran kunjungan sekolah, pemotretan, dan sarasehan dengan validasi otomatis terhadap **Hari Libur Adat / Hari Suci (Blackout Dates)** serta integrasi konfirmasi WhatsApp.

### 2. Panel CMS Admin Terpadu (`/admin`)
- **Dashboard Ringkasan & KPI**: Pantauan statistik reservasi masuk, agenda mendatang, dan verifikasi status instan (*Pending, Confirmed, Rejected*).
- **Pengelolaan Agenda & Workshop**: Tambah/edit acara, integrasi unggah poster foto dengan kompresi GD otomatis.
- **Pengelolaan Anatomi Bangunan**: Input elemen arsitektur, orientasi zona mandala, dan galeri visual.
- **Pustaka Media Terpusat (`admin/media.php`)**: Penjelajah berkas gambar terpadu dengan fitur salin path cepat, pratinjau resolusi, dan ukuran berkas.
- **Modal Pemilih Media Terpadu (`media_picker_modal.php`)**: Memilih foto yang sudah ada atau mengunggah foto baru langsung dari formulir tanpa berpindah halaman.
- **Pengaturan Kawasan & Sistem Keamanan (`admin/settings.php`)**:
  - Konfigurasi identitas kawasan, narasi profil, dan tautan media sosial resmi.
  - Pengaturan urutan slide hero beranda dan interval transisi.
  - Pengendalian kuota harian daya tampung ruang dan daftar tanggal libur adat (*blackout dates*).
  - Manajemen keamanan akun administrator & penggantian kata sandi berenkripsi BCRYPT.

---

## 🎨 Sistem Desain (Design System & Tokens)

Dibangun menggunakan arsitektur CSS modern berbasis **CSS Custom Properties (Design Tokens)**:

| Token | Nilai Baseline | Karakter Visual |
|---|---|---|
| `--color-forest` | `#1B3B2B` | Deep Forest Green — Keagungan & ketenangan alam Bali |
| `--color-terracotta` | `#9E472A` | Terracotta — Aksen bata merah tanah liat Tulikup |
| `--color-cream` | `#FDFBF7` | Warm Ivory / Cream — Latar belakang lembut & hangat |
| `--color-sand` | `#EFECE6` | Soft Sand Beige — Pemisah, border, dan kartu lembut |
| `--font-display` | `Playfair Display`, `Cormorant Garamond`, serif | Tipografi judul editorial, berwibawa, dan elegan |
| `--font-sans` | `Plus Jakarta Sans`, `Manrope`, sans-serif | Tipografi antarmuka, metadata, dan keterbacaan tinggi |

- **Bespoke SVG Icon System (`includes/icons.php`)**: Sistem ikon SVG monokromatik ringan berbasis vektor Lucide tanpa dependensi icon font pihak ketiga eksternal.

---

## 🛡️ Audit & Penguatan Keamanan Sistem (Security Hardening)

Aplikasi ini telah melalui audit keamanan menyeluruh dan dilengkapi proteksi berlapis:

1. **CSRF Protection (Cross-Site Request Forgery)**
   - Token kriptografi 256-bit (`random_bytes(32)`) diinjeksikan pada seluruh formulir administratif POST dan permintaan AJAX (`admin/ajax_upload_media.php`).
   - Validasi ketat menggunakan fungsi waktu konstan `hash_equals()`.

2. **Session Hardening & Proteksi Sesi**
   - Cookie sesi dikonfigurasi dengan flag `HttpOnly`, `SameSite=Lax`, dan `use_strict_mode=1`.
   - Pencegahan *Session Fixation* melalui `session_regenerate_id(true)` otomatis pada setiap login sukses.

3. **Anti Brute-Force Login Throttling**
   - Pembatasan maksimal 5 kali percobaan login gagal per IP/akun dalam jangka waktu 15 menit.
   - Penangguhan otomatis dengan pesan peringatan waktu tunggu sebelum percobaan berikutnya diizinkan.

4. **Proteksi Akses Data Sensitif (Direct File Access Denied)**
   - Seluruh berkas penyimpanan flat-file JSON di `/data/*.json` dan pustaka `/includes/` diblokir dari akses web langsung via Apache `.htaccess` (`Require all denied` / Apache 2.2 compat) serta aturan rewrite root.

5. **Pipa Unggah Berkas Aman (Secure File Upload)**
   - Validasi ketat format MIME type asli melalui PHP `finfo_open(FILEINFO_MIME_TYPE)` (hanya mengizinkan `image/jpeg`, `image/png`, `image/webp`).
   - Dekode dan rekompresi gambar ulang menggunakan PHP GD (`imagecreatefrom...` & `imagewebp`/`imagejpeg`) guna melucuti muatan jahat (Polyglot/steganography web shell).
   - Pengacakan nama berkas secara kriptografis (`bin2hex(random_bytes(10))`) dan peletakan dalam folder terisolasi.
   - Direktori `/uploads/.htaccess` melarang eksekusi segala jenis skrip (`php`, `phtml`, `phar`, `py`, `pl`, `cgi`) dan menonaktifkan directory indexing.

6. **Anti-Spam Form Honeypot**
   - Formulir reservasi publik dilengkapi input perangkap tak kasat mata (`website_url_hp`) untuk menjebak bot otomasi tanpa mengganggu pengalaman pengguna manusia.

7. **HTTP Security Headers**
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN` (Pencegahan Clickjacking)
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `Permissions-Policy: geolocation=(), camera=(), microphone=()`
   - Penghapusan header `X-Powered-By` untuk meminimalisasi fingerprinting teknologi server.

---

## 🚀 Panduan Menjalankan Secara Lokal (Local Setup)

### Kebutuhan Lingkungan:
- **PHP 8.0 atau lebih baru** (disarankan PHP 8.1 - 8.5)
- Ekstensi PHP wajib:
  - `php-json`
  - `php-gd` (untuk optimasi gambar & upload)
  - `php-session`

### Langkah Menjalankan:
1. Kloning atau unduh repositori ini ke komputer lokal Anda:
   ```bash
   git clone https://github.com/Eyestetix/WEBSITE-OMAH-BELUMBUNGAN.git
   cd WEBSITE-OMAH-BELUMBUNGAN
   ```

2. Jalankan server lokal bawaan PHP:
   ```bash
   php -S localhost:8000
   ```

3. Buka peramban (browser) dan akses alamat:
   - **Halaman Utama Publik**: `http://localhost:8000`
   - **Katalog Anatomi Rumah Bali**: `http://localhost:8000/anatomy.php`
   - **Agenda & Lokakarya**: `http://localhost:8000/events.php`
   - **Reservasi Kunjungan**: `http://localhost:8000/reservation.php`
   - **Portal Admin CMS**: `http://localhost:8000/admin/login.php`

---

## 🔑 Kredensial Pengujian Admin (Code Review Only)

Untuk kemudahan peninjauan kode (*code review*), kredensial akun administrator awal adalah sebagai berikut:

- **URL Login**: `http://localhost:8000/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

> [!NOTE]
> Setelah peninjauan kode selesai, kata sandi dapat diubah sewaktu-waktu melalui menu **Pengaturan Website &rsaquo; Keamanan &amp; Akun** (`/admin/settings.php?tab=security`).

---

## 📁 Struktur Direktori Repositori

```text
├── about.php                  # Halaman Narasi Filosofi Tri Hita Karana & Nilai Kawasan
├── anatomy.php                # Penjelajah Interaktif Anatomi Pekarangan Bali (Sanga Mandala)
├── anatomi/                   # Alias routing anatomi
├── contact.php                # Kontak, Denah Lokasi, & Navigasi Google Maps
├── events.php                 # Kalender Agenda, Sarasehan, & Lokakarya Terbuka
├── gallery.php                # Dokumentasi Fotografi Kawasan & Batch Event
├── index.php                  # Beranda Utama (Hero Slideshow, Sambutan, & Highlight)
├── portfolio.php              # Portofolio Restorasi Arsitektur Tradisional
├── reservation.php            # Formulir Pengajuan Jadwal Kunjungan & Proteksi Honeypot
├── .htaccess                  # Konfigurasi Apache, Security Headers, & Route Protection
├── .gitignore                 # Filter berkas OS (.DS_Store), cache, dan berkas log
│
├── admin/                     # Dashboard CMS Admin Terpadu
│   ├── index.php              # Dashboard KPI & Antrean Reservasi Masuk
│   ├── login.php              # Otentikasi Admin dengan Rate Limiting & Proteksi CSRF
│   ├── logout.php             # Terminasi Sesi Bersih
│   ├── events.php             # Manajemen Agenda & Workshop Kebudayaan
│   ├── anatomy.php            # Manajemen Elemen Struktur Arsitektur Bali
│   ├── portfolio.php          # Manajemen Portofolio & Proyek Restorasi
│   ├── gallery.php            # Manajemen Dokumentasi Batch Galeri
│   ├── bookings.php           # Manajemen Reservasi & Validasi Jadwal
│   ├── media.php              # Pustaka Media Terpusat
│   ├── settings.php           # Pengaturan Identitas, Slideshow, Libur Adat, & Ganti Password
│   ├── ajax_upload_media.php  # Endpoint Unggah AJAX Terisolasi & Validasi CSRF
│   ├── media_picker_modal.php # Komponen Modal Reusable Pemilih & Pengunggah Media
│   ├── header.php             # Navigasi Bar & Status Bar Admin
│   └── footer.php             # Penutup Layout Admin
│
├── assets/                    # Aset Publik
│   ├── css/
│   │   ├── style.css          # Desain Sistem & Stylesheet Website Publik
│   │   └── admin.css          # Desain Sistem & Antarmuka Panel Admin CMS
│   └── images/                # Aset Gambar Statis Kawasan & Brand Logo
│
├── data/                      # Penyimpanan Flat-File JSON (Terproteksi dari Web Langsung)
│   ├── .htaccess              # Aturan Blokir Mutlak (Require all denied)
│   ├── settings.json          # Identitas Situs, Durasi Slideshow, & Kontak
│   ├── users.json             # Kredensial Administrator (Password Hash BCRYPT)
│   ├── anatomy.json           # Master Data Anatomi Bangunan Rumah Bali
│   ├── events.json            # Agenda & Jadwal Lokakarya
│   ├── portfolio.json         # Dokumentasi Portofolio Konservasi
│   ├── gallery.json           # Dokumentasi Galeri Kegiatan
│   └── bookings.json          # Antrean & Riwayat Reservasi Kunjungan
│
├── includes/                  # Kernel Logika & Komponen Modular
│   ├── auth.php               # Modul Sesi, CSRF Token, & Throttling
│   ├── functions.php          # Engine JSON, Validasi Upload Gambar, & Security Headers
│   ├── icons.php              # Sistem Ikon Vektor SVG Mandiri
│   ├── header.php             # Header Navigasi Publik
│   └── footer.php             # Footer & Navigasi Peta Publik
│
└── uploads/                   # Direktori Berkas Unggahan Dinamis
    ├── .htaccess              # Blokir Eksekusi Skrip PHP/CGI (Options -ExecCGI)
    ├── events/                # Foto Poster Acara
    ├── gallery/               # Foto Dokumentasi Galeri
    ├── portfolio/             # Foto Portofolio Proyek
    ├── anatomy/               # Foto Detail Bangunan
    └── hero/                  # Foto Background Hero Slideshow
```

---

## 📝 Panduan Peninjauan Kode (Code Review Checklist)

Bagi rekan pereview (*reviewers*), berikut adalah titik pemeriksaan utama yang disarankan:

1. **Security & Session Management**:
   - Periksa implementasi CSRF dan rate limiting di [`includes/auth.php`](includes/auth.php).
   - Periksa penanganan upload aman dengan pembersihan GD di [`includes/functions.php`](includes/functions.php#L320).
   - Periksa aturan proteksi folder data di [`data/.htaccess`](data/.htaccess) dan [`uploads/.htaccess`](uploads/.htaccess).
2. **Design System & Typography**:
   - Periksa definisi design tokens warna, tipografi, dan spasi di [`assets/css/style.css`](assets/css/style.css) dan [`assets/css/admin.css`](assets/css/admin.css).
   - Periksa performa render SVG vector icons di [`includes/icons.php`](includes/icons.php).
3. **Visitor Flow & Honeypot**:
   - Periksa integrasi validasi blackout dates dan honeypot di [`reservation.php`](reservation.php).

---

## 📜 Hak Cipta & Lisensi

&copy; 2026 **Omah Belumbungan**. Seluruh materi budaya, filosofi arsitektur, dan aset visual dilindungi hak cipta untuk tujuan pelestarian warisan budaya Bali di Sibang Kaja, Badung, Bali.
