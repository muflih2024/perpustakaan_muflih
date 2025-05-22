# Sistem Reset Password

## Gambaran Umum

Sistem reset password memungkinkan pengguna untuk mereset password mereka jika lupa. Sistem ini menggunakan email untuk mengirim link reset password yang valid selama 1 jam.

## Fitur

1. Form permintaan reset password (meminta email)
2. Pengiriman email dengan link reset password
3. Form untuk membuat password baru
4. Konfirmasi perubahan password melalui email

## Cara Kerja

### Proses Reset Password

1. Pengguna mengklik link "Lupa Password" di halaman login.
2. Pengguna memasukkan alamat email mereka di form lupa password.
3. Sistem memeriksa apakah email tersebut terdaftar di database.
4. Jika email terdaftar, sistem:
   - Menghapus token reset password lama jika ada
   - Membuat token baru yang berlaku selama 1 jam
   - Menyimpan token ke database
   - Mengirim email yang berisi link reset password
5. Pengguna mengklik link reset password dari email mereka.
6. Sistem memeriksa validitas token (apakah ada dan belum kadaluarsa).
7. Pengguna memasukkan password baru dan konfirmasi password.
8. Sistem memeriksa apakah password memenuhi kriteria keamanan dan konfirmasi sama.
9. Setelah validasi berhasil, sistem:
   - Mengupdate password pengguna di database
   - Menghapus token reset password
   - Memberi konfirmasi bahwa password berhasil diubah
   - Mengirim email konfirmasi bahwa password telah diubah

### File-file Terkait

- `forgetpw/index.php` - Form untuk meminta reset password (meminta email)
- `forgetpw/reset.php` - Form untuk membuat password baru
- `forgetpw/send_email.php` - Fungsi untuk mengirim email
- `forgetpw/test_email.php` - Halaman untuk menguji pengiriman email (admin only)
- `sql/password_reset.sql` - SQL untuk membuat tabel password_reset
- `config/mail_config.php` - Konfigurasi email

## Konfigurasi Email

Untuk konfigurasi email, Anda perlu:

1. Memastikan file `config/mail_config.php` berisi informasi yang benar.
2. Untuk Gmail:
   - Gunakan App Password, bukan password akun utama
   - Aktifkan Two-Factor Authentication terlebih dahulu
   - Buat App Password di https://myaccount.google.com/security
   - Gunakan App Password sebagai password di konfigurasi

## Keamanan

- Token reset password dibuat menggunakan `random_bytes()` yang menghasilkan entropy tinggi
- Token berlaku hanya 1 jam
- Token dihapus setelah digunakan
- Email konfirmasi dikirim saat password berhasil diubah
- Password disimpan dengan hash menggunakan PASSWORD_DEFAULT (bcrypt)

## Troubleshooting

Jika pengguna tidak menerima email reset password:

1. Cek folder spam/junk di email mereka
2. Pastikan alamat email yang dimasukkan benar dan terdaftar
3. Pastikan konfigurasi SMTP di `config/mail_config.php` benar
4. Cek log email di `logs/email_logs.txt` dan `logs/email_errors.txt`
5. Gunakan `forgetpw/test_email.php` untuk menguji pengiriman email (admin only)
