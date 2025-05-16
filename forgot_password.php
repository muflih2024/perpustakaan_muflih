<?php
session_start();
include 'config/koneksi.php'; // Your DB connection

// --- PHPMailer Inclusion --- 
// Option 1: Composer (Recommended)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} 
// Option 2: Manual Installation (Place PHPMailer source in a PHPMailer directory)
else if (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    require __DIR__ . '/PHPMailer/src/Exception.php';
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
} else {
    // Critical error: PHPMailer not found. 
    // Display a generic error or log and exit if this is a production environment.
    exit("PHPMailer library not found. Please install it via Composer or manually.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// --- End PHPMailer Inclusion ---

$message = '';
$error = '';

// --- SMTP Configuration (REPLACE WITH YOUR ACTUAL CREDENTIALS) ---
define('SMTP_HOST', 'smtp.gmail.com'); // Example for Gmail
define('SMTP_USERNAME', 'your_email@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your_gmail_app_password'); // Your Gmail App Password
define('SMTP_PORT', 587); // TLS
// define('SMTP_PORT', 465); // SSL
define('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS); // For port 587
// define('SMTP_SECURE', PHPMailer::ENCRYPTION_SMTPS); // For port 465
define('EMAIL_FROM', 'your_email@gmail.com'); // Must be the same as SMTP_USERNAME for some providers like Gmail
define('EMAIL_FROM_NAME', 'Perpustakaan Muflih');
// --- End SMTP Configuration ---

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Check if email exists
        $stmt = $koneksi->prepare("SELECT id, email, nama_user FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            $error = "Database error (prepare failed): " . htmlspecialchars($koneksi->error);
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
                $user_name = !empty($user['nama_user']) ? $user['nama_user'] : 'Pengguna';

                $token = bin2hex(random_bytes(32)); // Generate a secure token
                $expires = time() + 1800; // Token expires in 30 minutes

                // Store token in database
                // Ensure your 'users' table has 'reset_token' (VARCHAR/TEXT) and 'reset_token_expires' (INT/TIMESTAMP) columns.
                $update_stmt = $koneksi->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                if (!$update_stmt) {
                    $error = "Database error (update prepare failed): " . htmlspecialchars($koneksi->error);
                } else {
                    $update_stmt->bind_param("sii", $token, $expires, $user_id);
                    if ($update_stmt->execute()) {
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'];
                        $script_path = dirname($_SERVER['PHP_SELF']);
                        $base_path = rtrim($script_path, '/') . '/'; // Ensures a trailing slash
                        $reset_link = $protocol . "://" . $host . $base_path . "reset_password.php?token=" . $token;

                        $mail = new PHPMailer(true);
                        try {
                            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to SMTP::DEBUG_SERVER for detailed logs during development
                            $mail->isSMTP();
                            $mail->Host       = SMTP_HOST;
                            $mail->SMTPAuth   = true;
                            $mail->Username   = SMTP_USERNAME;
                            $mail->Password   = SMTP_PASSWORD;
                            $mail->SMTPSecure = SMTP_SECURE;
                            $mail->Port       = SMTP_PORT;
                            $mail->CharSet    = 'UTF-8';

                            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
                            $mail->addAddress($email, $user_name);

                            $mail->isHTML(true);
                            $mail->Subject = 'Permintaan Reset Password - Perpustakaan Muflih';
                            $mail->Body    = "Halo " . htmlspecialchars($user_name) . ",<br><br>" .
                                             "Kami menerima permintaan untuk mereset password akun Anda.<br>" .
                                             "Klik link berikut untuk mereset password Anda:<br>" .
                                             "<a href='" . $reset_link . "'>" . $reset_link . "</a><br><br>" .
                                             "Link ini akan kedaluwarsa dalam 30 menit.<br><br>" .
                                             "Jika Anda tidak meminta reset password, abaikan email ini.<br><br>" .
                                             "Terima kasih,<br>Tim Perpustakaan Muflih";
                            $mail->AltBody = "Halo " . htmlspecialchars($user_name) . ",\n\n" .
                                             "Kami menerima permintaan untuk mereset password akun Anda.\n" .
                                             "Salin dan tempel link berikut di browser Anda untuk mereset password Anda:\n" .
                                             $reset_link . "\n\n" .
                                             "Link ini akan kedaluwarsa dalam 30 menit.\n\n" .
                                             "Jika Anda tidak meminta reset password, abaikan email ini.\n\n" .
                                             "Terima kasih,\nTim Perpustakaan Muflih";

                            if ($mail->send()) {
                                $message = "Jika alamat email Anda terdaftar, link reset password telah dikirim. Silakan periksa inbox dan folder spam Anda.";
                            } else {
                                $error = "Gagal mengirim email. Silakan coba lagi nanti.";
                                error_log("PHPMailer send error: " . $mail->ErrorInfo . " | Email: " . $email . " | SMTP Host: " . SMTP_HOST);
                            }
                        } catch (Exception $e) {
                            $error = "Gagal mengirim email. Silakan coba lagi nanti.";
                            error_log("PHPMailer exception: " . $e->getMessage() . " | Email: " . $email . " | SMTP Host: " . SMTP_HOST);
                        }
                    } else {
                        $error = "Gagal menyimpan token reset. Silakan coba lagi. Error: " . htmlspecialchars($update_stmt->error);
                    }
                    $update_stmt->close();
                }
            } else {
                // Security: Do not reveal if the email is registered or not.
                $message = "Jika alamat email Anda terdaftar, link reset password akan dikirim. Silakan periksa inbox dan folder spam Anda.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Perpustakaan Muflih</title>
    <link rel="stylesheet" href="assets/bootstrap.css/css/theme.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: sans-serif;
        }
        .container {
            max-width: 450px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center; /* Center align header text */
        }
        .form-header img {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .form-header h2 {
            margin-bottom: 10px;
            color: #333;
        }
        .form-header p {
            margin-bottom: 25px;
            color: #666;
            font-size: 0.95em;
        }
        .alert {
            text-align: left; /* Align alert text to left */
            font-size: 0.9em;
        }
        .form-label {
            text-align: left;
            display: block; /* Make label take full width */
            margin-bottom: 5px;
        }
        .btn-custom {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 10px 15px;
            font-size: 1em;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .login-link {
            margin-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <img src="assets/logosmk.png" alt="Logo Perpustakaan Muflih">
            <h2>Lupa Password</h2>
            <p>Masukkan alamat email Anda. Kami akan mengirimkan link untuk mereset password Anda.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php 
        // Show form if there's no success message, OR if there was an error (to allow re-try)
        if (empty($message) || !empty($error)): 
        ?>
        <form action="forgot_password.php" method="post" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Kirim Link Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="login-link">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>

    <script src="assets/bootstrap.js/bootstrap.bundle.min.js"></script>
</body>
</html>
