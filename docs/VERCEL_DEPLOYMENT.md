# Perpustakaan Muflih - Vercel Deployment Guide

## Panduan Deployment ke Vercel

### Persiapan

1. Pastikan Anda memiliki akun di [Vercel](https://vercel.com/)
2. Install Vercel CLI:
```bash
npm install -g vercel
```

### Deployment

1. Login ke Vercel dari terminal:
```bash
vercel login
```

2. Deploy proyek:
```bash
vercel
```

3. Jawab pertanyaan yang muncul:
   - Set up and deploy: `Y`
   - Which scope: Pilih akun atau tim Anda
   - Link to existing project: `N`
   - Project name: `perpustakaan-muflih` (atau nama yang Anda inginkan)
   - In which directory is your code: `.` (root directory)
   - Want to override settings: `N`

### Environment Variables

Setelah deployment pertama, buat environment variables berikut di dashboard Vercel:

1. `GOOGLE_CLIENT_ID` - Client ID untuk Google OAuth
2. `GOOGLE_CLIENT_SECRET` - Client Secret untuk Google OAuth
3. `DB_HOST` - Host database MySQL Anda
4. `DB_USER` - Username database
5. `DB_PASS` - Password database
6. `DB_NAME` - Nama database
7. `VERCEL_BASE_URL` - URL aplikasi di Vercel (contoh: https://perpustakaan-muflih.vercel.app/)

### Database

Untuk production, Anda perlu menggunakan layanan database eksternal seperti:
- PlanetScale
- Railway
- Amazon RDS
- DigitalOcean Managed Database

Pastikan untuk memperbarui kredensial database di environment variables Vercel.

### Memperbarui Aplikasi

Untuk update aplikasi yang sudah di-deploy:
```bash
vercel --prod
```

## Troubleshooting

Jika mengalami masalah saat deployment:
1. Periksa log di dashboard Vercel
2. Pastikan semua environment variables sudah dikonfigurasi dengan benar
3. Periksa apakah database dapat diakses dari Vercel
