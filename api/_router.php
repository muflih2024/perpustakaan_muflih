<?php
// api/_router.php
// This will map all requests to appropriate files
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string
if (strpos($request_uri, '?') !== false) {
    $request_uri = substr($request_uri, 0, strpos($request_uri, '?'));
}

// Remove leading slash
$request_uri = ltrim($request_uri, '/');

// Default to index
if (empty($request_uri)) {
    $request_uri = 'index';
}

// Map to PHP file
$file = dirname(__DIR__) . '/' . $request_uri;

// If direct PHP file exists, include it
if (file_exists($file . '.php')) {
    include $file . '.php';
    exit;
}

// If directory and index.php exists, include it
if (is_dir($file) && file_exists($file . '/index.php')) {
    include $file . '/index.php';
    exit;
}

// Default to main index.php
include dirname(__DIR__) . '/index.php';
?>
