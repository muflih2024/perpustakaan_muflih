<?php
require_once __DIR__ . '/../config/koneksi.php'; // Ensure BASE_URL is available

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();

if (session_destroy()) {
    header("location: " . BASE_URL . "auth/login.php?message=Anda telah berhasil logout.");
    exit;
} else {
    echo "Gagal logout.";
}
?>

