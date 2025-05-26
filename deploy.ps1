#!/usr/bin/env pwsh
# Comprehensive Vercel deployment script
# This script ensures all necessary files are properly prepared for Vercel deployment

Write-Host "Starting Vercel deployment preparation..." -ForegroundColor Green

# Verify that necessary environment variables are set in Vercel
Write-Host "Checking environment variables..." -ForegroundColor Yellow
php vercel_prebuild.php

if ($LASTEXITCODE -ne 0) {
    Write-Host "Environment variable check failed! Please fix the issues before deploying." -ForegroundColor Red
    exit 1
} else {
    Write-Host "Environment variables check passed!" -ForegroundColor Green
}

# Ensure all necessary directories exist in the project
$directories = @("api", "auth", "pages", "config")
foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        Write-Host "Creating directory: $dir" -ForegroundColor Yellow
        New-Item -ItemType Directory -Path $dir -Force
    }
}

# Confirm router configuration is correct
Write-Host "Checking router configuration..." -ForegroundColor Yellow
$routerPath = "api/_router.php"
if (Test-Path $routerPath) {
    Write-Host "Router file exists. Ensuring it's properly configured..." -ForegroundColor Green
} else {
    Write-Host "Router file missing! Creating default router..." -ForegroundColor Red
    Copy-Item "api/_router.example.php" -Destination $routerPath -ErrorAction SilentlyContinue
}

# Confirm vercel.json is correct
Write-Host "Checking Vercel configuration..." -ForegroundColor Yellow
$vercelJsonPath = "vercel.json"
if (Test-Path $vercelJsonPath) {
    Write-Host "Vercel configuration exists." -ForegroundColor Green
} else {
    Write-Host "Vercel configuration missing! Creating default config..." -ForegroundColor Red
    $vercelJson = @{
        version = 2
        functions = @{
            "api/_router.php" = @{
                runtime = "vercel-php@0.7.1"
            }
        }
        routes = @(
            @{
                src = "/assets/(.*)"
                dest = "/assets/`$1"
            },
            @{
                src = "/(.*)"
                dest = "/api/_router.php"
            }
        )
    } | ConvertTo-Json -Depth 10
    Set-Content -Path $vercelJsonPath -Value $vercelJson
}

# Deploy to Vercel
Write-Host "Ready to deploy to Vercel. Do you want to continue? (y/n)" -ForegroundColor Cyan
$confirm = Read-Host
if ($confirm -eq "y") {
    Write-Host "Deploying to Vercel..." -ForegroundColor Yellow
    vercel --prod
    
    Write-Host "Deployment complete!" -ForegroundColor Green
    Write-Host "IMPORTANT NOTES:" -ForegroundColor Yellow
    Write-Host "1. For full functionality, make sure your database tables are set up correctly in Supabase/PostgreSQL." -ForegroundColor Cyan
    Write-Host "2. You should run the SQL files from /sql/supabase.sql in your Supabase instance." -ForegroundColor Cyan
    Write-Host "3. To check if your deployment is working, visit your Vercel URL and check the status page." -ForegroundColor Cyan
} else {
    Write-Host "Deployment cancelled." -ForegroundColor Red
}
