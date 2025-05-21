<?php
require_once __DIR__ . '/env_loader.php';

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'perpustakaan_muflih';

$koneksi = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_login($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        $script_path = $_SERVER['SCRIPT_NAME'];
        // Determine the correct path to login.php using BASE_URL
        header("Location: " . BASE_URL . "auth/login.php?error=Silakan login terlebih dahulu.");
        exit();
    }
    if ($required_role && $_SESSION['role'] !== $required_role) {
        // Determine the correct path to dashboard.php using BASE_URL
        header("Location: " . BASE_URL . "dashboard.php?error=Akses ditolak.");
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

