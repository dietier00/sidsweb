<?php
session_start();
require_once('../../php/db_connect.php');
require_once('../../php/security/security_handler.php');

$security = new SecurityHandler($pdo);

// Verify admin is logged in and has appropriate permissions
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get timestamp for filtering
$timeRange = $_GET['timeRange'] ?? '24h';
$timeFilter = match($timeRange) {
    '1h' => 'INTERVAL 1 HOUR',
    '24h' => 'INTERVAL 24 HOUR',
    '7d' => 'INTERVAL 7 DAY',
    '30d' => 'INTERVAL 30 DAY',
    default => 'INTERVAL 24 HOUR'
};

// Fetch security events
$stmt = $pdo->prepare("
    SELECT sl.*, 
           c.name as customer_name,
           a.username as admin_username
    FROM security_logs sl
    LEFT JOIN customers c ON sl.user_id = c.id
    LEFT JOIN admin a ON sl.admin_id = a.id
    WHERE sl.created_at >= DATE_SUB(NOW(), {$timeFilter})
    ORDER BY sl.created_at DESC
    LIMIT 1000
");
$stmt->execute();
$securityEvents = $stmt->fetchAll();

// Fetch failed login attempts
$stmt = $pdo->prepare("
    SELECT COUNT(*) as attempts, ip_address, email, MAX(attempt_time) as last_attempt
    FROM login_attempts 
    WHERE attempt_time >= DATE_SUB(NOW(), {$timeFilter})
    AND status = 'failed'
    GROUP BY ip_address, email
    HAVING attempts >= 3
    ORDER BY attempts DESC
    LIMIT 100
");
$stmt->execute();
$suspiciousLogins = $stmt->fetchAll();

// Fetch notification stats
$stmt = $pdo->prepare("
    SELECT notification_type, 
           COUNT(*) as total_sent,
           COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
    FROM notification_logs
    WHERE sent_at >= DATE_SUB(NOW(), {$timeFilter})
    GROUP BY notification_type
");
$stmt->execute();
$notificationStats = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Monitoring - Admin Dashboard</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .security-card {
            transition: all 0.3s ease;
        }
        .security-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        .alert-danger {
            border-left: 4px solid #dc3545;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            border-left: 2px solid #e9ecef;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 24px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #0d6efd;
        }
        .stats-card {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Security Monitoring</h2>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="timeRange" onchange="updateTimeRange(this.value)">
                            <option value="1h" <?= $timeRange === '1h' ? 'selected' : '' ?>>Last Hour</option>
                            <option value="24h" <?= $timeRange === '24h' ? 'selected' : '' ?>>Last 24 Hours</option>
                            <option value="7d" <?= $timeRange === '7d' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="30d" <?= $timeRange === '30d' ? 'selected' : '' ?>>Last 30 Days</option>
                        </select>
                        <button class="btn btn-primary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stats-card security-card">
                    <div class="card-body">
                        <h5 class="card-title">Security Events</h5>
                        <h2 class="mb-0"><?= count($securityEvents) ?></h2>
                        <small>in selected time period</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card security-card">
                    <div class="card-body">
                        <h5 class="card-title">Suspicious Logins</h5>
                        <h2 class="mb-0"><?= count($suspiciousLogins) ?></h2>
                        <small>IPs with multiple failed attempts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card security-card">
                    <div class="card-body">
                        <h5 class="card-title">Failed Notifications</h5>
                        <h2 class="mb-0">
                            <?php 
                            $failedTotal = array_sum(array_column($notificationStats, 'failed_count'));
                            echo $failedTotal;
                            ?>
                        </h2>
                        <small>across all channels</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card security-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Sessions</h5>
                        <h2 class="mb-0" id="activeSessions">-</h2>
                        <small>current users online</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspicious Activities -->
        <?php if (!empty($suspiciousLogins)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card security-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Suspicious Login Attempts</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Email</th>
                                        <th>Failed Attempts</th>
                                        <th>Last Attempt</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suspiciousLogins as $login): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($login['ip_address']) ?></td>
                                        <td><?= htmlspecialchars($login['email']) ?></td>
                                        <td>
                                            <span class="badge bg-danger"><?= $login['attempts'] ?> attempts</span>
                                        </td>
                                        <td><?= date('Y-m-d H:i:s', strtotime($login['last_attempt'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="blockIP('<?= $login['ip_address'] ?>')">
                                                Block IP
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Notification Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card security-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bell"></i> Notification Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Total Sent</th>
                                        <th>Failed</th>
                                        <th>Success Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notificationStats as $stat): ?>
                                    <tr>
                                        <td><?= ucfirst(str_replace('_', ' ', $stat['notification_type'])) ?></td>
                                        <td><?= $stat['total_sent'] ?></td>
                                        <td><?= $stat['failed_count'] ?></td>
                                        <td>
                                            <?php 
                                            $successRate = ($stat['total_sent'] - $stat['failed_count']) / $stat['total_sent'] * 100;
                                            $badgeClass = $successRate >= 90 ? 'bg-success' : ($successRate >= 70 ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= number_format($successRate, 1) ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Event Timeline -->
            <div class="col-md-6">
                <div class="card security-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Security Events Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach (array_slice($securityEvents, 0, 10) as $event): ?>
                            <div class="timeline-item">
                                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($event['created_at'])) ?></small>
                                <p class="mb-0">
                                    <?php
                                    $actor = $event['user_id'] ? 
                                        "User: " . htmlspecialchars($event['customer_name']) : 
                                        ($event['admin_id'] ? 
                                            "Admin: " . htmlspecialchars($event['admin_username']) : 
                                            "System"
                                        );
                                    ?>
                                    <strong><?= ucfirst(str_replace('_', ' ', $event['action_type'])) ?></strong><br>
                                    <?= htmlspecialchars($event['action_details']) ?><br>
                                    <small class="text-muted">By <?= $actor ?> from <?= htmlspecialchars($event['ip_address']) ?></small>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script>
        function updateTimeRange(range) {
            window.location.href = 'monitor.php?timeRange=' + range;
        }

        function blockIP(ip) {
            if (confirm('Are you sure you want to block IP: ' + ip + '?')) {
                // Implement IP blocking logic
                fetch('block_ip.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ip: ip })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('IP has been blocked successfully');
                        window.location.reload();
                    } else {
                        alert('Failed to block IP: ' + data.message);
                    }
                });
            }
        }

        // Update active sessions count
        function updateActiveSessions() {
            fetch('get_active_sessions.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('activeSessions').textContent = data.count;
            });
        }

        // Update every 30 seconds
        updateActiveSessions();
        setInterval(updateActiveSessions, 30000);
    </script>
</body>
</html>