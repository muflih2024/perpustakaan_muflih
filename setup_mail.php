<?php
// Check if mail_config.php exists, if not create it
$configFile = __DIR__ . '/config/mail_config.php';

if (!file_exists($configFile)) {
    $mailConfig = <<<'EOT'
<?php
// Email configuration
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'perpustakaanmuflih@gmail.com');
define('MAIL_PASSWORD', '456RTY#@');  // Use an App Password instead of your regular account password
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_NAME', 'Perpustakaan Muflih');
EOT;

    if (file_put_contents($configFile, $mailConfig)) {
        echo "Mail config file created successfully.";
    } else {
        echo "Failed to create mail config file.";
    }
} else {
    echo "Mail config file already exists.";
}

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    if (mkdir($logsDir, 0755, true)) {
        echo "<br>Logs directory created.";
    } else {
        echo "<br>Failed to create logs directory.";
    }
} else {
    echo "<br>Logs directory already exists.";
}

// Create email logs files if they don't exist
$emailLogsFile = $logsDir . '/email_logs.txt';
$emailErrorsFile = $logsDir . '/email_errors.txt';

if (!file_exists($emailLogsFile)) {
    if (file_put_contents($emailLogsFile, '')) {
        echo "<br>Email logs file created.";
    } else {
        echo "<br>Failed to create email logs file.";
    }
} else {
    echo "<br>Email logs file already exists.";
}

if (!file_exists($emailErrorsFile)) {
    if (file_put_contents($emailErrorsFile, '')) {
        echo "<br>Email errors file created.";
    } else {
        echo "<br>Failed to create email errors file.";
    }
} else {
    echo "<br>Email errors file already exists.";
}

echo "<br><br>Setup completed. <a href='dashboard.php'>Go to Dashboard</a>";
?>
