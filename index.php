<?php
require_once __DIR__ . '/config/koneksi.php'; // Ensure BASE_URL is available

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah berjalan di Vercel
if (is_vercel_env()) {
    // Jika di Vercel, tampilkan halaman informasi
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perpustakaan Muflih - Vercel Demo</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/bootstrap.css/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2>Perpustakaan Muflih - Demo Mode</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4>⚠️ Database tidak tersedia di Vercel</h4>
                        <p>Aplikasi ini berjalan di platform Vercel yang tidak mendukung koneksi MySQL langsung.</p>
                        <p>Untuk menggunakan aplikasi dengan fitur lengkap, silakan jalankan secara lokal dengan XAMPP.</p>
                    </div>
                    
                    <h3>Tentang Aplikasi</h3>
                    <p>Perpustakaan Muflih adalah aplikasi manajemen perpustakaan yang dikembangkan dengan PHP dan MySQL.</p>
                    
                    <h3>Fitur Utama:</h3>
                    <ul>
                        <li>Manajemen buku dan peminjaman</li>
                        <li>Manajemen pengguna</li>
                        <li>Login dengan Google</li>
                        <li>System reset password</li>
                    </ul>

                    <div class="mt-4">
                        <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-primary">Lihat Demo</a>
                        <a href="https://github.com/yourusername/perpustakaan_muflih" target="_blank" class="btn btn-secondary">Source Code</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
} else {
    // Jika tidak di Vercel, lanjutkan dengan normal
    if (isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "dashboard.php");
        exit();
    } else {
        header("Location: " . BASE_URL . "auth/login.php");
        exit();
    }
}
?>

