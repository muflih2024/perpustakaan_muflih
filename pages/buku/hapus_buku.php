<?php
require_once '../../config/koneksi.php';
check_login('admin');

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    header("Location: list_buku.php?error=ID buku tidak valid.");
    exit();
}

// Get book info first to retrieve image path
$image_filename = null;
$sql_get_image = "SELECT gambar FROM buku WHERE id = ?";
if ($stmt_img = mysqli_prepare($koneksi, $sql_get_image)) {
    mysqli_stmt_bind_param($stmt_img, "i", $book_id);
    if (mysqli_stmt_execute($stmt_img)) {
        mysqli_stmt_bind_result($stmt_img, $image_filename);
        mysqli_stmt_fetch($stmt_img);
    }
    mysqli_stmt_close($stmt_img);
}

// Delete the book from database
$sql = "DELETE FROM buku WHERE id = ?";
if ($stmt = mysqli_prepare($koneksi, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    if (mysqli_stmt_execute($stmt)) {
        // Delete the associated image file if it exists
        if (!empty($image_filename)) {
            $upload_dir = '../../assets/book_images/';
            $image_path = $upload_dir . $image_filename;
            if (file_exists($image_path) && $image_filename !== 'contoh.png') {
                @unlink($image_path);
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($koneksi);
        header("Location: list_buku.php?success=Buku berhasil dihapus.");
        exit();
    } else {
        header("Location: list_buku.php?error=Gagal menghapus buku: " . mysqli_stmt_error($stmt));
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: list_buku.php?error=Gagal menyiapkan statement: " . mysqli_error($koneksi));
    exit();
}

mysqli_close($koneksi);
?>
