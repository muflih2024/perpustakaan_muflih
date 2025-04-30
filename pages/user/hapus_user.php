<?php
require_once '../../config/koneksi.php';
check_login('admin'); // Admin only

$user_id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$logged_in_user_id = $_SESSION['user_id'];

$status = "error"; // Default status

if ($user_id_to_delete <= 0) {
    $message = "ID User tidak valid.";
} elseif ($user_id_to_delete == $logged_in_user_id) {
    $message = "Anda tidak dapat menghapus akun Anda sendiri.";
} else {
    // Optional: Add check to prevent deleting the very first admin user if needed
    // if ($user_id_to_delete == 1) { // Assuming ID 1 is the initial admin
    //     $message = "Tidak dapat menghapus user admin utama.";
    // } else {

        // Prepare delete statement
        $sql = "DELETE FROM users WHERE id = ?";

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id_to_delete);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $message = "User berhasil dihapus.";
                    $status = "success";
                } else {
                    $message = "User tidak ditemukan atau sudah dihapus.";
                }
            } else {
                $message = "Gagal menghapus user: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Gagal menyiapkan statement: " . mysqli_error($koneksi);
        }
    // }
}

mysqli_close($koneksi);

// Redirect back to the user list page with a message
header("Location: list_user.php?" . $status . "=" . urlencode($message));
exit();

?>
