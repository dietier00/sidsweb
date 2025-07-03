<?php
require_once('../db_connect.php');

class OrderNotification {
    public function sendOrderConfirmation($orderId) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT o.*, c.phone, c.name
            FROM orders o
            JOIN customers c ON o.user_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            $message = "🛍️ Hi {$order['name']}, thank you for your order!\n\n";
            $message .= "Order #{$order['order_number']}\n";
            $message .= "Amount: ₱" . number_format($order['total_amount'], 2) . "\n\n";
            $message .= "Track your order here:\n";
            $message .= (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/shop/orders.php";
            
            // Here you can integrate any SMS or Email sending function
            // For example: $this->sendSMS($order['phone'], $message);
            // or $this->sendEmail($order['email'], 'Order Confirmation', $message);
            
            return true;
        }
        return false;
    }

    public function sendPaymentConfirmation($orderId) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT o.*, c.phone, c.name
            FROM orders o
            JOIN customers c ON o.user_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            $message = "💰 Payment Confirmed!\n\n";
            $message .= "Hi {$order['name']}, we've received your payment for order #{$order['order_number']}.\n\n";
            $message .= "Amount: ₱" . number_format($order['total_amount'], 2) . "\n";
            $message .= "We'll start processing your order shortly!\n\n";
            $message .= "Track your order here:\n";
            $message .= (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/shop/orders.php";
            
            // Here you can integrate any SMS or Email sending function
            // For example: $this->sendSMS($order['phone'], $message);
            // or $this->sendEmail($order['email'], 'Payment Confirmation', $message);
            
            return true;
        }
        return false;
    }

    public function sendDeliveryUpdate($orderId, $status) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT o.*, c.phone, c.name
            FROM orders o
            JOIN customers c ON o.user_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            $emoji = match($status) {
                'processing' => '🏭',
                'shipped' => '📦',
                'out_for_delivery' => '🚚',
                'delivered' => '✅',
                default => '📋'
            };

            $message = "{$emoji} Order Status Update\n\n";
            $message .= "Hi {$order['name']}, your order #{$order['order_number']} has been updated!\n\n";
            $message .= "Status: " . ucfirst(str_replace('_', ' ', $status)) . "\n\n";
            $message .= "Track your order here:\n";
            $message .= (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/shop/orders.php";
            
            // Here you can integrate any SMS or Email sending function
            // For example: $this->sendSMS($order['phone'], $message);
            // or $this->sendEmail($order['email'], 'Delivery Update', $message);
            
            return true;
        }
        return false;
    }
}