<?php
// File: config/google_config.example.php
// CONTOH KONFIGURASI - SALIN KE google_config.php DAN ISI DENGAN KREDENSIAL ANDA SENDIRI

// Menggunakan environment variables untuk menyimpan kredensial (lebih aman)
// Jika Anda menggunakan hosting, atur environment variables di panel hosting
// Jika di localhost, gunakan .env file (tambahkan ke .gitignore)

// Cara aman mendapatkan kredensial Google
function getGoogleClientID() {
    // Cek apakah environment variable tersedia
    if (getenv('GOOGLE_CLIENT_ID')) {
        return getenv('GOOGLE_CLIENT_ID');
    }
    // Jika tidak ada environment variable, gunakan default untuk development
    // PERHATIAN: Jangan commit nilai aslinya ke GitHub!
    return 'YOUR_GOOGLE_CLIENT_ID'; // Ganti dengan ID Anda di file .env
}

function getGoogleClientSecret() {
    // Cek apakah environment variable tersedia
    if (getenv('GOOGLE_CLIENT_SECRET')) {
        return getenv('GOOGLE_CLIENT_SECRET');
    }
    // Jika tidak ada environment variable, gunakan default untuk development
    // PERHATIAN: Jangan commit nilai aslinya ke GitHub!
    return 'YOUR_GOOGLE_CLIENT_SECRET'; // Ganti dengan Secret Anda di file .env
}

// Konfigurasi client
$clientID = getGoogleClientID();
$clientSecret = getGoogleClientSecret();
$redirectUri = 'http://localhost/perpustakaan_muflih/auth/google_callback.php';

// Scope yang dibutuhkan
$scopes = [
    'email',
    'profile',
];
?>
