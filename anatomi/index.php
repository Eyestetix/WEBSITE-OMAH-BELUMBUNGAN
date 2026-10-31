<?php
/**
 * Omah Belumbungan — Anatomi Routing Wrapper
 * Mendukung URL pattern /anatomi/{slug} maupun /anatomi/?id={slug}
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);

$targetSlug = '';
// Jika ada segment setelah 'anatomi', misalnya 'anatomi/angkul-angkul'
$anatomiIndex = array_search('anatomi', $segments);
if ($anatomiIndex !== false && isset($segments[$anatomiIndex + 1]) && !empty($segments[$anatomiIndex + 1])) {
    $targetSlug = $segments[$anatomiIndex + 1];
}

if (empty($targetSlug)) {
    $targetSlug = $_GET['id'] ?? $_GET['slug'] ?? '';
}

if (!empty($targetSlug)) {
    $_GET['id'] = $targetSlug;
}

require_once __DIR__ . '/../anatomy.php';
