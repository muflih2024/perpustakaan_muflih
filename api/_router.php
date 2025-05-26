<?php
// api/_router.php
// Enhanced router for Vercel deployment that properly handles all routes

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set environment for all requests
putenv('VERCEL=1');

// Define IS_VERCEL constant for use in included files
define('IS_VERCEL', true);  

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Debug log to help with troubleshooting
error_log("[ROUTER] Processing request: " . $request_uri);

// Remove query string if present
if (strpos($request_uri, '?') !== false) {
    $query_string = substr($request_uri, strpos($request_uri, '?') + 1);
    $_SERVER['QUERY_STRING'] = $query_string;
    parse_str($query_string, $_GET);
    $request_uri = substr($request_uri, 0, strpos($request_uri, '?'));
}

// Remove leading slash
$request_uri = ltrim($request_uri, '/');

// Default to index if empty
if (empty($request_uri)) {
    $request_uri = 'index';
}

// Project root directory
$project_root = dirname(__DIR__);

// Include necessary helper files
require_once $project_root . '/config/koneksi.php';
require_once $project_root . '/api/helpers.php';

// Map common URL patterns to appropriate PHP files
$routes = [
    'dashboard' => $project_root . '/dashboard.php',
    'auth/login' => $project_root . '/auth/login.php',
    'auth/register' => $project_root . '/auth/register.php',
    'auth/logout' => $project_root . '/auth/logout.php',
    'forgetpw' => $project_root . '/forgetpw/index.php',
    'forgetpw/reset' => $project_root . '/forgetpw/reset.php',
];

// Check if there's a direct route match
if (isset($routes[$request_uri])) {
    error_log("[ROUTER] Found direct route match: " . $routes[$request_uri]);
    include $routes[$request_uri];
    exit;
}

// Map to PHP file by path
$file_path = $project_root . '/' . $request_uri;
$php_file_path = $file_path . '.php';

error_log("[ROUTER] Looking for file: " . $php_file_path);

// Check for direct PHP file
if (file_exists($php_file_path)) {
    error_log("[ROUTER] Found direct PHP file: " . $php_file_path);
    include $php_file_path;
    exit;
}

// Check for directory with index.php
if (is_dir($file_path) && file_exists($file_path . '/index.php')) {
    error_log("[ROUTER] Found directory index: " . $file_path . '/index.php');
    include $file_path . '/index.php';
    exit;
}

// Handle auth paths - more specific matching
if (strpos($request_uri, 'auth/') === 0) {
    $auth_file = $project_root . '/' . $request_uri . '.php';
    if (file_exists($auth_file)) {
        error_log("[ROUTER] Found auth file: " . $auth_file);
        include $auth_file;
        exit;
    }
}

// Handle pages directory with subfolders
if (strpos($request_uri, 'pages/') === 0) {
    $pages_file = $project_root . '/' . $request_uri . '.php';
    if (file_exists($pages_file)) {
        error_log("[ROUTER] Found pages file: " . $pages_file);
        include $pages_file;
        exit;
    }
    
    // Try to find index.php in the pages subfolders
    $pages_dir = $project_root . '/' . $request_uri;
    if (is_dir($pages_dir) && file_exists($pages_dir . '/index.php')) {
        error_log("[ROUTER] Found pages directory index: " . $pages_dir . '/index.php');
        include $pages_dir . '/index.php';
        exit;
    }
}

// Default fallback to main index.php
error_log("[ROUTER] No specific file found, falling back to index.php");
include $project_root . '/index.php';
?>
