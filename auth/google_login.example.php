<?php
// File: google_login.example.php
// Example template for Google login process

// Include file konfigurasi
require_once '../config/env_loader.php';
require_once '../config/koneksi.php';
require_once '../config/google_config.php';
require_once '../vendor/autoload.php';

// Get client credentials from environment variables
$google_client_id = getenv('GOOGLE_CLIENT_ID');
$google_client_secret = getenv('GOOGLE_CLIENT_SECRET');
$redirect_uri = 'http://localhost/perpustakaan_muflih/auth/google_callback.php';

// Membuat objek Google Client
$client = new Google_Client();
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setRedirectUri($redirect_uri);

// Define scopes
$google_scopes = ['email', 'profile'];
foreach($google_scopes as $scope) {
    $client->addScope($scope);
}

// Set parameter tambahan untuk selalu menampilkan layar pemilihan akun
$client->setPrompt('select_account');

// Mendapatkan URL login Google
$authUrl = $client->createAuthUrl();

// Redirect pengguna ke halaman login Google
header("Location: " . $authUrl);
exit;
