<?php
require_once '../config/koneksi.php';
require_once 'send_email.php';

echo "<h1>Test Email Sender</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to = trim($_POST['email']);
    
    if (empty($to)) {
        echo "<p style='color:red;'>Email wajib diisi.</p>";
    } else {
        // Create test email
        $subject = "Test Email dari Sistem Perpustakaan";
        $message = "
        <html>
        <head>
            <title>Test Email</title>
        </head>
        <body>
            <h2>Test Email</h2>
            <p>Ini adalah email test dari sistem perpustakaan.</p>
            <p>Jika Anda menerima email ini, berarti konfigurasi email Anda sudah benar.</p>
            <p>Waktu pengiriman: " . date('Y-m-d H:i:s') . "</p>
        </body>
        </html>
        ";
        
        // Try to send the email
        if (send_reset_email($to, $subject, $message)) {
            echo "<p style='color:green;'>Email berhasil dikirim ke {$to}. Silakan periksa kotak masuk Anda.</p>";
            echo "<p>Jika Anda tidak melihat email, periksa juga folder spam.</p>";
        } else {
            echo "<p style='color:red;'>Gagal mengirim email. Silakan periksa log error untuk informasi lebih lanjut.</p>";
            
            // Show log contents
            echo "<h3>Error Log:</h3>";
            echo "<pre>";
            if (file_exists('../logs/email_errors.txt')) {
                echo htmlspecialchars(file_get_contents('../logs/email_errors.txt'));
            } else {
                echo "No error logs found.";
            }
            echo "</pre>";
            
            // Show mail configuration (hide password)
            echo "<h3>Mail Configuration:</h3>";
            echo "<ul>";
            echo "<li>Host: " . MAIL_HOST . "</li>";
            echo "<li>Port: " . MAIL_PORT . "</li>";
            echo "<li>Username: " . MAIL_USERNAME . "</li>";
            echo "<li>Encryption: " . MAIL_ENCRYPTION . "</li>";
            echo "<li>From Name: " . MAIL_FROM_NAME . "</li>";
            echo "</ul>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email Sender</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        form { margin-top: 20px; }
        input, button { padding: 10px; margin: 5px 0; }
        button { background-color: #4e73df; color: white; border: none; cursor: pointer; }
        pre { background: #f4f4f4; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <?php if ($_SERVER['REQUEST_METHOD'] != 'POST'): ?>
    <form method="post">
        <div>
            <label for="email">Email untuk test:</label><br>
            <input type="email" id="email" name="email" required placeholder="example@example.com">
        </div>
        <button type="submit">Kirim Email Test</button>
    </form>
    <?php else: ?>
    <p><a href="simple_test_email.php">Kirim email test lagi</a></p>
    <?php endif; ?>
</body>
</html>
