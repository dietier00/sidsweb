<?php
require_once('../db_connect.php');

class WebhookNotification {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function sendWebhook($event, $data) {
        // Get all active webhook endpoints for this event
        $stmt = $this->pdo->prepare("
            SELECT * FROM webhook_endpoints 
            WHERE active = 1 
            AND (event_type = ? OR event_type = 'all')
        ");
        $stmt->execute([$event]);
        $endpoints = $stmt->fetchAll();

        $results = [];
        foreach ($endpoints as $endpoint) {
            try {
                $payload = [
                    'event' => $event,
                    'timestamp' => time(),
                    'data' => $data,
                    'signature' => $this->generateSignature($data, $endpoint['secret_key'])
                ];

                $ch = curl_init($endpoint['url']);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-Webhook-Signature: ' . $payload['signature']
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Log webhook attempt
                $this->logWebhook($endpoint['id'], $event, $httpCode, $response);

                $results[] = [
                    'endpoint_id' => $endpoint['id'],
                    'success' => $httpCode >= 200 && $httpCode < 300,
                    'http_code' => $httpCode,
                    'response' => $response
                ];

            } catch (Exception $e) {
                $this->logWebhook($endpoint['id'], $event, 500, $e->getMessage());
                $results[] = [
                    'endpoint_id' => $endpoint['id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    private function generateSignature($data, $secret) {
        return hash_hmac('sha256', json_encode($data), $secret);
    }

    private function logWebhook($endpointId, $event, $statusCode, $response) {
        $stmt = $this->pdo->prepare("
            INSERT INTO webhook_logs (
                endpoint_id, event_type, status_code, 
                response, created_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$endpointId, $event, $statusCode, $response]);
    }

    public function sendOrderNotification($orderId, $event) {
        // Get order details
        $stmt = $this->pdo->prepare("
            SELECT o.*, c.email, c.phone, c.name,
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
        $order = $stmt->fetch();

        if (!$order) return false;

        // Format order data for webhook
        $webhookData = [
            'order_id' => $order['id'],
            'order_number' => $order['order_number'],
            'customer' => [
                'name' => $order['name'],
                'email' => $order['email'],
                'phone' => $order['phone']
            ],
            'total_amount' => $order['total_amount'],
            'payment_method' => $order['payment_method'],
            'payment_status' => $order['payment_status'],
            'order_status' => $order['status'],
            'items' => json_decode('[' . $order['items'] . ']', true),
            'created_at' => $order['created_at']
        ];

        return $this->sendWebhook($event, $webhookData);
    }
}