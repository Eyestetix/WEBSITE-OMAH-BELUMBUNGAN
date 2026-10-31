<?php
/**
 * OMAH BELUMBUNGAN - AJAX Single Media Upload Endpoint
 * Handles secure async uploads for single image fields across Admin CMS
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

// Verifikasi sesi login administrator
if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Akses ditolak. Sesi administrator tidak aktif.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metode permintaan tidak valid.']);
    exit;
}

// Verifikasi CSRF token
$csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!verify_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token keamanan (CSRF) tidak valid atau sesi berakhir.']);
    exit;
}

$fileKey = isset($_FILES['image']) ? 'image' : (isset($_FILES['media_file']) ? 'media_file' : null);

if (!$fileKey || !isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $errCode = $fileKey ? ($_FILES[$fileKey]['error'] ?? 'unknown') : 'no_file';
    echo json_encode(['success' => false, 'error' => 'Berkas gambar tidak terkirim dengan benar (Kode: ' . $errCode . ').']);
    exit;
}

$tmpPath = $_FILES[$fileKey]['tmp_name'];
$origName = $_FILES[$fileKey]['name'];
$fileSize = $_FILES[$fileKey]['size'];

// Batas ukuran per file 15MB
if ($fileSize > 15 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ukuran berkas melebihi 15MB.']);
    exit;
}

// Folder tujuan yang diizinkan
$folder = sanitize($_POST['folder'] ?? 'events');
$allowedFolders = ['events', 'gallery', 'portfolio', 'general', 'anatomy'];
if (!in_array($folder, $allowedFolders, true)) {
    $folder = 'events';
}

$uploadResult = upload_image_secure($tmpPath, $origName, $folder);

if (!$uploadResult['success']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
    exit;
}

echo json_encode([
    'success' => true,
    'path' => $uploadResult['path'],
    'thumb' => $uploadResult['thumb'] ?? $uploadResult['path'],
    'filename' => htmlspecialchars($origName),
    'size' => $fileSize
]);
