<?php
// config/koneksi.php - VERSI FINAL DENGAN LOGGING DETAIL

error_log("[CONFIG_KONEKSI] TOP - Timestamp: " . date('Y-m-d H:i:s'));

// Load .env file untuk pengembangan lokal (jika ada dan jika env_loader.php digunakan)
if (file_exists(__DIR__ . '/env_loader.php')) {
    require_once __DIR__ . '/env_loader.php';
    error_log("[CONFIG_KONEKSI] env_loader.php telah di-require.");
} else {
    error_log("[CONFIG_KONEKSI] env_loader.php tidak ditemukan di " . __DIR__ . "/env_loader.php");
}

// Periksa apakah berjalan di Vercel
$is_vercel = getenv('VERCEL') === '1'; // Vercel set variabel 'VERCEL' ke string '1'

// Logging detail untuk variabel VERCEL dan evaluasi $is_vercel
$vercel_env_value = getenv('VERCEL');
if ($vercel_env_value === false) {
    error_log("[CONFIG_KONEKSI] getenv('VERCEL') mengembalikan FALSE (variabel sistem VERCEL tidak diset).");
} elseif ($vercel_env_value === '') {
    error_log("[CONFIG_KONEKSI] getenv('VERCEL') mengembalikan string KOSONG.");
} else {
    error_log("[CONFIG_KONEKSI] Nilai getenv('VERCEL'): '" . $vercel_env_value . "' (tipe: " . gettype($vercel_env_value) . ")");
}
error_log("[CONFIG_KONEKSI] Hasil evaluasi (\$is_vercel = getenv('VERCEL') === '1'): " . ($is_vercel ? 'TRUE' : 'FALSE'));


// Inisialisasi variabel koneksi ke null
$pdo_connection = null;  // Untuk Supabase/PostgreSQL di Vercel
$mysqli_connection = null; // Untuk MySQL lokal

// Menentukan BASE_URL (jika belum terdefinisi)
if (!defined('BASE_URL')) {
    $base_url_env_vercel = getenv('VERCEL_BASE_URL');
    $base_url_env_local = getenv('LOCAL_BASE_URL');

    if ($is_vercel && $base_url_env_vercel) {
        define('BASE_URL', rtrim($base_url_env_vercel, '/') . '/');
        error_log("[CONFIG_KONEKSI] BASE_URL didefinisikan dari VERCEL_BASE_URL: " . BASE_URL);
    } elseif (!$is_vercel && $base_url_env_local) {
        define('BASE_URL', rtrim($base_url_env_local, '/') . '/');
        error_log("[CONFIG_KONEKSI] BASE_URL didefinisikan dari LOCAL_BASE_URL: " . BASE_URL);
    } else {
        // Fallback jika tidak diset melalui environment variables
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host_server = $_SERVER['HTTP_HOST'] ?? ($is_vercel ? 'nama-proyek.vercel.app' : 'localhost'); // Perlu host default yang lebih baik
        $base_subdir = $is_vercel ? '' : '/perpustakaan_muflih/'; // Sesuaikan untuk setup lokal Anda
        define('BASE_URL', $protocol . $host_server . $base_subdir);
        error_log("[CONFIG_KONEKSI] BASE_URL didefinisikan dari fallback: " . BASE_URL);
    }
} else {
    error_log("[CONFIG_KONEKSI] BASE_URL sudah terdefinisi: " . BASE_URL);
}


if ($is_vercel) {
    // LINGKUNGAN VERCEL: Terhubung ke Supabase (PostgreSQL) menggunakan PDO
    error_log("[CONFIG_KONEKSI] DALAM BLOK IF (\$is_vercel TRUE) - Mencoba koneksi Supabase (PostgreSQL) di Vercel...");

    $db_host_vercel = getenv('DB_HOST');
    $db_port_vercel = getenv('DB_PORT') ?: '6543';
    $db_name_vercel = getenv('DB_NAME');
    $db_user_vercel = getenv('DB_USER');
    $db_pass_vercel = getenv('DB_PASS');
    $db_sslmode_vercel = getenv('DB_SSLMODE') ?: 'require';

    error_log("[CONFIG_KONEKSI-VERCEL-ENV] DB_HOST: " . ($db_host_vercel ?: 'KOSONG/TIDAK ADA'));
    error_log("[CONFIG_KONEKSI-VERCEL-ENV] DB_PORT: " . $db_port_vercel);
    error_log("[CONFIG_KONEKSI-VERCEL-ENV] DB_NAME: " . ($db_name_vercel ?: 'KOSONG/TIDAK ADA'));
    error_log("[CONFIG_KONEKSI-VERCEL-ENV] DB_USER: " . ($db_user_vercel ?: 'KOSONG/TIDAK ADA'));
    error_log("[CONFIG_KONEKSI-VERCEL-ENV] DB_PASS: " . ($db_pass_vercel ? 'ADA (disembunyikan)' : 'KOSONG/TIDAK ADA'));
    error_log("[CONFIG_KONEKSI-VERCEL-ENV] DB_SSLMODE: " . $db_sslmode_vercel);


    if (!$db_host_vercel || !$db_user_vercel || !$db_pass_vercel || !$db_name_vercel) {
        error_log("[CONFIG_KONEKSI] Kesalahan fatal koneksi Supabase di Vercel: Satu atau lebih environment variable DB penting hilang (DB_HOST, DB_USER, DB_PASS, DB_NAME). Tidak akan mencoba koneksi PDO.");
    } else {
        $dsn_vercel = "pgsql:host={$db_host_vercel};port={$db_port_vercel};dbname={$db_name_vercel};user={$db_user_vercel};password={$db_pass_vercel};sslmode={$db_sslmode_vercel}";
        error_log("[CONFIG_KONEKSI] DSN Vercel (Supabase): " . "pgsql:host={$db_host_vercel};port={$db_port_vercel};dbname={$db_name_vercel};user={$db_user_vercel};password=*****;sslmode={$db_sslmode_vercel}"); // Password disembunyikan dari log DSN

        $options_pdo = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo_connection = new PDO($dsn_vercel, null, null, $options_pdo);
            error_log("[CONFIG_KONEKSI] SUKSES: Berhasil terhubung ke Supabase (PostgreSQL) di Vercel.");
        } catch (PDOException $e) {
            error_log("[CONFIG_KONEKSI] GAGAL KONEKSI PDO Supabase di Vercel: " . $e->getMessage());
            // $pdo_connection tetap null
        }
    }
} else {
    // LINGKUNGAN LOKAL (atau jika $is_vercel FALSE): Terhubung ke MySQL menggunakan mysqli
    error_log("[CONFIG_KONEKSI] DALAM BLOK ELSE (\$is_vercel FALSE) - Mencoba koneksi MySQL lokal...");

    $host_local = getenv('DB_HOST') ?: 'localhost';
    $user_local = getenv('DB_USER') ?: 'root';
    $pass_local = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $name_local = getenv('DB_NAME') ?: 'perpustakaan_muflih';

    error_log("[CONFIG_KONEKSI-LOCAL-ENV] DB_HOST: " . $host_local);
    error_log("[CONFIG_KONEKSI-LOCAL-ENV] DB_USER: " . $user_local);
    error_log("[CONFIG_KONEKSI-LOCAL-ENV] DB_PASS: " . ($pass_local ? 'ADA (disembunyikan)' : 'KOSONG/TIDAK ADA'));
    error_log("[CONFIG_KONEKSI-LOCAL-ENV] DB_NAME: " . $name_local);
    
    if (empty($host_local) || empty($user_local) || empty($name_local)) { // Password bisa kosong
         error_log("[CONFIG_KONEKSI] Kesalahan fatal koneksi MySQL lokal: Satu atau lebih environment variable DB penting hilang (DB_HOST, DB_USER, DB_NAME). Tidak akan mencoba koneksi mysqli.");
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            // INI BARIS YANG MENYEBABKAN ERROR FATAL JIKA $is_vercel FALSE DAN mysqli dipanggil
            // Di Vercel, blok ini seharusnya TIDAK PERNAH dieksekusi jika $is_vercel TRUE
            $mysqli_connection = mysqli_connect($host_local, $user_local, $pass_local, $name_local); 
            error_log("[CONFIG_KONEKSI] SUKSES: Berhasil terhubung ke MySQL lokal.");
        } catch (mysqli_sql_exception $e) {
            error_log("[CONFIG_KONEKSI] GAGAL KONEKSI mysqli MySQL lokal: " . $e->getMessage());
            // $mysqli_connection tetap null
        }
    }
}

// --- Fungsi Anda yang lain (check_login, sanitize) ---
// Pastikan ini tidak menyebabkan error jika $pdo_connection atau $mysqli_connection adalah null
// jika koneksi gagal di atas.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    error_log("[CONFIG_KONEKSI] Session dimulai.");
}

if (!function_exists('check_login')) {
    function check_login($required_role = null) {
        // ... (kode check_login Anda) ...
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        // ... (kode sanitize Anda) ...
    }
}

error_log("[CONFIG_KONEKSI] BOTTOM - Eksekusi selesai.");
?>