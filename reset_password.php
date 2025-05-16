<?php
session_start();
include 'config/koneksi.php'; // Your DB connection

$message = '';
$error = '';
$showForm = false; // Flag to control form visibility

if (!isset($_GET['token'])) {
    $_SESSION['error_message'] = "Token reset tidak valid atau tidak ditemukan.";
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Validate the token against the database
$stmt = $koneksi->prepare("SELECT id, email, reset_token_expires FROM users WHERE reset_token = ? LIMIT 1");
if (!$stmt) {
    $error = "Database error (prepare failed): " . htmlspecialchars($koneksi->error);
} else {
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($user['reset_token_expires'] >= time()) {
            // Token is valid and not expired
            $showForm = true;
            $user_id = $user['id'];
        } else {
            $error = "Token reset password telah kedaluwarsa. Silakan minta link baru.";
            // Optionally, clear the expired token from DB
            $clear_stmt = $koneksi->prepare("UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
            if ($clear_stmt) {
                $clear_stmt->bind_param("s", $token);
                $clear_stmt->execute();
                $clear_stmt->close();
            }
        }
    } else {
        $error = "Token reset password tidak valid. Pastikan Anda menggunakan link yang benar.";
    }
    $stmt->close();
}

if ($showForm && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || empty($confirm_password)) {
        $error = "Silakan masukkan dan konfirmasi password baru Anda.";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok.";
    } elseif (strlen($password) < 8) { // Enforce minimum password length
        $error = "Password minimal harus 8 karakter.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password in the database and clear the reset token
        $update_stmt = $koneksi->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        if (!$update_stmt) {
            $error = "Database error (update prepare failed): " . htmlspecialchars($koneksi->error);
        } else {
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $message = "Password Anda telah berhasil direset. Anda sekarang dapat <a href='login.php'>login</a> dengan password baru Anda.";
                $showForm = false; // Hide form after successful reset
            } else {
                $error = "Terjadi kesalahan saat mereset password Anda. Silakan coba lagi. Error: " . htmlspecialchars($update_stmt->error);
            }
            $update_stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Perpustakaan Muflih</title>
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
            text-align: center;
        }
        .form-header img {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .form-header h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .alert {
            text-align: left;
            font-size: 0.9em;
        }
        .form-label {
            text-align: left;
            display: block;
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
        .action-link {
            margin-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
             <img src="assets/logosmk.png" alt="Logo Perpustakaan Muflih">
            <h2>Reset Password Anda</h2>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert"><?php echo $message; /* Already includes HTML link */ ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($showForm): ?>
        <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="post" novalidate>
            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="action-link">
            <?php if (!$showForm && !empty($error)): // If form is not shown due to an error (e.g. token invalid/expired) ?>
                <a href="forgot_password.php">Minta link reset baru</a>
            <?php elseif (empty($message) && empty($error) && !$showForm): // Generic link if no specific error but form isn't shown (e.g. initial load error) ?>
                 <a href="login.php">Kembali ke Login</a>
            <?php endif; ?>
        </div>

    </div>

    <script src="assets/bootstrap.js/bootstrap.bundle.min.js"></script>
</body>
</html>
