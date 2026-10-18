<?php
// Turn off error display on live site
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u368001018_mmuslim_dbadm');
define('DB_PASS', 'U2FsdGVkX18FQrT97doNra3+TLnIE0/ez1FnvKlx3Hv2aL1r');
define('DB_NAME', 'u368001018_mmuslims_db');

// Site configuration
define('SITE_NAME', 'Myanmar Muslim Library');
define('SITE_URL', 'https://myanmarmuslim.org/');
define('SITE_EMAIL', 'admin@myanmarmuslims.com');

// Upload paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/myanmarmuslims/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Timezone
date_default_timezone_set('Asia/Yangon');

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Database connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Service temporarily unavailable. Please try again later.');
}