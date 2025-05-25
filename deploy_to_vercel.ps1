# Script untuk deploy ke Vercel (PowerShell)

Write-Host "===== Deployment Script untuk Vercel ====="
Write-Host "Memulai deployment aplikasi Perpustakaan Muflih ke Vercel..."

# Periksa jika Vercel CLI terinstal
if (!(Get-Command vercel -ErrorAction SilentlyContinue)) {
    Write-Host "Vercel CLI tidak ditemukan. Menginstal..."
    npm install -g vercel
}

# Login jika belum
try {
    $whoami = vercel whoami
} catch {
    Write-Host "Harap login ke Vercel:"
    vercel login
}

# Periksa file konfigurasi
if (!(Test-Path "vercel.json")) {
    Write-Host "ERROR: vercel.json tidak ditemukan!" -ForegroundColor Red
    exit 1
}

Write-Host "Melakukan deployment ke Vercel..."
vercel --prod

Write-Host ""
Write-Host "===== PENTING =====" -ForegroundColor Yellow
Write-Host "Pastikan environment variables sudah dikonfigurasi di dashboard Vercel:" -ForegroundColor Yellow
Write-Host "- GOOGLE_CLIENT_ID"
Write-Host "- GOOGLE_CLIENT_SECRET" 
Write-Host "- DB_HOST"
Write-Host "- DB_USER"
Write-Host "- DB_PASS"
Write-Host "- DB_NAME"
Write-Host "- VERCEL_BASE_URL"
Write-Host ""
Write-Host "Jangan lupa untuk memperbarui redirect URI di Google Cloud Console!" -ForegroundColor Yellow
Write-Host "===== Deployment selesai =====" -ForegroundColor Green
