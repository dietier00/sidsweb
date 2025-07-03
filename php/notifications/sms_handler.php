<?php
require_once('../php/db_connect.php');

class SMSNotification {
    private $apiKey = 'YOUR_SEMAPHORE_API_KEY'; // Replace with your Semaphore API key
    private $sender = 'SKYEBLINDS';

    public function sendSMS($number, $message) {
        $ch = curl_init();
        $parameters = [
            'apikey' => $this->apiKey,
            'number' => $this->formatNumber($number),
            'message' => $message,
            'sendername' => $this->sender
        ];
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    private function formatNumber($number) {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Ensure number starts with 63 (Philippines)
        if (substr($number, 0, 2) != '63') {
            if (substr($number, 0, 1) == '0') {
                $number = '63' . substr($number, 1);
            } else if (strlen($number) == 10) {
                $number = '63' . $number;
            }
        }
        
        return $number;
    }

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
            $message = "Hi {$order['name']}, your order #{$order['order_number']} has been confirmed. ";
            $message .= "Total amount: ₱" . number_format($order['total_amount'], 2) . ". ";
            $message .= "Track your order at: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/shop/orders.php";
            
            return $this->sendSMS($order['phone'], $message);
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
            $message = "Hi {$order['name']}, your payment for order #{$order['order_number']} has been confirmed. ";
            $message .= "We'll start processing your order shortly. ";
            $message .= "Track your order at: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/shop/orders.php";
            
            return $this->sendSMS($order['phone'], $message);
        }
        return false;
    }

    public function sendDeliveryNotification($orderId) {
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
            $message = "Hi {$order['name']}, your order #{$order['order_number']} is out for delivery today. ";
            $message .= "Track your order at: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/shop/orders.php";
            
            return $this->sendSMS($order['phone'], $message);
        }
        return false;
    }
}
?>