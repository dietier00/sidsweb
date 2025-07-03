<?php
require_once('sms_handler.php');
require_once('../php/mailer_config.php');
require_once('../php/config.php');

class NotificationHandler {
    private $sms;
    private $mailer;
    private $telegramBotToken = 'YOUR_TELEGRAM_BOT_TOKEN';
    private $adminChatId = 'ADMIN_CHAT_ID'; // Telegram chat ID for admin notifications

    public function __construct() {
        $this->sms = new SMSNotification();
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer() {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            $this->mailer->setFrom(SMTP_USERNAME, 'Skye Blinds');
        } catch (Exception $e) {
            error_log("Mailer setup failed: " . $e->getMessage());
        }
    }

    private function sendTelegramMessage($chatId, $message) {
        $url = "https://api.telegram.org/bot{$this->telegramBotToken}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function notifyNewOrder($orderId) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT o.*, c.email, c.phone, c.name,
                       GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR '\n') as items
                FROM orders o
                JOIN customers c ON o.user_id = c.id
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.id = ?
                GROUP BY o.id
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) return false;

            // Email notification
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($order['email']);
            $this->mailer->Subject = "Order Confirmation - #{$order['order_number']}";
            
            $emailBody = file_get_contents('../templates/email/order_confirmation.html');
            $emailBody = str_replace(
                ['{{customer_name}}', '{{order_number}}', '{{total_amount}}', '{{items}}'],
                [$order['name'], $order['order_number'], number_format($order['total_amount'], 2), nl2br($order['items'])],
                $emailBody
            );
            
            $this->mailer->msgHTML($emailBody);
            $this->mailer->send();

            // SMS notification
            $this->sms->sendOrderConfirmation($orderId);

            // Admin Telegram notification
            $adminMsg = "🛍️ New Order #{$order['order_number']}\n\n";
            $adminMsg .= "Customer: {$order['name']}\n";
            $adminMsg .= "Amount: ₱" . number_format($order['total_amount'], 2) . "\n";
            $adminMsg .= "Items:\n{$order['items']}\n\n";
            $adminMsg .= "Payment: " . ucfirst($order['payment_method']);
            
            $this->sendTelegramMessage($this->adminChatId, $adminMsg);

            // Log notification
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (
                    order_id, notification_type, recipient, status, sent_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$orderId, 'order_confirmation_email', $order['email'], 'sent']);
            $stmt->execute([$orderId, 'order_confirmation_sms', $order['phone'], 'sent']);
            $stmt->execute([$orderId, 'order_confirmation_telegram', $this->adminChatId, 'sent']);

            return true;

        } catch (Exception $e) {
            error_log("Notification failed for order #$orderId: " . $e->getMessage());
            return false;
        }
    }

    public function notifyPaymentConfirmation($orderId) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT o.*, c.email, c.phone, c.name
                FROM orders o
                JOIN customers c ON o.user_id = c.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) return false;

            // Email notification
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($order['email']);
            $this->mailer->Subject = "Payment Confirmed - Order #{$order['order_number']}";
            
            $emailBody = file_get_contents('../templates/email/payment_confirmation.html');
            $emailBody = str_replace(
                ['{{customer_name}}', '{{order_number}}', '{{total_amount}}'],
                [$order['name'], $order['order_number'], number_format($order['total_amount'], 2)],
                $emailBody
            );
            
            $this->mailer->msgHTML($emailBody);
            $this->mailer->send();

            // SMS notification
            $this->sms->sendPaymentConfirmation($orderId);

            // Admin Telegram notification
            $adminMsg = "💰 Payment Confirmed\n\n";
            $adminMsg .= "Order #{$order['order_number']}\n";
            $adminMsg .= "Customer: {$order['name']}\n";
            $adminMsg .= "Amount: ₱" . number_format($order['total_amount'], 2) . "\n";
            $adminMsg .= "Method: " . ucfirst($order['payment_method']);
            
            $this->sendTelegramMessage($this->adminChatId, $adminMsg);

            // Log notifications
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (
                    order_id, notification_type, recipient, status, sent_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$orderId, 'payment_confirmation_email', $order['email'], 'sent']);
            $stmt->execute([$orderId, 'payment_confirmation_sms', $order['phone'], 'sent']);
            $stmt->execute([$orderId, 'payment_confirmation_telegram', $this->adminChatId, 'sent']);

            return true;

        } catch (Exception $e) {
            error_log("Payment notification failed for order #$orderId: " . $e->getMessage());
            return false;
        }
    }

    public function notifyDeliveryUpdate($orderId, $status) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT o.*, c.email, c.phone, c.name
                FROM orders o
                JOIN customers c ON o.user_id = c.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) return false;

            $statusMessages = [
                'processing' => 'is being processed',
                'shipped' => 'has been shipped',
                'out_for_delivery' => 'is out for delivery',
                'delivered' => 'has been delivered'
            ];

            $message = $statusMessages[$status] ?? 'has been updated';

            // Email notification
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($order['email']);
            $this->mailer->Subject = "Order Status Update - #{$order['order_number']}";
            
            $emailBody = file_get_contents('../templates/email/delivery_update.html');
            $emailBody = str_replace(
                ['{{customer_name}}', '{{order_number}}', '{{status_message}}'],
                [$order['name'], $order['order_number'], $message],
                $emailBody
            );
            
            $this->mailer->msgHTML($emailBody);
            $this->mailer->send();

            // SMS notification if out for delivery
            if ($status == 'out_for_delivery') {
                $this->sms->sendDeliveryNotification($orderId);
            }

            // Admin Telegram notification
            $adminMsg = "📦 Order Status Update\n\n";
            $adminMsg .= "Order #{$order['order_number']}\n";
            $adminMsg .= "Status: " . ucfirst(str_replace('_', ' ', $status)) . "\n";
            $adminMsg .= "Customer: {$order['name']}";
            
            $this->sendTelegramMessage($this->adminChatId, $adminMsg);

            // Log notifications
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (
                    order_id, notification_type, recipient, status, sent_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$orderId, 'delivery_update_email', $order['email'], 'sent']);
            if ($status == 'out_for_delivery') {
                $stmt->execute([$orderId, 'delivery_update_sms', $order['phone'], 'sent']);
            }
            $stmt->execute([$orderId, 'delivery_update_telegram', $this->adminChatId, 'sent']);

            return true;

        } catch (Exception $e) {
            error_log("Delivery notification failed for order #$orderId: " . $e->getMessage());
            return false;
        }
    }
}
?>