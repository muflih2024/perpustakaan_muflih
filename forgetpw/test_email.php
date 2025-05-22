<?php
require_once '../config/koneksi.php';

$error = '';
$success = '';

// Check if the user is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php?error=Anda tidak memiliki akses ke halaman ini");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to = trim($_POST['email']);
    
    if (empty($to)) {
        $error = "Email wajib diisi.";
    } else {
        // Create test email
        $subject = "Test Email dari Sistem Perpustakaan";
        $message = "
        <html>
        <head>
            <title>Test Email</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #4e73df;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    padding: 20px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 0.8em;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Perpustakaan Muflih</h2>
                </div>
                <div class='content'>
                    <h3>Test Email</h3>
                    <p>Halo,</p>
                    <p>Ini adalah email test dari sistem Perpustakaan Muflih.</p>
                    <p>Jika Anda menerima email ini, berarti sistem pengiriman email berfungsi dengan baik.</p>
                    <p>Email ini dikirim pada: " . date('Y-m-d H:i:s') . "</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Perpustakaan Muflih. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Kirim email test
        require_once '../forgetpw/send_email.php';
        if (send_reset_email($to, $subject, $message)) {
            $success = "Email test berhasil dikirim ke {$to}. Silakan cek kotak masuk atau folder spam.";
        } else {
            $error = "Gagal mengirim email test. Periksa konfigurasi email di file config/mail_config.php.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/bootstrap.css/css/theme.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Test Pengiriman Email</h4>
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
                                <label for="email" class="form-label">Email Tujuan</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Masukkan email tujuan">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Kirim Email Test</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="../dashboard.php">Kembali ke Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Informasi Konfigurasi Email</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Catatan:</strong> Jika email tidak terkirim, periksa konfigurasi berikut:</p>
                        <ol>
                            <li>Pastikan konfigurasi email di <code>config/mail_config.php</code> sudah benar.</li>
                            <li>Untuk Gmail, gunakan App Password, bukan password akun utama.</li>
                            <li>Pastikan akun Gmail mengizinkan "Less secure app access" atau menggunakan 2FA dan App Password.</li>
                            <li>Periksa firewall dan konfigurasi jaringan untuk port SMTP (587 atau 465).</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap.js/bootstrap.bundle.min.js"></script>
</body>
</html>
