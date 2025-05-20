<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    try {
        // Check customers table first
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE customers SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expires, $email]);
        } else {
            // Check admins table if not found in customers
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $pdo->prepare("UPDATE admins SET reset_token = ?, reset_expires = ? WHERE email = ?");
                $stmt->execute([$token, $expires, $email]);
            }
        }

        if ($user || $admin) {
            // Send email with reset link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/../reset-password.php?token=" . $token;
            
            $to = $email;
            $subject = "Password Reset Request - Skye Blinds";
            $message = "Hello,\n\n";
            $message .= "You have requested to reset your password. Click the link below to reset it:\n\n";
            $message .= $resetLink . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you didn't request this, please ignore this email.\n\n";
            $message .= "Best regards,\nSkye Blinds Team";
            
            $headers = "From: noreply@skyeblinds.com";
            
            if(mail($to, $subject, $message, $headers)) {
                header("Location: ../forgot-password.php?status=sent");
            } else {
                header("Location: ../forgot-password.php?error=Failed to send email");
            }
        } else {
            // Don't reveal if email exists or not for security
            header("Location: ../forgot-password.php?status=sent");
        }
        exit();
    } catch(PDOException $e) {
        header("Location: ../forgot-password.php?error=An error occurred");
        exit();
    }
} else {
    header("Location: ../forgot-password.php");
    exit();
}
?>