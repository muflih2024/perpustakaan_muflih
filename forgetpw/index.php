<?php
require_once '../config/koneksi.php';
$error = '';
$success = '';

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

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Email wajib diisi.";
    } else {
        // Cek apakah email ada di database
        $sql = "SELECT id, username, email FROM users WHERE email = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
              // Generate token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 7200); // Token berlaku 2 jam untuk mengatasi perbedaan timezone
              // Check if password_reset table exists
            $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'password_reset'");
            
            if (mysqli_num_rows($check_table) == 0) {
                // Table doesn't exist, create it
                $create_table_query = "
                CREATE TABLE `password_reset` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `token` varchar(255) NOT NULL,
                    `expires_at` datetime NOT NULL,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`),
                    KEY `token` (`token`),
                    CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                mysqli_query($koneksi, $create_table_query);
            }
            
            // Hapus token lama jika ada
            $delete_sql = "DELETE FROM password_reset WHERE user_id = ?";
            $delete_stmt = mysqli_prepare($koneksi, $delete_sql);
            
            // Check if prepare was successful
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, "i", $user['id']);
                mysqli_stmt_execute($delete_stmt);
            }
              // Simpan token baru
            $insert_sql = "INSERT INTO password_reset (user_id, token, expires_at) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($koneksi, $insert_sql);
            
            // Check if prepare was successful
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, "iss", $user['id'], $token, $expires_at);
            } else {
                $error = "Terjadi kesalahan saat memproses permintaan. Error: " . mysqli_error($koneksi);
            }
            
            if ($insert_stmt && mysqli_stmt_execute($insert_stmt)) {
                // Kirim email reset password
                $reset_link = BASE_URL . "forgetpw/reset.php?token=" . $token;
                $to = $user['email'];
                $subject = "Reset Password Perpustakaan";
                
                $message = "
                <html>
                <head>
                    <title>Reset Password</title>
                </head>
                <body>
                    <h2>Permintaan Reset Password</h2>
                    <p>Halo {$user['username']},</p>
                    <p>Kami menerima permintaan untuk reset password akun Anda. Silakan klik link di bawah ini untuk reset password:</p>
                    <p><a href='{$reset_link}'>Reset Password</a></p>
                    <p>Link ini akan kadaluarsa dalam 1 jam.</p>
                    <p>Jika Anda tidak meminta reset password, silakan abaikan email ini.</p>
                    <br>
                    <p>Terima kasih,</p>
                    <p>Tim Perpustakaan</p>
                </body>
                </html>
                ";
                
                // Set header email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Perpustakaan <perpustakaanmuflih@gmail.com>" . "\r\n";
                
                // Kirim email
                require_once '../forgetpw/send_email.php';
                if (send_reset_email($to, $subject, $message)) {
                    $success = "Link untuk reset password telah dikirim ke email Anda. Silakan cek email Anda.";
                } else {
                    $error = "Gagal mengirim email reset password. Silakan coba lagi nanti.";
                }
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi nanti.";
            }
        } else {
            $error = "Email tidak ditemukan dalam sistem kami.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/bootstrap.css/css/theme.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Lupa Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Masukkan email anda">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Kirim Link Reset Password</button>
                            </div>
                        </form>
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
