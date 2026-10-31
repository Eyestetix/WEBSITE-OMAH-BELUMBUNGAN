<?php
/**
 * OMAH BELUMBUNGAN - Keluar Sesi Administrator
 */
require_once dirname(__DIR__) . '/includes/auth.php';
logout_user();
header('Location: login.php');
exit;
