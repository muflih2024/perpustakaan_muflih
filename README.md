# Perpustakaan Muflih - Sistem Informasi Perpustakaan

## Tentang Aplikasi

Perpustakaan Muflih adalah sistem informasi perpustakaan untuk pengelolaan buku dan peminjaman yang efisien dan mudah digunakan.

## Fitur

- Manajemen buku (tambah, edit, hapus, lihat)
- Manajemen peminjaman (pinjam, kembalikan, daftar peminjaman)
- Manajemen pengguna (tambah, edit, hapus, lihat)
- Login pengguna dengan username/email dan password
- Login dengan Google
- Fitur lupa password dengan reset via email
- Pembedaan hak akses berdasarkan role

## Dokumentasi

- [Panduan Login dengan Google](./docs/README_GOOGLE_LOGIN.md)
- [Setup Login dengan Google](./docs/GOOGLE_LOGIN_SETUP.md)
- [Sistem Reset Password](./docs/PASSWORD_RESET.md)

## Instalasi

1. Clone repositori ini ke direktori web server Anda (misalnya: htdocs untuk XAMPP)
2. Import file SQL dari folder `sql/perpustakaan_muflih.sql` ke database Anda
3. Konfigurasi koneksi database di file `config/koneksi.php`
4. Konfigurasi email untuk fitur lupa password di file `config/mail_config.php`
5. Jalankan `install_password_reset.php` untuk membuat tabel password_reset jika belum ada
6. Buka aplikasi melalui browser

## Konfigurasi Email (Untuk Fitur Lupa Password)

1. Edit file `config/mail_config.php`
2. Masukkan informasi SMTP yang sesuai
3. Untuk Gmail, gunakan App Password jika Two-Factor Authentication diaktifkan
4. Jalankan `setup_mail.php` untuk setup awal jika diperlukan
5. Tes pengiriman email melalui `forgetpw/test_email.php` (admin only)

## Login Default

- Admin:
  - Username: admin
  - Password: admin123
- Petugas:
  - Username: petugas
  - Password: petugas123
- Anggota:
  - Username: anggota
  - Password: anggota123

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Ekstensi PHP: mysqli, mbstring, json

## Teknologi yang Digunakan

- PHP
- MySQL
- Bootstrap 5
- jQuery
- PHPMailer (untuk pengiriman email)

## Deployment ke Vercel

Untuk informasi lengkap tentang deployment ke Vercel, baca panduan di [docs/VERCEL_DEPLOYMENT.md](./docs/VERCEL_DEPLOYMENT.md).

### Langkah Singkat
1. Salin `.env.vercel.example` dan terapkan di dashboard Vercel sebagai environment variables
2. Pastikan database eksternal sudah disiapkan (PlanetScale, Railway, dll)
3. Jalankan deployment menggunakan Vercel CLI atau hubungkan dengan repositori GitHub
4. Perbarui URL redirect di Google Cloud Console untuk OAuth

## Catatan Migrasi dari Google App Engine
Aplikasi ini sebelumnya dikonfigurasi untuk Google App Engine, namun sekarang telah dimigrasi ke Vercel deployment karena:
- Deployment dan konfigurasi lebih sederhana
- Integrasi yang lebih baik dengan Git
- Opsi database yang lebih fleksibel

## Kontributor

- Muflih (Developer)
