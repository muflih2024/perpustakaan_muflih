# PowerShell script untuk deployment ke Vercel dengan optimasi file limits
# For Perpustakaan Muflih project

Write-Host "=== OPTIMIZED VERCEL DEPLOYMENT ===" -ForegroundColor Cyan
Write-Host "Memulai deployment dengan archive mode untuk mengatasi batas file..." -ForegroundColor Green

# Pastikan direktori api ada
if (-not (Test-Path -Path "api")) {
    New-Item -ItemType Directory -Path "api" | Out-Null
    Write-Host "✓ Direktori api dibuat."
}

# Pastikan file index.php ada di direktori api
if (-not (Test-Path -Path "api\index.php")) {
    Copy-Item "index.php" -Destination "api\" -ErrorAction SilentlyContinue
    Write-Host "✓ File index.php disalin ke direktori api/"
} 

Write-Host "Deploying dengan archive mode (tgz) untuk mengatasi batas file..." -ForegroundColor Yellow
Write-Host ""

# Jalankan deployment dengan flag archive
vercel --archive=tgz

Write-Host ""
Write-Host "Setelah deployment, pastikan untuk:" -ForegroundColor Cyan
Write-Host "1. Verifikasi bahwa environment variables sudah dikonfigurasi di Vercel dashboard" -ForegroundColor White
Write-Host "2. Setup koneksi database external (PlanetScale, Railway, dll)" -ForegroundColor White
Write-Host "3. Cek log di Vercel dashboard jika terjadi error" -ForegroundColor White
