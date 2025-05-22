# Cara Mengatasi Error "Gagal mengirim email reset password"

Jika Anda mengalami masalah dengan pengiriman email reset password, berikut adalah langkah-langkah untuk mengatasinya:

## Masalah dan Solusi

Berdasarkan log error yang ada, sistem mengalami masalah autentikasi saat mencoba mengirim email melalui SMTP Gmail:
```
SMTP Error: Could not authenticate.
```

Ini biasanya terjadi karena beberapa alasan:

1. Password aplikasi (App Password) tidak valid atau sudah kadaluarsa
2. Pengaturan keamanan Gmail yang membatasi akses
3. Two-Factor Authentication (2FA) belum diaktifkan

## Langkah-langkah Perbaikan

### 1. Aktifkan Verifikasi 2 Langkah (2-Step Verification)

Untuk bisa menggunakan App Password di Gmail, Anda harus mengaktifkan Verifikasi 2 Langkah terlebih dahulu:

1. Buka [Pengaturan Keamanan Google Account](https://myaccount.google.com/security)
2. Cari bagian "Masuk ke Google" dan pilih "Verifikasi 2 Langkah"
3. Ikuti petunjuk untuk mengaktifkan fitur tersebut

### 2. Buat App Password Baru

Setelah Verifikasi 2 Langkah aktif, buat App Password khusus untuk aplikasi PHP:

1. Buka [Pengaturan App Password](https://myaccount.google.com/apppasswords)
2. Di bagian "Pilih aplikasi", pilih "Lainnya (Nama khusus)"
3. Masukkan nama seperti "Perpustakaan PHP"
4. Klik "Buat" dan salin password 16 karakter yang muncul

### 3. Update Konfigurasi Email

Edit file `config/mail_config.php` dan update password dengan App Password baru:

```php
define('MAIL_PASSWORD', 'passwordbarududisini');  // Ganti dengan App Password baru
```

### 4. Tes Pengiriman Email

Untuk memastikan konfigurasi sudah benar:

1. Buka halaman `forgetpw/test_email_detailed.php` di browser
2. Lihat hasil dan pesan error (jika ada)
3. Jika berhasil, kembali ke halaman reset password dan coba lagi

## Catatan Penting

- App Password adalah string 16 karakter tanpa spasi
- Jangan menggunakan password Gmail biasa, harus menggunakan App Password
- Pastikan email pengirim (`perpustakaanmuflih@gmail.com`) adalah akun valid yang Anda miliki

Jika masih mengalami masalah, cek log error di `logs/email_errors.txt` untuk informasi lebih detail.
