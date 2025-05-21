# Pengaturan Login Google untuk Aplikasi Perpustakaan

Dokumen ini menjelaskan bagaimana mengatur dan mengkonfigurasi fitur login dengan Google untuk aplikasi Perpustakaan.

## Prasyarat
1. Akun Google Developer
2. Akses ke Google Cloud Console
3. PHP dengan ekstensi cURL dan JSON diaktifkan
4. Composer untuk instalasi library Google API Client

## Langkah-Langkah Pengaturan

### 1. Buat Project di Google Cloud Console
1. Kunjungi [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru dengan mengklik "Select a project" > "NEW PROJECT"
3. Berikan nama project (misal: "Perpustakaan Muflih")
4. Klik "CREATE"

### 2. Konfigurasi OAuth Consent Screen
1. Di panel sidebar, klik "APIs & Services" > "OAuth consent screen"
2. Pilih jenis user (External atau Internal)
3. Isi informasi aplikasi (nama aplikasi, email, dll)
4. Tambahkan scope yang diperlukan (biasanya cukup `email` dan `profile`)
5. Klik "SAVE AND CONTINUE"

### 3. Buat Kredensial OAuth Client
1. Di panel sidebar, klik "APIs & Services" > "Credentials"
2. Klik "CREATE CREDENTIALS" > "OAuth client ID"
3. Pilih "Web application" sebagai tipe aplikasi
4. Berikan nama (misal: "Perpustakaan Web Client")
5. Tambahkan URL berikut pada "Authorized JavaScript origins":
   ```
   http://localhost
   ```
6. Tambahkan URL berikut pada "Authorized redirect URIs":
   ```
   http://localhost/perpustakaan_muflih/google_callback.php
   ```
7. Klik "CREATE"
8. Salin "Client ID" dan "Client Secret" yang dihasilkan

### 4. Konfigurasi Aplikasi
1. Buka file `.env` di root aplikasi (buat jika belum ada)
2. Tambahkan kredensial Google Anda:
   ```
   GOOGLE_CLIENT_ID=your_client_id_here
   GOOGLE_CLIENT_SECRET=your_client_secret_here
   ```
   **PENTING:** Pastikan `.env` sudah ditambahkan ke `.gitignore` agar kredensial tidak tercatat di Git!

3. Alternatif: Atur environment variables di server Anda:
   ```
   SetEnv GOOGLE_CLIENT_ID your_client_id_here
   SetEnv GOOGLE_CLIENT_SECRET your_client_secret_here
   ```

4. Menggunakan file contoh (example files):
   - Copy file contoh ke file aktual:
   ```
   copy auth\google_login.example.php auth\google_login.php
   copy auth\google_callback.example.php auth\google_callback.php
   ```
   - File contoh tidak berisi kredensial sensitif dan aman untuk di-commit ke repository
   - File aktual (`google_login.php` dan `google_callback.php`) sudah ditambahkan ke `.gitignore`

## Keamanan dan Praktik Terbaik
1. JANGAN PERNAH menyimpan kredensial OAuth langsung di kode
2. SELALU gunakan environment variables atau file `.env` yang ditambahkan ke `.gitignore`
3. Batasi scope OAuth ke yang diperlukan saja
4. Secara berkala rotasi kredensial Client Secret

## Troubleshooting
- Error "invalid_request": Periksa redirect URI, pastikan sama persis dengan yang didaftarkan
- Error "redirect_uri_mismatch": Pastikan URL callback sudah terdaftar di Google Cloud Console
- Error "invalid_client": Client ID atau Secret salah