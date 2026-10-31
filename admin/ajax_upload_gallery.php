<?php
/**
 * OMAH BELUMBUNGAN - AJAX Batch Photo Upload Endpoint
 * Handles async drag-and-drop uploads up to 10 photos per event gallery
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

$currentCount = (int)($_POST['current_count'] ?? 0);
if ($currentCount >= 10) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Batas maksimal 10 foto untuk setiap event telah tercapai. Tidak dapat menambah foto lagi.'
    ]);
    exit;
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $errCode = $_FILES['photo']['error'] ?? 'no_file';
    echo json_encode(['success' => false, 'error' => 'Berkas tidak terkirim dengan benar (Kode: ' . $errCode . ').']);
    exit;
}

$tmpPath = $_FILES['photo']['tmp_name'];
$origName = $_FILES['photo']['name'];
$fileSize = $_FILES['photo']['size'];

// Batas ukuran per file 10MB
if ($fileSize > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ukuran berkas melebihi 10MB.']);
    exit;
}

$uploadResult = upload_image_secure($tmpPath, $origName, 'gallery');

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
