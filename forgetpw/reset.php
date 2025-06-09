<?php
// Set secure session parameters if running in Vercel
if (getenv('VERCEL') === '1' && session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_secure', 'On');
    ini_set('session.cookie_httponly', 'On');
    ini_set('session.cookie_samesite', 'None');
}

require_once '../config/koneksi.php';

$error = '';
$success = '';
$valid_token = false;
$token = '';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

// Cek jika ada pesan dari URL
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}

// Cek token dari URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
      // Verifikasi token
    $sql = "SELECT pr.id, pr.user_id, pr.expires_at, u.username, u.email 
            FROM password_reset pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $reset_data = mysqli_fetch_assoc($result);
        
        // Check expiration manually to handle timezone issues
        $expires_timestamp = strtotime($reset_data['expires_at']);
        $current_timestamp = time();
        
        if ($expires_timestamp > $current_timestamp) {
            $valid_token = true;
        } else {
            $error = "Token sudah kadaluarsa. Silakan minta link reset password baru.";
        }
    } else {
        $error = "Token tidak valid. Silakan minta link reset password baru.";
    }
} else {
    header("Location: " . BASE_URL . "forgetpw/index.php");
    exit();
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validasi password
    if (strlen($password) < 8) {
        $error = "Password harus minimal 8 karakter.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok dengan password baru.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $reset_data['user_id']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Hapus token
            $delete_sql = "DELETE FROM password_reset WHERE user_id = ?";
            $delete_stmt = mysqli_prepare($koneksi, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "i", $reset_data['user_id']);
            mysqli_stmt_execute($delete_stmt);
            
            // Kirim email konfirmasi
            $to = $reset_data['email'];
            $subject = "Password Berhasil Diubah - Perpustakaan";
            
            $message = "
            <html>
            <head>
                <title>Password Berhasil Diubah</title>
            </head>
            <body>
                <h2>Password Berhasil Diubah</h2>
                <p>Halo {$reset_data['username']},</p>
                <p>Password akun Perpustakaan Anda telah berhasil diubah.</p>
                <p>Jika Anda tidak melakukan perubahan ini, silakan hubungi administrator sistem segera.</p>
                <br>
                <p>Terima kasih,</p>
                <p>Tim Perpustakaan</p>
            </body>
            </html>
            ";
            
            // Kirim email konfirmasi
            require_once '../forgetpw/send_email.php';
            send_reset_email($to, $subject, $message);
            
            $success = "Password berhasil diubah. Silakan <a href='" . BASE_URL . "auth/login.php'>login</a> dengan password baru Anda.";
            $valid_token = false; // Sembunyikan form setelah berhasil
        } else {
            $error = "Gagal mengubah password. Silakan coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/bootstrap.css/css/theme.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Reset Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <?php if ($valid_token): ?>
                            <form action="?token=<?= htmlspecialchars($token) ?>" method="post">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                    <small class="form-text text-muted">Password minimal 8 karakter</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <?php if (empty($success)): ?>
                                <div class="text-center">
                                    <p>Token tidak valid atau sudah kadaluarsa.</p>
                                    <a href="../forgetpw/index.php" class="btn btn-primary">Minta Link Reset Password Baru</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="mt-3 text-center">
                            <a href="../auth/login.php">Kembali ke halaman login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap.js/bootstrap.bundle.min.js"></script>
</body>
</html>
