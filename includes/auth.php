<?php
/**
 * OMAH BELUMBUNGAN - Autentikasi Admin Sederhana & Aman
 */

require_once __DIR__ . '/functions.php';

function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in() {
    init_session();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function get_logged_in_user() {
    init_session();
    return $_SESSION['admin_user'] ?? null;
}

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
            // 2. Cek fallback awal jika pertama kali setup (admin123)
            elseif (isset($user['initial_fallback']) && $password === $user['initial_fallback']) {
                $authenticated = true;
                // Auto-upgrade ke hash bcrypt yang kuat
                $user['password'] = password_hash($password, PASSWORD_BCRYPT);
                unset($user['initial_fallback']);
                save_json_data('users.json', $users);
            }

            if ($authenticated) {
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
