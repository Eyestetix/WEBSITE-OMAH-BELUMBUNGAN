<?php
/**
 * OMAH BELUMBUNGAN - Helper Functions & JSON Flat-File Engine
 * Lokasi: Sibang Kaja, Abiansemal, Badung, Bali
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('DATA_PATH')) {
    define('DATA_PATH', BASE_PATH . '/data');
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', BASE_PATH . '/uploads');
}

// Load Premium SVG Icon System
require_once __DIR__ . '/icons.php';

// Security Headers (SEC-06) - PHP fallback layer
if (!headers_sent() && php_sapi_name() !== 'cli') {
    if (function_exists('header_remove')) {
        header_remove('X-Powered-By');
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
}

/**
 * Membaca file JSON dengan aman
 */
function get_json_data($filename) {
    $filePath = DATA_PATH . '/' . ltrim($filename, '/');
    if (!file_exists($filePath)) {
        return [];
    }
    $content = file_get_contents($filePath);
    if ($content === false || trim($content) === '') {
        return [];
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Menyimpan data ke file JSON dengan File Locking (LOCK_EX)
 */
function save_json_data($filename, $data) {
    if (!is_dir(DATA_PATH)) {
        mkdir(DATA_PATH, 0755, true);
    }
    $filePath = DATA_PATH . '/' . ltrim($filename, '/');
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Tulis menggunakan atomic lock agar tidak rusak saat concurrent request
    return file_put_contents($filePath, $jsonData, LOCK_EX) !== false;
}

/**
 * Ambil konfigurasi website
 */
function get_settings() {
    return get_json_data('settings.json');
}

/**
 * Ambil daftar event publik (Agenda & Workshop)
 */
function get_events($status = null) {
    $events = get_json_data('events.json');
    if ($status === null) {
        return $events;
    }
    return array_values(array_filter($events, function($e) use ($status) {
        return isset($e['status']) && $e['status'] === $status;
    }));
}

/**
 * Ambil daftar reservasi
 */
function get_bookings($status = null) {
    $bookings = get_json_data('bookings.json');
    if ($status === null) {
        return $bookings;
    }
    return array_values(array_filter($bookings, function($b) use ($status) {
        return isset($b['status']) && $b['status'] === $status;
    }));
}

/**
 * Ambil data anatomi arsitektur Bali
 */
/**
 * Ambil data Anatomi Bangunan Tradisional (opsional filter status published)
 */
function get_anatomy($onlyPublished = false) {
    $anatomy = get_json_data('anatomy.json');
    if ($onlyPublished) {
        $anatomy = array_values(array_filter($anatomy, function($item) {
            return ($item['status'] ?? 'published') === 'published';
        }));
    }
    usort($anatomy, function($a, $b) {
        return ($a['sort_order'] ?? 99) <=> ($b['sort_order'] ?? 99);
    });
    return $anatomy;
}

/**
 * Cari satu elemen anatomi berdasarkan ID atau slug secara fleksibel
 */
function get_anatomy_by_identifier($identifier) {
    if (empty($identifier)) return null;
    $list = get_anatomy(false);
    $normalized = strtolower(trim($identifier));
    $dashVersion = str_replace('_', '-', $normalized);
    $underVersion = str_replace('-', '_', $normalized);

    foreach ($list as $item) {
        $itemId = strtolower($item['id'] ?? '');
        $itemSlug = strtolower($item['slug'] ?? '');
        if ($itemId === $normalized || $itemId === $underVersion || $itemId === $dashVersion) {
            return $item;
        }
        if (!empty($itemSlug) && ($itemSlug === $normalized || $itemSlug === $dashVersion || $itemSlug === $underVersion)) {
            return $item;
        }
    }
    return null;
}

/**
 * Ambil elemen sebelumnya dan berikutnya untuk navigasi detail anatomi
 */
function get_anatomy_adjacent($currentId) {
    $list = get_anatomy(true);
    $total = count($list);
    $targetIndex = -1;

    $normalized = strtolower(trim($currentId));
    $dashVersion = str_replace('_', '-', $normalized);
    $underVersion = str_replace('-', '_', $normalized);

    foreach ($list as $idx => $item) {
        $itemId = strtolower($item['id'] ?? '');
        $itemSlug = strtolower($item['slug'] ?? '');
        if ($itemId === $normalized || $itemId === $underVersion || $itemId === $dashVersion ||
            (!empty($itemSlug) && ($itemSlug === $normalized || $itemSlug === $dashVersion))) {
            $targetIndex = $idx;
            break;
        }
    }

    if ($targetIndex === -1) {
        return ['prev' => null, 'next' => null, 'index' => 0, 'total' => $total, 'current' => null];
    }

    $prev = ($targetIndex > 0) ? $list[$targetIndex - 1] : null;
    $next = ($targetIndex < $total - 1) ? $list[$targetIndex + 1] : null;

    return [
        'prev' => $prev,
        'next' => $next,
        'index' => $targetIndex + 1,
        'total' => $total,
        'current' => $list[$targetIndex]
    ];
}

/**
 * Ambil data Portfolio Proyek
 */
function get_portfolio($status = null) {
    $projects = get_json_data('portfolio.json');
    if ($status !== null) {
        $projects = array_values(array_filter($projects, function($p) use ($status) {
            return isset($p['status']) && $p['status'] === $status;
        }));
    }
    // Urutkan featured di depan, lalu berdasarkan created_at desc
    usort($projects, function($a, $b) {
        if (!empty($a['is_featured']) && empty($b['is_featured'])) return -1;
        if (empty($a['is_featured']) && !empty($b['is_featured'])) return 1;
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
    });
    return $projects;
}

/**
 * Ambil data Gallery Event (Dokumentasi Kegiatan Selesai)
 */
function get_gallery_events($status = null) {
    $galleries = get_json_data('gallery.json');
    if ($status !== null) {
        $galleries = array_values(array_filter($galleries, function($g) use ($status) {
            return isset($g['status']) && $g['status'] === $status;
        }));
    }
    // Urutkan berdasarkan tanggal kegiatan terbaru (newest first)
    usort($galleries, function($a, $b) {
        return strcmp($b['event_date'] ?? '', $a['event_date'] ?? '');
    });
    return $galleries;
}

/**
 * Upload dan optimasi gambar tunggal secara aman
 */
function upload_image_secure($fileTmp, $fileName, $subfolder = 'gallery') {
    $targetDir = UPLOAD_PATH . '/' . trim($subfolder, '/') . '/';
    $thumbDir = $targetDir . 'thumbs/';

    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

    // Validasi ekstensi dan tipe MIME
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExts, true)) {
        return ['success' => false, 'error' => 'Format file tidak didukung (gunakan JPG, PNG, atau WEBP).'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fileTmp);
    if (PHP_VERSION_ID < 80500 && is_resource($finfo)) {
        @finfo_close($finfo);
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        return ['success' => false, 'error' => 'Tipe file tidak valid.'];
    }

    // Nama file unik
    $cleanName = preg_replace('/[^a-z0-9_-]/i', '', pathinfo($fileName, PATHINFO_FILENAME));
    $uniqueName = strtolower(substr($cleanName, 0, 24)) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $targetPath = $targetDir . $uniqueName;
    $thumbPath = $thumbDir . $uniqueName;

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        return ['success' => false, 'error' => 'Gagal memindahkan berkas unggahan ke folder tujuan.'];
    }

    // Buat thumbnail optimal dengan GD
    create_image_thumbnail($targetPath, $thumbPath, 640, 480);

    $relPath = 'uploads/' . trim($subfolder, '/') . '/' . $uniqueName;
    return ['success' => true, 'path' => $relPath, 'thumb' => 'uploads/' . trim($subfolder, '/') . '/thumbs/' . $uniqueName];
}

/**
 * Pembuatan thumbnail dengan proporsi terjaga menggunakan PHP GD
 */
function create_image_thumbnail($src, $dest, $maxWidth = 640, $maxHeight = 480) {
    if (!file_exists($src)) return false;
    
    $info = @getimagesize($src);
    if (!$info) return false;

    $width = $info[0];
    $height = $info[1];
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($src);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($src);
            break;
        case 'image/webp':
            $image = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : null;
            break;
        default:
            $image = null;
    }

    if (!$image) return false;

    // Hitung dimensi thumbnail
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    if ($ratio >= 1) {
        // Gambar sudah lebih kecil dari thumbnail, salin saja
        if (PHP_VERSION_ID < 80500 && is_resource($image)) {
            @imagedestroy($image);
        }
        return copy($src, $dest);
    }

    $newWidth = (int)round($width * $ratio);
    $newHeight = (int)round($height * $ratio);

    $thumb = imagecreatetruecolor($newWidth, $newHeight);

    // Penanganan transparansi PNG/WEBP
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumb, $dest, 85);
            break;
        case 'image/png':
            imagepng($thumb, $dest, 7);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                imagewebp($thumb, $dest, 85);
            } else {
                imagejpeg($thumb, $dest, 85);
            }
            break;
    }

    if (PHP_VERSION_ID < 80500) {
        if (is_resource($image)) @imagedestroy($image);
        if (is_resource($thumb)) @imagedestroy($thumb);
    }
    return true;
}

/**
 * Dapatkan seluruh berkas media dari direktori images dan uploads
 */
function get_all_media_files() {
    $baseDir = dirname(__DIR__);
    $directories = [
        'Aset Utama'     => $baseDir . '/assets/images',
        'Galeri Event'   => $baseDir . '/uploads/gallery',
        'Portofolio'     => $baseDir . '/uploads/portfolio',
        'Event & Edukasi'=> $baseDir . '/uploads/events',
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

            $mediaFiles[] = [
                'filename' => $f,
                'rel_path' => $relPath,
                'category' => $catName,
                'ext' => $ext,
                'size' => $sizeBytes,
                'size_formatted' => ($sizeBytes > 1048576) ? (round($sizeBytes / 1048576, 2) . ' MB') : (round($sizeBytes / 1024, 1) . ' KB'),
                'mod_time' => $modTime
            ];
        }
    }

    usort($mediaFiles, function($a, $b) {
        return $b['mod_time'] <=> $a['mod_time'];
    });

    return $mediaFiles;
}

/**
 * Format tanggal Indonesia lengkap dengan nama hari
 */
function format_date_id($dateStr, $withDay = true) {
    if (empty($dateStr)) return '-';
    $time = strtotime($dateStr);
    if (!$time) return $dateStr;

    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $dayName = $days[date('l', $time)] ?? '';
    $day = date('j', $time);
    $month = $months[(int)date('n', $time)] ?? '';
    $year = date('Y', $time);

    if ($withDay) {
        return "{$dayName}, {$day} {$month} {$year}";
    }
    return "{$day} {$month} {$year}";
}

/**
 * Format mata uang Rupiah
 */
function format_rupiah($number) {
    if (!is_numeric($number)) return $number;
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Bersihkan input data form untuk mencegah XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Periksa apakah tanggal bertepatan dengan Blackout Date (Libur/Adat)
 */
function is_blackout_date($dateStr) {
    $settings = get_settings();
    $blackouts = $settings['blackout_dates'] ?? [];
    return in_array($dateStr, $blackouts, true);
}

/**
 * Generate Kode Unik Reservasi (Contoh: BKG-202611-X4K2)
 */
function generate_booking_id() {
    $prefix = 'BKG-' . date('Ym') . '-';
    $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
    return $prefix . $random;
}

/**
 * Buat Tautan WhatsApp Terformat Otomatis
 */
function make_whatsapp_url($phone, $message) {
    // Normalisasi nomor telepon ke format 62xxx
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($cleanPhone, 0, 1) === '0') {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }
    return 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . urlencode($message);
}

/**
 * Flash Message Handler (Notifikasi Alert Sesi)
 */
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'text' => $message
    ];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}
