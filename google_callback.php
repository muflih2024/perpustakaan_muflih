<?php
// File: google_callback.php
// Menangani callback dari autentikasi Google

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

// Memproses kode autentikasi Google
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    
    // Mendapatkan data profil pengguna
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    // Mendapatkan informasi pengguna dari Google
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $google_id = $google_account_info->id;
    
    // Cek apakah pengguna sudah terdaftar
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Jika pengguna sudah terdaftar, update informasi login
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_method'] = 'google';
        
        // Update Google ID jika belum tersimpan
        if (empty($user['google_id'])) {
            $update_query = "UPDATE users SET google_id = '$google_id' WHERE id = " . $user['id'];
            mysqli_query($koneksi, $update_query);
        }
        
        // Redirect ke dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Jika pengguna belum terdaftar, tambahkan ke database
        $username = explode('@', $email)[0] . rand(100, 999);
        $password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $role = 'user'; // Default role untuk pengguna baru
        
        $insert_query = "INSERT INTO users (username, email, password, role, google_id) 
                        VALUES ('$username', '$email', '$password_hash', '$role', '$google_id')";
        
        if (mysqli_query($koneksi, $insert_query)) {
            $user_id = mysqli_insert_id($koneksi);
            
            // Set session untuk pengguna baru
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['login_method'] = 'google';
            
            // Redirect ke dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Gagal menyimpan ke database
            header("Location: login.php?error=Gagal menyimpan data pengguna");
            exit();
        }
    }
} else {
    // Jika tidak ada kode autentikasi, redirect ke login
    header("Location: login.php?error=Autentikasi Google gagal");
    exit();
}