<?php
require_once 'db_connect.php';
require_once 'mailer_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $facebook = $_POST['facebook'] ?? '';
    $viber = $_POST['viber'] ?? '';
    $preferred_contact = $_POST['preferred_contact'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    try {
        $sql = "INSERT INTO contact_messages (name, email, facebook, viber, preferred_contact, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name, $email, $facebook, $viber, $preferred_contact, $subject, $message);
        $stmt->execute();
        
        // Send notification email to admin
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'Contact Form');
        $mail->addAddress($_ENV['ADMIN_EMAIL']);
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission';
        
        $mailBody = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            " . ($facebook ? "<p><strong>Facebook:</strong> {$facebook}</p>" : "") . "
            " . ($viber ? "<p><strong>Viber:</strong> {$viber}</p>" : "") . "
            <p><strong>Preferred Contact Method:</strong> {$preferred_contact}</p>
            <p><strong>Subject:</strong> {$subject}</p>
            <p><strong>Message:</strong></p>
            <p>{$message}</p>
        ";
        
        $mail->Body = $mailBody;
        $mail->send();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Contact form error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}