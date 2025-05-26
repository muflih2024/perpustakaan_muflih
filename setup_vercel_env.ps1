#!/usr/bin/env pwsh
# setup_vercel_env.ps1
# This script helps you set up Vercel environment variables for your deployment

Write-Host "Perpustakaan Muflih - Vercel Environment Setup" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This script will guide you through setting up the required environment variables"
Write-Host "for your Vercel deployment. Make sure you have the Vercel CLI installed and"
Write-Host "you are logged in to your Vercel account."
Write-Host ""
Write-Host "First, let's check if you're logged in to Vercel:" -ForegroundColor Yellow
vercel whoami

if ($LASTEXITCODE -ne 0) {
    Write-Host "You're not logged in to Vercel. Please log in first:" -ForegroundColor Red
    vercel login
}

Write-Host ""
Write-Host "Let's configure your environment variables for the project:" -ForegroundColor Green
Write-Host "These will be used to connect to your Supabase PostgreSQL database" -ForegroundColor Green
Write-Host ""

# Collect database information
$dbHost = Read-Host "Enter your Supabase Database Host (e.g., db.xyz123.supabase.co)"
$dbUser = Read-Host "Enter your Supabase Database User (usually 'postgres')"
$dbPass = Read-Host "Enter your Supabase Database Password" -AsSecureString
$dbName = Read-Host "Enter your Supabase Database Name (usually 'postgres')"

# Convert secure string to plain text (for Vercel)
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass)
$dbPassPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
[System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($BSTR)

# Google OAuth settings
Write-Host ""
Write-Host "Would you like to configure Google OAuth login? (y/n)" -ForegroundColor Yellow
$configGoogle = Read-Host

if ($configGoogle -eq "y") {
    Write-Host "You'll need to create a project in Google Cloud Console and set up OAuth credentials." -ForegroundColor Yellow
    Write-Host "Visit: https://console.cloud.google.com/apis/credentials" -ForegroundColor Cyan
    Write-Host ""
    
    $googleClientId = Read-Host "Enter your Google Client ID"
    $googleClientSecret = Read-Host "Enter your Google Client Secret" -AsSecureString
    
    # Convert secure string to plain text
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($googleClientSecret)
    $googleClientSecretPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($BSTR)
} else {
    $googleClientId = "dummy-client-id"
    $googleClientSecretPlain = "dummy-client-secret"
}

# Set up Vercel URL
Write-Host ""
Write-Host "Enter the URL of your Vercel deployment" -ForegroundColor Yellow
Write-Host "This will be used for redirects and links in your application" -ForegroundColor Yellow
Write-Host "Example: https://perpustakaan-muflih.vercel.app" -ForegroundColor Cyan
$vercelBaseUrl = Read-Host "Vercel URL"

# Ensure URL ends with /
if (-not $vercelBaseUrl.EndsWith('/')) {
    $vercelBaseUrl = $vercelBaseUrl + '/'
}

Write-Host ""
Write-Host "Setting up environment variables in Vercel..." -ForegroundColor Green

# Set environment variables in Vercel
vercel env add VERCEL "1"
vercel env add DB_HOST $dbHost
vercel env add DB_USER $dbUser
vercel env add DB_PASS $dbPassPlain
vercel env add DB_NAME $dbName
vercel env add DB_PORT "5432"
vercel env add VERCEL_BASE_URL $vercelBaseUrl
vercel env add GOOGLE_CLIENT_ID $googleClientId
vercel env add GOOGLE_CLIENT_SECRET $googleClientSecretPlain

Write-Host ""
Write-Host "Environment variables have been set up." -ForegroundColor Green
Write-Host "You are now ready to deploy your project with:" -ForegroundColor Green
Write-Host "./deploy.ps1" -ForegroundColor Cyan
