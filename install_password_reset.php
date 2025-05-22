<?php
// Script untuk membuat tabel password_reset jika belum ada

require_once __DIR__ . '/config/koneksi.php';

// Cek apakah tabel password_reset sudah ada
$check_table_query = "SHOW TABLES LIKE 'password_reset'";
$result = mysqli_query($koneksi, $check_table_query);

if (mysqli_num_rows($result) == 0) {
    // Tabel tidak ada, buat tabel baru
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
    
    if (mysqli_query($koneksi, $create_table_query)) {
        echo "Tabel password_reset berhasil dibuat.";
    } else {
        echo "Error saat membuat tabel password_reset: " . mysqli_error($koneksi);
    }
} else {
    echo "Tabel password_reset sudah ada.";
}

// Tutup koneksi
mysqli_close($koneksi);
?>
