<?php
// Test script for email functionality
require_once '../vendor/autoload.php';
require_once 'send_email.php';

echo "<h1>Testing Email Functionality</h1>";

// Create a test message
$to = "muflih.rafileseppa@gmail.com"; // Replace with your test email
$subject = "Test Email from Perpustakaan System";
$message = "
<html>
<head>
    <title>Test Email</title>
</head>
<body>
    <h2>Test Email</h2>
    <p>This is a test email from the Perpustakaan system.</p>
    <p>If you received this email, the email system is working correctly.</p>
    <br>
    <p>Time sent: " . date('Y-m-d H:i:s') . "</p>
</body>
</html>
";

// Try sending the email with detailed output
echo "<h2>Email Configuration</h2>";
echo "<pre>";
require_once '../config/mail_config.php';
echo "MAIL_HOST: " . MAIL_HOST . "\n";
echo "MAIL_PORT: " . MAIL_PORT . "\n";
echo "MAIL_USERNAME: " . MAIL_USERNAME . "\n";
echo "MAIL_PASSWORD: " . substr(MAIL_PASSWORD, 0, 3) . "..." . substr(MAIL_PASSWORD, -3) . " (partially hidden)\n";
echo "MAIL_ENCRYPTION: " . MAIL_ENCRYPTION . "\n";
echo "MAIL_FROM_NAME: " . MAIL_FROM_NAME . "\n";
echo "</pre>";

echo "<h2>Attempting to send email...</h2>";

// Temporarily enable direct output for detailed error info
define('MAIL_DEBUG', 2); // Force debug output for this test

// Try sending the email
if (send_reset_email($to, $subject, $message)) {
    echo "<div style='color: green; font-weight: bold;'>Email sent successfully!</div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>Failed to send email.</div>";
    
    echo "<h3>Recent Error Logs:</h3>";
    echo "<pre>";
    if (file_exists('../logs/email_errors.txt')) {
        $errors = file_get_contents('../logs/email_errors.txt');
        echo htmlspecialchars($errors);
    } else {
        echo "No error logs found.";
    }
    echo "</pre>";
}

echo "<h2>Troubleshooting Steps:</h2>";
echo "<ol>";
echo "<li>Verify that the Gmail account <b>" . MAIL_USERNAME . "</b> exists and is accessible</li>";
echo "<li>Check that the App Password is correct and has been generated recently</li>";
echo "<li>Ensure that 2-Step Verification is enabled on the Gmail account</li>";
echo "<li>Generate a new App Password from <a href='https://myaccount.google.com/apppasswords' target='_blank'>Google Account Settings</a></li>";
echo "<li>Update the MAIL_PASSWORD in config/mail_config.php with the new App Password</li>";
echo "<li>Make sure your PHP installation has SSL extension enabled for secure SMTP connections</li>";
echo "</ol>";
