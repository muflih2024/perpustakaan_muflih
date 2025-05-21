<?php
// File: google_login.php
// Menangani proses login dengan Google

// Include file konfigurasi
require_once 'config/env_loader.php';
require_once 'config/koneksi.php';
require_once 'config/google_config.php';
require_once 'vendor/autoload.php';

// Membuat objek Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope($scopes);

// Mendapatkan URL login Google
$authUrl = $client->createAuthUrl();

// Redirect pengguna ke halaman login Google
header("Location: " . $authUrl);
exit;