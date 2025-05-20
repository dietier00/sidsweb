<?php
// PHPMailer configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendMail($to, $subject, $body, $from_email, $from_name) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                           // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                      // Gmail SMTP server
        $mail->SMTPAuth   = true;                                  // Enable SMTP authentication
        $mail->Username   = 'dyterljfederiz@gmail.com';           // Your Gmail address
        $mail->Password   = 'adwq fzby fktu frrg';             // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          // Enable implicit TLS encryption
        $mail->Port       = 465;                                   // TCP port to connect to (465 for SSL)

        // Recipients - dyterljfederiz@gmail.com should be the recipient
        $mail->setFrom($from_email, $from_name);                  // From the customer
        $mail->addAddress('dyterljfederiz@gmail.com', 'Skye Interior Design Services'); // To your inbox
        $mail->addReplyTo($from_email, $from_name);              // Reply goes back to customer

        // Content
        $mail->isHTML(true);                                      // Set email format to HTML
        $mail->Subject = $subject;
        
        // Create professional HTML email template
        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                <h2 style='color: #333; margin-top: 0;'>New Contact Form Submission</h2>
                <p style='color: #666;'>You have received a new e-mail from your website contact form.</p>
            </div>
            
            <div style='background-color: white; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6;'>
                <h3 style='color: #333; margin-top: 0;'>Contact Details</h3>
                $body
            </div>
            
            <div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666; font-size: 12px;'>
                <p>This e-mail was sent from Skye Blinds contact form.</p>
                <p>© " . date('Y') . " Skye Blinds Interior Design Services. All rights reserved.</p>
            </div>
        </div>";
        
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($body);                      // Plain text version

        ob_start();
        $mail->send();
        $debug_output = ob_get_clean();
        error_log("Email Debug Output: " . $debug_output);        // Log debug output
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}