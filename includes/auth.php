<?php
/**
 * OMAH BELUMBUNGAN - Autentikasi Admin, Keamanan Sesi & Proteksi CSRF
 * Location: Sibang Kaja, Abiansemal, Badung, Bali
 */

require_once __DIR__ . '/functions.php';

/**
 * Inisialisasi sesi dengan standar keamanan modern (HttpOnly, SameSite, Strict Mode)
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }
}

/**
 * Periksa apakah pengguna saat ini telah terautentikasi sebagai admin
 */
function is_logged_in() {
    init_session();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Guard proteksi rute halaman admin
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Dapatkan data administrator yang sedang aktif
 */
function get_logged_in_user() {
    init_session();
    return $_SESSION['admin_user'] ?? null;
}

/**
 * ==============================================================================
 * SISTEM KEAMANAN CSRF (CROSS-SITE REQUEST FORGERY)
 * ==============================================================================
 */

/**
 * Generate atau ambil token CSRF unik untuk sesi aktif
 */
function generate_csrf_token() {
    init_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token() {
    return generate_csrf_token();
}

/**
 * Kembalikan tag input HTML hidden berisi token CSRF
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifikasi kecocokan token CSRF
 */
function verify_csrf_token($token) {
    init_session();
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Wajibkan token CSRF valid untuk setiap permintaan POST berbahaya
 */
function require_csrf_token() {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!verify_csrf_token($token)) {
        http_response_code(400);
        set_flash('error', 'Permintaan ditolak: Sesi kedaluwarsa atau token keamanan (CSRF) tidak valid. Silakan coba kembali.');
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header('Location: ' . $referer);
        exit;
    }
}

/**
 * ==============================================================================
 * RATE LIMITING / BRUTE FORCE PROTECTION UNTUK LOGIN ADMIN
 * ==============================================================================
 */
function check_login_throttle() {
    init_session();
    $attempts = $_SESSION['login_fail_count'] ?? 0;
    $lockTime = $_SESSION['login_lock_time'] ?? 0;
    $now = time();

    if ($attempts >= 5) {
        $elapsed = $now - $lockTime;
        $cooldown = 15 * 60; // 15 menit
        if ($elapsed < $cooldown) {
            $remaining = ceil(($cooldown - $elapsed) / 60);
            return "Terlalu banyak percobaan masuk yang gagal. Akses terkunci sementara. Silakan coba lagi dalam {$remaining} menit.";
        } else {
            // Masa penalti selesai, reset
            $_SESSION['login_fail_count'] = 0;
            unset($_SESSION['login_lock_time']);
        }
    }
    return null;
}

function record_failed_login() {
    init_session();
    $attempts = ($_SESSION['login_fail_count'] ?? 0) + 1;
    $_SESSION['login_fail_count'] = $attempts;
    if ($attempts >= 5) {
        $_SESSION['login_lock_time'] = time();
    }
}

function reset_login_throttle() {
    init_session();
    $_SESSION['login_fail_count'] = 0;
    unset($_SESSION['login_lock_time']);
}

/**
 * ==============================================================================
 * AUTENTIKASI PENGGUNA
 * ==============================================================================
 */
function login_user($username, $password) {
    init_session();
    $users = get_json_data('users.json');
    
    foreach ($users as &$user) {
        if ($user['username'] === $username) {
            $authenticated = false;

            // 1. Cek dengan password_verify jika hash bcrypt
            if (password_verify($password, $user['password'])) {
                $authenticated = true;
            } 
            // 2. Cek fallback awal jika pertama kali setup
            elseif (isset($user['initial_fallback']) && $password === $user['initial_fallback']) {
                $authenticated = true;
                $user['password'] = password_hash($password, PASSWORD_BCRYPT);
                unset($user['initial_fallback']);
                save_json_data('users.json', $users);
            }

            if ($authenticated) {
                // Pertahanan Session Fixation: Regenerasi Session ID
                session_regenerate_id(true);

                // Reset hitungan percobaan gagal
                reset_login_throttle();

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'email' => $user['email']
                ];
                return true;
            }
        }
    }
    
    record_failed_login();
    return false;
}

function logout_user() {
    init_session();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
