<?php
// File: config/env_loader.php
// Memuat variabel lingkungan dari file .env

/**
 * Fungsi sederhana untuk memuat variabel dari file .env
 * 
 * @param string $path Path ke file .env
 * @return bool True jika berhasil, false jika gagal
 */
function load_env($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsing variabel
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Hapus quotes jika ada
        if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }
        
        // Set variabel lingkungan
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
    
    return true;
}

// Coba muat .env file jika ada
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    load_env($env_path);
}

// Define the base URL
// Automatically detect if running on Vercel or locally
if (isset($_ENV['VERCEL_ENV']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false)) {
    define('BASE_URL', $_ENV['VERCEL_BASE_URL'] ?? 'https://perpustakaan-muflih.vercel.app/');
} else {
    define('BASE_URL', $_ENV['LOCAL_BASE_URL'] ?? 'http://localhost/perpustakaan_muflih/');
}
