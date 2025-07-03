<?php
require_once('../db_connect.php');

class NotificationManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function notifyOrderCreated($orderId) {
        try {
            $this->pdo->beginTransaction();

            // Get order details
            $orderDetails = $this->getOrderDetails($orderId);
            if (!$orderDetails) {
                throw new Exception('Order not found');
            }

            // Send Facebook message notification
            if ($orderDetails['facebook_id']) {
                $fbResult = $this->sendFacebookMessage($orderDetails, 'order_created');
                $this->logNotification($orderId, 'facebook', 'order_created', $fbResult);
            }

            // Send Viber notification
            if ($orderDetails['viber_id']) {
                $viberResult = $this->sendViberMessage($orderDetails, 'order_created');
                $this->logNotification($orderId, 'viber', 'order_created', $viberResult);
            }

            // Send SMS notification
            if ($orderDetails['phone']) {
                $smsResult = $this->sendSMS($orderDetails, 'order_created');
                $this->logNotification($orderId, 'sms', 'order_created', $smsResult);
            }

            // Send email notification
            $emailResult = $this->sendOrderEmail($orderDetails, 'order_confirmation');
            $this->logNotification($orderId, 'email', 'order_created', $emailResult);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->logError('order_created', $orderId, $e->getMessage());
            return false;
        }
    }

    public function notifyPaymentConfirmed($orderId) {
        try {
            $this->pdo->beginTransaction();

            $orderDetails = $this->getOrderDetails($orderId);
            if (!$orderDetails) {
                throw new Exception('Order not found');
            }

            // Send Facebook message notification
            if ($orderDetails['facebook_id']) {
                $fbResult = $this->sendFacebookMessage($orderDetails, 'payment_confirmed');
                $this->logNotification($orderId, 'facebook', 'payment_confirmed', $fbResult);
            }

            // Send Viber notification
            if ($orderDetails['viber_id']) {
                $viberResult = $this->sendViberMessage($orderDetails, 'payment_confirmed');
                $this->logNotification($orderId, 'viber', 'payment_confirmed', $viberResult);
            }

            // Send SMS notification
            if ($orderDetails['phone']) {
                $smsResult = $this->sendSMS($orderDetails, 'payment_confirmed');
                $this->logNotification($orderId, 'sms', 'payment_confirmed', $smsResult);
            }

            // Send email notification
            $emailResult = $this->sendOrderEmail($orderDetails, 'payment_confirmation');
            $this->logNotification($orderId, 'email', 'payment_confirmed', $emailResult);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->logError('payment_confirmed', $orderId, $e->getMessage());
            return false;
        }
    }

    public function notifyOrderStatusUpdate($orderId, $status) {
        try {
            $this->pdo->beginTransaction();

            $orderDetails = $this->getOrderDetails($orderId);
            if (!$orderDetails) {
                throw new Exception('Order not found');
            }

            // Send Facebook message notification
            if ($orderDetails['facebook_id']) {
                $fbResult = $this->sendFacebookMessage($orderDetails, 'status_update', ['status' => $status]);
                $this->logNotification($orderId, 'facebook', 'status_update', $fbResult);
            }

            // Send Viber notification
            if ($orderDetails['viber_id']) {
                $viberResult = $this->sendViberMessage($orderDetails, 'status_update', ['status' => $status]);
                $this->logNotification($orderId, 'viber', 'status_update', $viberResult);
            }

            // Send SMS notification
            if ($orderDetails['phone']) {
                $smsResult = $this->sendSMS($orderDetails, 'status_update', ['status' => $status]);
                $this->logNotification($orderId, 'sms', 'status_update', $smsResult);
            }

            // Send email notification
            $emailResult = $this->sendOrderEmail($orderDetails, 'delivery_update', ['status' => $status]);
            $this->logNotification($orderId, 'email', 'status_update', $emailResult);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->logError('status_update', $orderId, $e->getMessage());
            return false;
        }
    }

    private function getOrderDetails($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, c.email, c.name, c.phone, c.facebook_id, c.viber_id,
                   GROUP_CONCAT(
                       JSON_OBJECT(
                           'product_id', p.id,
                           'name', p.name,
                           'quantity', oi.quantity,
                           'price', oi.price
                       )
                   ) as items
            FROM orders o
            JOIN customers c ON o.user_id = c.id
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            WHERE o.id = ?
            GROUP BY o.id
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    private function sendFacebookMessage($orderDetails, $type, $additionalData = []) {
        if (!$orderDetails['facebook_id']) return false;

        $messages = [
            'order_created' => [
                'text' => "🛍️ Hi {$orderDetails['name']}, thank you for your order!\n\n" .
                         "Order #{$orderDetails['order_number']}\n" .
                         "Amount: ₱" . number_format($orderDetails['total_amount'], 2) . "\n\n" .
                         "Track your order here: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
            ],
            'payment_confirmed' => [
                'text' => "💰 Payment Confirmed!\n\n" .
                         "Hi {$orderDetails['name']}, we've received your payment for order #{$orderDetails['order_number']}.\n\n" .
                         "Amount: ₱" . number_format($orderDetails['total_amount'], 2) . "\n" .
                         "We'll start processing your order shortly!\n\n" .
                         "Track your order here: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
            ],
            'status_update' => [
                'text' => $this->getStatusEmoji($additionalData['status']) . " Order Status Update\n\n" .
                         "Hi {$orderDetails['name']}, your order #{$orderDetails['order_number']} has been updated!\n\n" .
                         "Status: " . ucfirst(str_replace('_', ' ', $additionalData['status'])) . "\n\n" .
                         "Track your order here: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
            ]
        ];

        // Facebook Message API integration would go here
        // This is a placeholder for the actual Facebook integration
        return true;
    }

    private function sendViberMessage($orderDetails, $type, $additionalData = []) {
        if (!$orderDetails['viber_id']) return false;

        $messages = [
            'order_created' => [
                'text' => "🛍️ Hi {$orderDetails['name']}, thank you for your order!\n\n" .
                         "Order #{$orderDetails['order_number']}\n" .
                         "Amount: ₱" . number_format($orderDetails['total_amount'], 2) . "\n\n" .
                         "Track your order here: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
            ],
            'payment_confirmed' => [
                'text' => "💰 Payment Confirmed!\n\n" .
                         "Hi {$orderDetails['name']}, we've received your payment for order #{$orderDetails['order_number']}.\n\n" .
                         "Amount: ₱" . number_format($orderDetails['total_amount'], 2) . "\n" .
                         "We'll start processing your order shortly!\n\n" .
                         "Track your order here: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
            ],
            'status_update' => [
                'text' => $this->getStatusEmoji($additionalData['status']) . " Order Status Update\n\n" .
                         "Hi {$orderDetails['name']}, your order #{$orderDetails['order_number']} has been updated!\n\n" .
                         "Status: " . ucfirst(str_replace('_', ' ', $additionalData['status'])) . "\n\n" .
                         "Track your order here: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
            ]
        ];

        // Viber API integration would go here
        // This is a placeholder for the actual Viber integration
        return true;
    }

    private function sendSMS($orderDetails, $type, $additionalData = []) {
        if (!$orderDetails['phone']) return false;

        $messages = [
            'order_created' => 
                "Order Confirmation: #{$orderDetails['order_number']}\n" .
                "Amount: P" . number_format($orderDetails['total_amount'], 2) . "\n" .
                "Track: " . $this->getOrderTrackingUrl($orderDetails['order_number']),
            'payment_confirmed' => 
                "Payment Received: #{$orderDetails['order_number']}\n" .
                "Amount: P" . number_format($orderDetails['total_amount'], 2) . "\n" .
                "Track: " . $this->getOrderTrackingUrl($orderDetails['order_number']),
            'status_update' => 
                "Order #{$orderDetails['order_number']} Update:\n" .
                "Status: " . ucfirst(str_replace('_', ' ', $additionalData['status'])) . "\n" .
                "Track: " . $this->getOrderTrackingUrl($orderDetails['order_number'])
        ];

        // SMS Gateway integration would go here
        // This is a placeholder for the actual SMS integration
        return true;
    }

    private function sendOrderEmail($orderDetails, $template, $additionalData = []) {
        if (!$orderDetails['email']) return false;

        // Load email template
        $templatePath = __DIR__ . "/../../templates/email/{$template}.html";
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found: {$template}");
        }

        $templateContent = file_get_contents($templatePath);

        // Replace placeholders
        $replacements = [
            '{{customer_name}}' => $orderDetails['name'],
            '{{order_number}}' => $orderDetails['order_number'],
            '{{total_amount}}' => number_format($orderDetails['total_amount'], 2),
            '{{payment_date}}' => date('F j, Y'),
            '{{status_message}}' => $additionalData['status'] ?? '',
            '{{tracking_url}}' => $this->getOrderTrackingUrl($orderDetails['order_number'])
        ];

        $emailContent = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $templateContent
        );

        // Send email using PHPMailer
        require_once(__DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php');
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Email configuration
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'Skye Blinds');
            $mail->addAddress($orderDetails['email'], $orderDetails['name']);
            $mail->isHTML(true);
            
            // Set subject based on template
            $subjects = [
                'order_confirmation' => 'Order Confirmation - Skye Blinds',
                'payment_confirmation' => 'Payment Received - Skye Blinds',
                'delivery_update' => 'Order Status Update - Skye Blinds'
            ];
            $mail->Subject = $subjects[$template] ?? 'Skye Blinds Notification';
            
            $mail->Body = $emailContent;
            $mail->send();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to send email: ' . $mail->ErrorInfo);
        }
    }

    private function getStatusEmoji($status) {
        return match($status) {
            'processing' => '🏭',
            'shipped' => '📦',
            'out_for_delivery' => '🚚',
            'delivered' => '✅',
            default => '📋'
        };
    }

    private function getOrderTrackingUrl($orderNumber) {
        return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
               $_SERVER['HTTP_HOST'] . 
               "/shop/orders.php?order=" . urlencode($orderNumber);
    }

    private function logNotification($orderId, $channel, $type, $result) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notification_logs (
                order_id, channel, notification_type,
                status, response_data, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $status = is_array($result) && isset($result['success']) ? 
            ($result['success'] ? 'success' : 'failed') : 
            (($result === true) ? 'success' : 'failed');

        $stmt->execute([
            $orderId,
            $channel,
            $type,
            $status,
            json_encode($result)
        ]);
    }

    private function logError($type, $orderId, $error) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notification_errors (
                notification_type, order_id, error_message,
                created_at
            ) VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$type, $orderId, $error]);
    }
}