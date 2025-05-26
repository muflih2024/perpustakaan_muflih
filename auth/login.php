<?php
// auth/login.php

// Sertakan file koneksi. Ini akan mendefinisikan $is_vercel, 
// $pdo_connection (jika di Vercel & sukses), $mysqli_connection (jika lokal & sukses), 
// dan BASE_URL.
require_once '../config/koneksi.php'; // Path disesuaikan karena login.php ada di dalam auth/

// Pastikan variabel dari koneksi.php tersedia.
global $pdo_connection, $mysqli_connection, $is_vercel;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';

// Cek jika ada error dari URL
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Check for Google login error
if (isset($_SESSION['google_login_error'])) {
    $error = $_SESSION['google_login_error'];
    unset($_SESSION['google_login_error']);
}


// --- LOGIKA LOGIN BERDASARKAN LINGKUNGAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = trim($_POST['username']); // Gunakan nama variabel yang berbeda untuk input
    $password_input = trim($_POST['password']); // Gunakan nama variabel yang berbeda untuk input

    if (empty($username_input) || empty($password_input)) {
        $error = "Username/Email dan password wajib diisi.";
    } else {
        $is_email = strpos($username_input, '@') !== false;

        if ($is_vercel) {
            // --- LINGKUNGAN VERCEL (GUNAKAN PDO DAN SUPABASE) ---
            error_log("[LOGIN_VERCEL] Memulai proses login Vercel untuk: " . $username_input);
            if (!$pdo_connection) {
                $error = "Koneksi ke database Supabase gagal. Silakan coba lagi nanti atau hubungi administrator.";
                error_log("[LOGIN_VERCEL] Gagal: \$pdo_connection adalah null.");
            } else {
                if ($is_email) {
                    $sql = "SELECT id, username, email, password, role FROM users WHERE email = :credential";
                } else {
                    $sql = "SELECT id, username, email, password, role FROM users WHERE username = :credential";
                }
                
                try {
                    $stmt = $pdo_connection->prepare($sql);
                    $stmt->bindParam(':credential', $username_input);
                    $stmt->execute();
                    
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        // Verifikasi password
                        // Asumsi password di Supabase (PostgreSQL) di-hash dengan password_hash() PHP
                        if (password_verify($password_input, $user['password'])) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            // $_SESSION['email'] = $user['email']; // Simpan email jika perlu
                            $_SESSION['role'] = $user['role'];
                            // $_SESSION['loggedin'] = true; // Opsional

                            error_log("[LOGIN_VERCEL] Login berhasil untuk user: " . $user['username']);
                            header("Location: " . BASE_URL . "dashboard.php");
                            exit();
                        } else {
                            $error = "Password yang Anda masukkan salah.";
                            error_log("[LOGIN_VERCEL] Gagal: Password salah untuk " . $username_input);
                        }
                    } else {
                        $error = $is_email ? "Email tidak ditemukan." : "Username tidak ditemukan.";
                        error_log("[LOGIN_VERCEL] Gagal: User tidak ditemukan - " . $username_input);
                    }
                } catch (PDOException $e) {
                    $error = "Oops! Terjadi kesalahan database. Silakan coba lagi nanti.";
                    error_log("[LOGIN_VERCEL] PDOException: " . $e->getMessage());
                }
            }
        } else {
            // --- LINGKUNGAN LOKAL (GUNAKAN MYSQLI) ---
            error_log("[LOGIN_LOKAL] Memulai proses login lokal untuk: " . $username_input);
            if (!$mysqli_connection) {
                 $error = "Koneksi ke database MySQL lokal gagal. Periksa konfigurasi.";
                 error_log("[LOGIN_LOKAL] Gagal: \$mysqli_connection adalah null.");
            } else {
                if ($is_email) {
                    $sql = "SELECT id, username, email, password, role FROM users WHERE email = ?";
                } else {
                    $sql = "SELECT id, username, email, password, role FROM users WHERE username = ?";
                }
                
                $stmt_mysqli = null; // Gunakan nama variabel berbeda untuk statement mysqli
                $password_verified = false;

                if ($stmt_mysqli = mysqli_prepare($mysqli_connection, $sql)) {
                    mysqli_stmt_bind_param($stmt_mysqli, "s", $param_username);
                    $param_username = $username_input;

                    if (mysqli_stmt_execute($stmt_mysqli)) {
                        mysqli_stmt_store_result($stmt_mysqli);

                        if (mysqli_stmt_num_rows($stmt_mysqli) == 1) {
                            mysqli_stmt_bind_result($stmt_mysqli, $id_db, $username_db, $email_db, $hash_db, $role_db);
                            if (mysqli_stmt_fetch($stmt_mysqli)) {
                                // Cek apakah password di DB sudah di-hash atau plain (seperti kode Anda sebelumnya)
                                $is_hashed_mysqli = preg_match('/^\$2[axy]\$/', $hash_db);

                                if ($is_hashed_mysqli) {
                                    if (password_verify($password_input, $hash_db)) {
                                        $password_verified = true;
                                    }
                                } else {
                                    // Jika plain text, verifikasi dan update ke hash (seperti kode Anda)
                                    if ($password_input === $hash_db) {
                                        $password_verified = true;
                                        // Logika update hash Anda bisa ditaruh di sini
                                        $new_hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
                                        $sql_update_hash = "UPDATE users SET password = ? WHERE id = ?";
                                        if ($stmt_update_mysqli = mysqli_prepare($mysqli_connection, $sql_update_hash)) {
                                            mysqli_stmt_bind_param($stmt_update_mysqli, "si", $new_hashed_password, $id_db);
                                            mysqli_stmt_execute($stmt_update_mysqli);
                                            mysqli_stmt_close($stmt_update_mysqli);
                                        }
                                    }
                                }

                                if ($password_verified) {
                                    session_regenerate_id(true);
                                    $_SESSION['user_id'] = $id_db;
                                    $_SESSION['username'] = $username_db;
                                    $_SESSION['role'] = $role_db;
                                    // $_SESSION['loggedin'] = true;

                                    mysqli_stmt_close($stmt_mysqli);
                                    // mysqli_close($mysqli_connection); // JANGAN close koneksi global di sini
                                    error_log("[LOGIN_LOKAL] Login berhasil untuk user: " . $username_db);
                                    header("Location: " . BASE_URL . "dashboard.php");
                                    exit();
                                } else {
                                    $error = "Password yang Anda masukkan salah.";
                                    error_log("[LOGIN_LOKAL] Gagal: Password salah untuk " . $username_input);
                                }
                            }
                        } else {
                            $error = $is_email ? "Email tidak ditemukan." : "Username tidak ditemukan.";
                            error_log("[LOGIN_LOKAL] Gagal: User tidak ditemukan - " . $username_input);
                        }
                    } else {
                        $error = "Oops! Terjadi kesalahan saat eksekusi query. Silakan coba lagi nanti.";
                        error_log("[LOGIN_LOKAL] mysqli_stmt_execute error: " . mysqli_stmt_error($stmt_mysqli));
                    }
                    if ($stmt_mysqli) {
                         mysqli_stmt_close($stmt_mysqli);
                    }
                } else {
                     $error = "Oops! Terjadi kesalahan database saat persiapan statement. Silakan coba lagi nanti.";
                     error_log("[LOGIN_LOKAL] mysqli_prepare error: " . mysqli_error($mysqli_connection));
                }
                // if (!$password_verified && $mysqli_connection) { mysqli_close($mysqli_connection); } // Jangan close koneksi global
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Perpustakaan Muflih</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>assets/bootstrap.css/css/bootstrap.min.css">
    <style>
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            margin-top: 100px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background-color: #4e73df;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            width: 100%;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        .btn-google {
            background-color: #ea4335;
            border-color: #ea4335;
            color: white;
            width: 100%;
            padding: 10px;
        }
        .btn-google:hover {
            background-color: #d62516;
            border-color: #d62516;
            color: white;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .logo {
            max-width: 80px;
            margin: 0 auto 10px auto;
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <?php if (file_exists(__DIR__ . '/../assets/logosmk.png')): ?>
                        <img src="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>assets/logosmk.png" alt="Logo Perpustakaan" class="logo">
                    <?php endif; ?>
                    <h4>Masuk ke Perpustakaan Muflih</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post" class="mt-3">
                        <div class="form-group mb-3">
                            <label for="username">Username atau Email</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>forgetpw/" class="text-decoration-none">Lupa password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary mb-3">Masuk</button>
                    </form>
                    
                    <?php
                    // Check if Google login is configured
                    $google_config_file = __DIR__ . '/../config/google_config.php';
                    $google_login_enabled = file_exists($google_config_file);
                    
                    if ($google_login_enabled):
                    ?>
                    <div class="text-center my-3">
                        <p>ATAU</p>
                    </div>
                    <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>auth/google_login.php" class="btn btn-google">
                        <i class="fa fa-google"></i> Masuk dengan Google
                    </a>
                    <?php endif; ?>
                    
                    <div class="register-link">
                        <p>Belum punya akun? <a href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>auth/register.php" class="text-decoration-none">Daftar</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>assets/bootstrap.js/bootstrap.bundle.min.js"></script>
</body>
</html>