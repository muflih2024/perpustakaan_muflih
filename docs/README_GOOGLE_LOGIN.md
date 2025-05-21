# Panduan Login Google untuk Aplikasi Perpustakaan

Dokumen ini menjelaskan cara menggunakan dan mengimplementasikan fitur login dengan Google untuk aplikasi Perpustakaan Muflih.

## Daftar Isi
1. [Overview](#overview)
2. [Alur Kerja](#alur-kerja)
3. [File-File Terkait](#file-file-terkait)
4. [Konfigurasi](#konfigurasi)
5. [Penggunaan](#penggunaan)
6. [Troubleshooting](#troubleshooting)

## Overview

Fitur login dengan Google memungkinkan pengguna untuk masuk ke aplikasi perpustakaan menggunakan akun Google mereka, tanpa perlu membuat password baru. Ini meningkatkan keamanan dan kemudahan penggunaan.

## Alur Kerja

1. Pengguna mengklik tombol "Login dengan Google" di halaman login
2. Sistem mengarahkan pengguna ke halaman login Google
3. Pengguna memberikan izin ke aplikasi untuk mengakses data profil
4. Google mengarahkan kembali pengguna ke aplikasi dengan kode otorisasi
5. Aplikasi memverifikasi kode dan mendapatkan data profil pengguna
6. Sistem memeriksa apakah email pengguna sudah terdaftar
   - Jika sudah terdaftar: Update informasi dan arahkan ke dashboard
   - Jika belum terdaftar: Buatkan akun baru dan arahkan ke dashboard

## File-File Terkait

- `config/google_config.php` - Konfigurasi Google OAuth
- `google_login.php` - Memulai proses login Google
- `google_callback.php` - Menangani callback setelah autentikasi Google
- `login.php` - Halaman login dengan tombol "Login dengan Google"

## Konfigurasi

Untuk mengatur kredensial Google OAuth:

1. Buka file `.env` di root aplikasi (buat jika belum ada)
2. Tambahkan kredensial Google Anda:
   ```
   GOOGLE_CLIENT_ID=your_client_id_here
   GOOGLE_CLIENT_SECRET=your_client_secret_here
   ```
   
3. Pastikan `.env` sudah ditambahkan ke `.gitignore`!

## Penggunaan

### Menambahkan Tombol Login dengan Google

Tambahkan kode berikut di halaman login Anda:

```html
<div class="mt-3 text-center">
  <p>-- atau --</p>
  <a href="google_login.php" class="btn btn-danger btn-block">
    <i class="fab fa-google"></i> Login dengan Google
  </a>
</div>
```

### Memeriksa Metode Login

Untuk memeriksa apakah user login dengan Google:

```php
if (isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'google') {
    // User login dengan Google
}
```

## Troubleshooting

### Masalah Umum

1. **Error "redirect_uri_mismatch"**
   - Solusi: Pastikan URL callback di Google Console sama persis dengan `$redirectUri` di konfigurasi

2. **Error "invalid_client"**
   - Solusi: Periksa Client ID dan Client Secret

3. **Pengguna tidak terdaftar setelah login**
   - Solusi: Periksa query insert di `google_callback.php` dan struktur tabel `users`