# Skrip PowerShell untuk deployment ke Vercel
# Created for Perpustakaan Muflih project

Write-Host "=== PERSIAPAN DEPLOYMENT VERCEL ===" -ForegroundColor Green
Write-Host "Memulai persiapan deployment Perpustakaan Muflih ke Vercel..." -ForegroundColor Cyan

# Pastikan direktori api ada
if (-not (Test-Path -Path "api")) {
    New-Item -ItemType Directory -Path "api" | Out-Null
    Write-Host "✓ Direktori api dibuat." -ForegroundColor Green
} else {
    Write-Host "✓ Direktori api sudah ada." -ForegroundColor Green
}

# Pastikan file index.php ada di direktori api/
if (-not (Test-Path -Path "api\index.php")) {
    Copy-Item "index.php" -Destination "api\" -ErrorAction SilentlyContinue
    Write-Host "✓ File index.php disalin ke direktori api/" -ForegroundColor Green
} else {
    Write-Host "✓ File api/index.php sudah ada." -ForegroundColor Green
}

# Periksa file vercel.json
if (Test-Path -Path "vercel.json") {
    Write-Host "✓ File vercel.json sudah ada." -ForegroundColor Green
} else {
    Write-Host "⚠ File vercel.json tidak ditemukan. Membuat file baru..." -ForegroundColor Yellow
    @'
{
  "version": 2,
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [
    { "src": "/assets/(.*)", "dest": "/assets/$1" },
    { "src": "/(css|js|images|img|assets)/(.*)", "dest": "/$1/$2" },
    { "src": "/(.*)", "dest": "/api/index.php" }
  ],
  "env": {
    "APP_ENV": "production"
  }
}
'@ | Out-File -FilePath "vercel.json" -Encoding UTF8
    Write-Host "✓ File vercel.json dibuat." -ForegroundColor Green
}

# Periksa file .vercelignore
if (Test-Path -Path ".vercelignore") {
    Write-Host "✓ File .vercelignore sudah ada." -ForegroundColor Green
} else {
    Write-Host "⚠ File .vercelignore tidak ditemukan. Membuat file baru..." -ForegroundColor Yellow
    @'
# Ignore logs and temporary files
logs/
*.log
tmp/

# Ignore SQL files (not needed in production)
sql/
*.sql

# Ignore development files
node_modules/
.git/
.gitignore
.idea/
.vscode/

# Ignore sensitive data
.env
.env.*
!.env.example

# Do NOT ignore vendor - needed for Composer autoload
# vendor/
'@ | Out-File -FilePath ".vercelignore" -Encoding UTF8
    Write-Host "✓ File .vercelignore dibuat." -ForegroundColor Green
}

Write-Host ""
Write-Host "=== DEPLOYMENT KE VERCEL ===" -ForegroundColor Green
Write-Host "Menjalankan deployment ke Vercel..." -ForegroundColor Cyan
Write-Host ""

# Jalankan deployment
vercel

Write-Host ""
Write-Host "Jika deployment gagal, periksa error log dan pastikan konfigurasi environment variables di Vercel dashboard" -ForegroundColor Yellow
Write-Host "Lihat panduan lengkap di docs/VERCEL_DEPLOYMENT.md" -ForegroundColor Yellow
