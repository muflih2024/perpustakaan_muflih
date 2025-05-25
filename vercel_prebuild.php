<?php
// Pre-deployment script for Vercel
// This script will run before deployment to verify environment variables

// List of required environment variables for Vercel
$requiredEnvVars = [
    'GOOGLE_CLIENT_ID',
    'GOOGLE_CLIENT_SECRET',
    'DB_HOST',
    'DB_USER',
    'DB_PASS',
    'DB_NAME',
    'VERCEL_BASE_URL'
];

// Check if any of these are missing
$missing = [];
foreach ($requiredEnvVars as $var) {
    if (!getenv($var)) {
        $missing[] = $var;
    }
}

if (!empty($missing)) {
    echo "ERROR: The following environment variables are missing:\n";
    foreach ($missing as $var) {
        echo "- $var\n";
    }
    echo "\nPlease add these to your Vercel environment variables.\n";
    exit(1);
}

echo "All required environment variables are set.\n";
echo "Ready for deployment!\n";
?>
