<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Install PHPMailer jika belum ada
if (!file_exists('../vendor/phpmailer/phpmailer')) {
    // Check if Composer is installed
    $composer_check = shell_exec('composer --version');
    if (!$composer_check) {
        die("Composer tidak ditemukan. Silakan install Composer terlebih dahulu.");
    }
    
    // Install PHPMailer via Composer
    $install_result = shell_exec('cd .. && composer require phpmailer/phpmailer');
}

require_once '../vendor/autoload.php';

function send_reset_email($to, $subject, $message) {
    // Membuat objek PHPMailer
    $mail = new PHPMailer(true);
    
    try {        // Load email configuration
        require_once '../config/mail_config.php';
        
        // Pengaturan SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;        // Enable debugging in development mode - outputs to error log
        $mail->SMTPDebug = defined('MAIL_DEBUG') ? MAIL_DEBUG : 2; // Use config setting if available
        $mail->Debugoutput = 'error_log';
        
        // Pengirim dan Penerima
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        
        // Log activity
        file_put_contents('../logs/email_logs.txt', 
            date('[Y-m-d H:i:s]') . " Email sent to: {$to}, Subject: {$subject}\n", 
            FILE_APPEND);
            
        return true;    } catch (Exception $e) {
        // Log detailed error information
        $errorMsg = date('[Y-m-d H:i:s]') . " Email error: " . $e->getMessage();
        
        // Add SMTP specific error info if available
        if (!empty($mail->ErrorInfo)) {
            $errorMsg .= " (SMTP: {$mail->ErrorInfo})";
        }
        
        // Add recipient information
        $errorMsg .= ", To: {$to}\n";
        
        file_put_contents('../logs/email_errors.txt', $errorMsg, FILE_APPEND);
        
        // In development, you might want to see the error directly
        error_log("Failed to send email: " . $e->getMessage());
        
        return false;
    }
}
