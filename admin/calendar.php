<?php
/**
 * OMAH BELUMBUNGAN - Kalender Terpadu Jadwal
 * Redirects to Unified Calendar View inside bookings.php
 */
$queryString = $_SERVER['QUERY_STRING'] ? ('&' . $_SERVER['QUERY_STRING']) : '';
header('Location: bookings.php?view=calendar' . $queryString);
exit;
