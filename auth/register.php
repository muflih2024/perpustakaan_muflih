<?php
// filepath: c:\xampp\htdocs\perpustakaan_muflih\auth\register.php
// Set secure session parameters if running in Vercel
if (getenv('VERCEL') === '1' && session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_secure', 'On');
    ini_set('session.cookie_httponly', 'On');
    ini_set('session.cookie_samesite', 'None');
}

require_once '../config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';
$success = '';

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil dan membersihkan input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $role = 'user'; // Default role untuk pendaftar baru

    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = "Semua field wajib diisi.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Username harus memiliki 3-50 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password != $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password harus memiliki minimal 6 karakter.";
    } else {
        // Cek apakah username atau email sudah terdaftar
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        
        if ($stmt = mysqli_prepare($koneksi, $check_query)) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $error = "Username atau email sudah terdaftar.";
                }
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
            
            mysqli_stmt_close($stmt);
        }
        
        // Jika tidak ada error, lanjutkan pendaftaran
        if (empty($error)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Cek apakah kolom email sudah ada di tabel users
            $result = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'email'");
            $email_column_exists = (mysqli_num_rows($result) > 0);
            
            // Jika kolom email belum ada, tambahkan kolom email
            if (!$email_column_exists) {
                $alter_table_query = "ALTER TABLE users ADD COLUMN email VARCHAR(100) UNIQUE AFTER username";
                if (!mysqli_query($koneksi, $alter_table_query)) {
                    $error = "Gagal menambahkan kolom email ke tabel users: " . mysqli_error($koneksi);
                }
            }
            
            // Insert user baru
            if (empty($error)) {
                $insert_query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                
                if ($stmt = mysqli_prepare($koneksi, $insert_query)) {
                    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Pendaftaran berhasil! Silakan login.";
                    } else {
                        $error = "Terjadi kesalahan saat mendaftar: " . mysqli_stmt_error($stmt);
                    }
                    
                    mysqli_stmt_close($stmt);
                } else {
                    $error = "Terjadi kesalahan pada database: " . mysqli_error($koneksi);
                }
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
    <title>Register - Perpustakaan</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/bootstrap.css/css/theme.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 40px;
        }
        .register-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-height: 80px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-register {
            width: 100%;
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-register:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="logo">
                <img src="<?= BASE_URL; ?>assets/logosmk.png" alt="Logo Perpustakaan">
                <h4>Sistem Perpustakaan</h4>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Masukkan kembali password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-register">Daftar</button>
            </form>
            
            <div class="login-link">
                Sudah memiliki akun? <a href="<?= BASE_URL; ?>auth/login.php">Login disini</a>
            </div>
        </div>
    </div>
    
    <script src="<?= BASE_URL; ?>assets/bootstrap.js/bootstrap.bundle.min.js"></script>
</body>
</html>