<?php
// env_loader.php kemungkinan menggunakan phpdotenv untuk memuat .env untuk pengembangan lokal.
// Pastikan ini di-include jika Anda bergantung pada .env untuk lokal.
// Untuk Vercel, environment variables diatur di dashboard.
if (file_exists(__DIR__ . '/env_loader.php')) {
    require_once __DIR__ . '/env_loader.php';
}

// Periksa apakah berjalan di Vercel
$is_vercel = getenv('VERCEL') === '1';

// Inisialisasi variabel koneksi ke null
$pdo_connection = null;  // Untuk Supabase/PostgreSQL di Vercel
$mysqli_connection = null; // Untuk MySQL lokal

// Menentukan BASE_URL
if (!defined('BASE_URL')) {
    $base_url_env = $is_vercel ? getenv('VERCEL_BASE_URL') : getenv('LOCAL_BASE_URL');
    if ($base_url_env) {
        define('BASE_URL', rtrim($base_url_env, '/') . '/');
    } else {
        // Fallback jika tidak diset melalui environment variables
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host_server = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_subdir = $is_vercel ? '' : '/perpustakaan_muflih/'; // Sesuaikan untuk setup lokal Anda
        define('BASE_URL', $protocol . $host_server . $base_subdir);
    }
}


if ($is_vercel) {
    // LINGKUNGAN VERCEL: Terhubung ke Supabase (PostgreSQL) menggunakan PDO
    error_log("Mencoba koneksi Supabase (PostgreSQL) di Vercel...");

    $db_host_vercel = getenv('DB_HOST');
    $db_port_vercel = getenv('DB_PORT') ?: '6543'; // Port default pooler Supabase
    $db_name_vercel = getenv('DB_NAME');
    $db_user_vercel = getenv('DB_USER');
    $db_pass_vercel = getenv('DB_PASS'); // Pastikan ini di-set di Vercel env vars
    $db_sslmode_vercel = getenv('DB_SSLMODE') ?: 'require'; // Default untuk Supabase

    if (!$db_host_vercel || !$db_user_vercel || !$db_pass_vercel || !$db_name_vercel) {
        error_log("Kesalahan koneksi Supabase di Vercel: Satu atau lebih environment variable DB hilang (DB_HOST, DB_USER, DB_PASS, DB_NAME).");
    } else {
        // DSN untuk PostgreSQL
        // User & password dimasukkan ke DSN untuk contoh ini
        $dsn_vercel = "pgsql:host={$db_host_vercel};port={$db_port_vercel};dbname={$db_name_vercel};user={$db_user_vercel};password={$db_pass_vercel};sslmode={$db_sslmode_vercel}";
        
        $options_pdo = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo_connection = new PDO($dsn_vercel, null, null, $options_pdo);
            error_log("Berhasil terhubung ke Supabase (PostgreSQL) di Vercel.");
        } catch (PDOException $e) {
            error_log("Kesalahan koneksi Supabase di Vercel: " . $e->getMessage());
            // $pdo_connection tetap null
        }
    }
} else {
    // LINGKUNGAN LOKAL: Terhubung ke MySQL menggunakan mysqli
    error_log("Mencoba koneksi MySQL lokal...");

    // Gunakan getenv() untuk konsistensi, asumsikan env_loader.php mengisinya dari .env
    // Nilai default diambil dari file asli Anda
    $host_local = getenv('DB_HOST') ?: 'localhost';
    $user_local = getenv('DB_USER') ?: 'root';
    $pass_local = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; // Tangani password kosong
    $name_local = getenv('DB_NAME') ?: 'perpustakaan_muflih';
    
    if (!$host_local || !$user_local || !$name_local) { // Password bisa kosong untuk MySQL lokal
         error_log("Kesalahan koneksi MySQL lokal: Satu atau lebih environment variable DB hilang untuk setup lokal.");
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Agar error mysqli jadi exception
            $mysqli_connection = mysqli_connect($host_local, $user_local, $pass_local, $name_local);
            error_log("Berhasil terhubung ke MySQL lokal.");
        } catch (mysqli_sql_exception $e) {
            error_log("Kesalahan koneksi MySQL lokal: " . $e->getMessage());
            // $mysqli_connection tetap null
        }
    }
}

// --- Fungsi Anda yang lain ---

// Only start session if none is active and we're not in a context where ini_set would be used
// We'll leave session initialization to specific pages when needed
// This helps avoid "headers already sent" and ini_set errors on Vercel
if (session_status() == PHP_SESSION_NONE && !is_vercel_env()) {
    session_start();
}

if (!function_exists('check_login')) {
    function check_login($required_role = null) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "auth/login.php?error=Silakan login terlebih dahulu.");
            exit();
        }
        if ($required_role && isset($_SESSION['role']) && $_SESSION['role'] !== $required_role) {
            header("Location: " . BASE_URL . "dashboard.php?error=Akses ditolak.");
            exit();
        }
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

// Restore is_vercel_env function since it's used in several places
if (!function_exists('is_vercel_env')) {
    function is_vercel_env() {
        return getenv('VERCEL') === '1';
    }
}

// Modified vercel_message function to be accurate with Supabase connection
if (!function_exists('vercel_message')) {
    function vercel_message($feature = 'Database') {
        if (is_vercel_env()) {
            global $pdo_connection;
            $connection_status = $pdo_connection ? "aktif" : "tidak tersedia";
            
            echo "<div style='padding: 20px; background-color: #f0f8ff; color: #31708f; border: 1px solid #bce8f1; border-radius: 5px; margin: 20px;'>";
            echo "<h3>ℹ️ Info {$feature} di Vercel</h3>";
            echo "<p>Aplikasi ini berjalan di Vercel dengan koneksi Supabase (PostgreSQL) {$connection_status}.</p>";
            echo "<p>Untuk pengembangan lokal dengan MySQL, jalankan aplikasi dengan XAMPP.</p>";
            echo "</div>";
        }
    }
}

// --- CONTOH PENGGUNAAN KONEKSI DI FILE LAIN ---
// Anda perlu mendeklarasikan variabel global dan memeriksa lingkungan
//
// global $pdo_connection, $mysqli_connection;
// $is_vercel = getenv('VERCEL') === '1';
//
// if ($is_vercel) {
//     if ($pdo_connection) {
//         // Gunakan $pdo_connection dengan metode PDO
//         // Contoh: $stmt = $pdo_connection->query("SELECT * FROM users");
//         // $users = $stmt->fetchAll();
//         // foreach ($users as $user) { echo $user['username']; }
//     } else {
//         // Koneksi PDO ke Supabase gagal
//         die("Koneksi database (Supabase/PDO) tidak tersedia di Vercel. Periksa log.");
//     }
// } else {
//     if ($mysqli_connection) {
//         // Gunakan $mysqli_connection dengan fungsi mysqli_*
//         // Contoh: $result = mysqli_query($mysqli_connection, "SELECT * FROM users");
//         // if ($result) {
//         //     while ($row = mysqli_fetch_assoc($result)) { echo $row['username']; }
//         // } else {
//         //     die("Query MySQL gagal: " . mysqli_error($mysqli_connection));
//         // }
//     } else {
//         // Koneksi mysqli ke MySQL lokal gagal
//         die("Koneksi database (MySQL/mysqli) tidak tersedia secara lokal. Periksa log.");
//     }
// }
?>