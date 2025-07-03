<?php
require_once('../php/db_connect.php');
class SecurityHandler {
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 1800; // 30 minutes in seconds
    private $sessionTimeout = 3600; // 1 hour in seconds
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function validateLoginAttempt($email, $userType = 'customer') {
        $ip = $this->getClientIP();
        
        // Check if IP is blocked
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE ip_address = ? 
            AND attempt_time > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND status = 'failed'
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();

        if ($result['attempts'] >= $this->maxLoginAttempts) {
            $this->logSecurityEvent('login_blocked', "Too many failed attempts from IP: $ip", null, null);
            return false;
        }

        return true;
    }

    public function logLoginAttempt($email, $status, $userId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (
                ip_address, email, status, user_agent
            ) VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $this->getClientIP(),
            $email,
            $status,
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);

        if ($status === 'success' && $userId) {
            $this->logSecurityEvent('login_success', "Successful login", $userId, null);
        }
    }

    public function validateSession() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        return true;
    }

    public function initializeSession($userId, $userType = 'customer') {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_type'] = $userType;
        $_SESSION['last_activity'] = time();
        $_SESSION['user_ip'] = $this->getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function logSecurityEvent($actionType, $details, $userId = null, $adminId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO security_logs (
                user_id, admin_id, action_type, ip_address, 
                user_agent, action_details, status
            ) VALUES (?, ?, ?, ?, ?, ?, 'logged')
        ");
        
        $stmt->execute([
            $userId,
            $adminId,
            $actionType,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $details
        ]);
    }

    public function logUserActivity($activityType, $userId = null, $requestData = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_activity_logs (
                user_id, session_id, activity_type, ip_address,
                user_agent, request_data
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            session_id(),
            $activityType,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $requestData ? json_encode($requestData) : null
        ]);
    }

    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->logSecurityEvent('csrf_attempt', "Invalid CSRF token", $_SESSION['user_id'] ?? null, null);
            return false;
        }
        return true;
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validatePaymentRequest($orderId, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM orders 
            WHERE id = ? AND user_id = ? 
            AND (payment_status = 'pending' OR payment_status = 'awaiting_payment')
        ");
        $stmt->execute([$orderId, $userId]);
        return $stmt->rowCount() > 0;
    }

    private function getClientIP() {
        $ipAddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipAddress = 'UNKNOWN';
        return $ipAddress;
    }

    private function destroySession() {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }
}