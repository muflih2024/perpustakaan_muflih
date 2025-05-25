<?php

// Set a base path if your application relies on it.
// This might be useful for resolving includes, assets, etc.
// define('BASE_PATH', dirname(__DIR__));

// Change the current working directory to the project root.
// This can help with relative paths in your main application.
chdir(dirname(__DIR__));

// Include the main application entry point.
require dirname(__DIR__) . '/index.php';

?>
