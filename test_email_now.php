<?php
// Simple direct test script for email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';
require_once 'config/mail_config.php';

echo "<h1>Testing Email Configuration</h1>";

try {
    // Create a PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = MAIL_PORT;
    
    // Debug settings
    $mail->SMTPDebug  = 2; // Debug mode
    $mail->Debugoutput = 'html'; // Output format
    
    // Recipients
    $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
    $mail->addAddress('muflih.rafileseppa@gmail.com'); // Add a recipient
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email dari Perpustakaan System';
    $mail->Body    = '<h2>Test Email</h2><p>Ini adalah email test dari sistem perpustakaan.</p><p>Waktu: '.date('Y-m-d H:i:s').'</p>';
    $mail->AltBody = 'Test email dari sistem perpustakaan. Waktu: '.date('Y-m-d H:i:s');
    
    // Send email
    if ($mail->send()) {
        echo "<h3 style='color:green;'>Email berhasil dikirim!</h3>";
    } else {
        echo "<h3 style='color:red;'>Gagal mengirim email.</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
}

// Current configuration
echo "<h3>Current Email Configuration:</h3>";
echo "<ul>";
echo "<li>Host: " . MAIL_HOST . "</li>";
echo "<li>Port: " . MAIL_PORT . "</li>";
echo "<li>Username: " . MAIL_USERNAME . "</li>";
echo "<li>Password: " . substr(MAIL_PASSWORD, 0, 2) . "..." . substr(MAIL_PASSWORD, -2) . " (hidden for security)</li>";
echo "<li>Encryption: " . MAIL_ENCRYPTION . "</li>";
echo "<li>From Name: " . MAIL_FROM_NAME . "</li>";
echo "</ul>";

// Log file contents
echo "<h3>Recent Error Log:</h3>";
echo "<pre style='background:#f0f0f0; padding:10px; max-height:300px; overflow:auto'>";
if (file_exists('logs/email_errors.txt')) {
    echo htmlspecialchars(file_get_contents('logs/email_errors.txt'));
} else {
    echo "No error log found.";
}
echo "</pre>";

// Steps to verify
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Access Gmail account <b>" . MAIL_USERNAME . "</b> and check if it's working</li>";
echo "<li>Verify that 2-Step Verification is enabled on the account</li>";
echo "<li>Confirm that the App Password has been correctly entered in config/mail_config.php</li>";
echo "<li>Try the password reset functionality again</li>";
echo "</ol>";
?>
