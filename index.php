<?php
// index.php (di root proyek)

// Sertakan file koneksi. Ini akan mendefinisikan $is_vercel, $pdo_connection (jika di Vercel & sukses),
// $mysqli_connection (jika lokal & sukses), dan BASE_URL.
require_once __DIR__ . '/config/koneksi.php'; 

// Pastikan variabel koneksi dari koneksi.php tersedia di scope ini.
// Ini diperlukan jika koneksi.php tidak secara eksplisit me-return variabel
// atau jika Anda ingin mengaksesnya langsung tanpa prefix objek (jika koneksi.php adalah class).
global $pdo_connection, $mysqli_connection, $is_vercel; // $is_vercel juga didefinisikan di koneksi.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah berjalan di Vercel menggunakan variabel $is_vercel dari koneksi.php
// atau cek ulang jika Anda lebih suka: $is_running_on_vercel = getenv('VERCEL') === '1';
if ($is_vercel) { // Menggunakan $is_vercel yang sudah di-set di config/koneksi.php
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perpustakaan Muflih - Mode Vercel (Supabase)</title>
        <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>assets/bootstrap.css/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card">
                <div class="card-header <?php echo ($pdo_connection ? 'bg-success' : 'bg-danger'); ?> text-white">
                    <h2>Perpustakaan Muflih - Status Vercel (Supabase)</h2>
                </div>
                <div class="card-body">
                    <?php
                    if ($pdo_connection) {
                        echo "<div class='alert alert-success'><h4>✅ Berhasil terhubung ke database Supabase (PostgreSQL) via PDO!</h4><p>Aplikasi sekarang menggunakan database di cloud.</p><p>Anda dapat melanjutkan ke dashboard.</p></div>";
                    } else {
                        echo "<div class='alert alert-danger'><h4>❌ Gagal terhubung ke database Supabase (PostgreSQL).</h4><p>Silakan periksa Vercel Runtime Logs untuk detail error koneksi. Cari pesan yang diawali dengan '[CONFIG_KONEKSI]'.</p></div>";
                    }
                    ?>
                    
                    <h3>Tentang Aplikasi</h3>
                    <p>Perpustakaan Muflih adalah aplikasi manajemen perpustakaan yang dikembangkan dengan PHP.</p>
                    
                    <h3>Fitur Utama:</h3>
                    <ul>
                        <li>Manajemen buku dan peminjaman</li>
                        <li>Manajemen pengguna</li>
                        <li>Login dengan Google</li>
                        <li>System reset password</li>
                    </ul>

                    <div class="mt-4">
                        <?php if ($pdo_connection): // Hanya tampilkan link dashboard jika koneksi berhasil ?>
                            <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>auth/login.php" class="btn btn-primary">Masuk ke Dashboard</a>
                        <?php endif; ?>
                        <a href="https://github.com/muflih2024/perpustakaan_muflih" target="_blank" class="btn btn-secondary">Source Code</a> 
                        <!-- Pastikan 'muflih2024' adalah username GitHub Anda yang benar -->
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit(); // Hentikan eksekusi setelah menampilkan halaman info Vercel
} else {
    // LINGKUNGAN LOKAL (tidak di Vercel), lanjutkan dengan normal menggunakan MySQL
    // Pastikan $mysqli_connection dari koneksi.php tersedia
    // global $mysqli_connection; // uncomment jika perlu

    if (!$mysqli_connection) {
        // Tampilkan error jika koneksi lokal gagal, jangan langsung redirect
        die("Koneksi ke database MySQL lokal gagal. Periksa konfigurasi .env dan log error PHP Anda. Pesan dari koneksi.php: (cek error_log)");
    }

    if (isset($_SESSION['user_id'])) {
        header("Location: " . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . "dashboard.php");
        exit();
    } else {
        header("Location: " . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . "auth/login.php");
        exit();
    }
}
?>