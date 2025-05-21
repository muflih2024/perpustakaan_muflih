<?php
// File: google_callback.example.php
// Example template for Google callback process

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

// Memproses kode autentikasi Google
if (isset($_GET['code'])) {
    // Fetch access token and handle any errors
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            // Log error and redirect to login page with error message
            error_log('Google Auth Error: ' . $token['error_description']);
            header('Location: login.php?error=Google authentication failed: ' . urlencode($token['error_description']));
            exit;
        }
        $client->setAccessToken($token);
    } catch (Exception $e) {
        // Log error and redirect to login page with error message
        error_log('Google Auth Exception: ' . $e->getMessage());
        header('Location: login.php?error=Google authentication failed: ' . urlencode($e->getMessage()));
        exit;
    }
    
    // Mendapatkan data profil pengguna
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    // Mendapatkan informasi pengguna dari Google
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $google_id = $google_account_info->id;
    
    // Cek apakah pengguna sudah terdaftar
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Jika pengguna sudah terdaftar, update informasi login
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_method'] = 'google';
        
        // Update Google ID jika belum tersimpan
        if (empty($user['google_id'])) {
            $update_query = "UPDATE users SET google_id = ? WHERE id = ?";
            $update_stmt = $koneksi->prepare($update_query);
            $update_stmt->bind_param("si", $google_id, $user['id']);
            $update_stmt->execute();
        }
        
        // Redirect ke dashboard
        header("Location: ../dashboard.php");
        exit();
    } else {
        // Jika pengguna belum terdaftar, tambahkan ke database
        $username = explode('@', $email)[0] . rand(100, 999);
        $password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $role = 'user'; // Default role untuk pengguna baru
        
        $insert_query = "INSERT INTO users (username, email, password, role, google_id) 
                        VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $koneksi->prepare($insert_query);
        $insert_stmt->bind_param("sssss", $username, $email, $password_hash, $role, $google_id);
        
        if ($insert_stmt->execute()) {
            $user_id = $koneksi->insert_id;
            
            // Set session untuk pengguna baru
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['login_method'] = 'google';
            
            // Redirect ke dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Gagal menyimpan ke database
            header("Location: ./login.php?error=Gagal menyimpan data pengguna");
            exit();
        }
    }
} else {
    // Jika tidak ada kode autentikasi, redirect ke login
    header("Location: ./login.php?error=Autentikasi Google gagal");
    exit();
}
