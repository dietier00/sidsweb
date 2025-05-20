<?php
session_start();
require_once '../php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total messages count
$total_query = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$total_messages = $total_query->fetchColumn();
$total_pages = ceil($total_messages / $limit);

// Get messages for current page
$query = "SELECT *, 
          DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as formatted_date 
          FROM contact_messages 
          ORDER BY status = 'unread' DESC, created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle mark as read
if (isset($_POST['mark_read']) && isset($_POST['message_id'])) {
    $update = $pdo->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE id = ?");
    $update->execute([$_POST['message_id']]);
    header('Location: messages.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .message-card {
            transition: transform 0.2s ease;
            border-left: 4px solid transparent;
        }
        .message-card:hover {
            transform: translateY(-2px);
        }
        .message-card.unread {
            border-left-color: #dc3545;
            background-color: rgba(220, 53, 69, 0.05);
        }
        .message-timestamp {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .message-preview {
            max-height: 50px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark text-white">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>
            <ul class="list-unstyled components">
                <li>
                    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="orders.php"><i class="bi bi-cart"></i> Orders</a>
                </li>
                <li>
                    <a href="products.php"><i class="bi bi-box-seam"></i> Products</a>
                </li>
                <li class="active">
                    <a href="messages.php"><i class="bi bi-envelope"></i> Messages</a>
                </li>
                <li>
                    <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
                </li>
                <li>
                    <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                </li>
                <li>
                    <a href="auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2>Messages</h2>
                    </div>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Messages List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($messages)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="mt-3 mb-0">No messages found</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($messages as $message): ?>
                                        <div class="message-card card mb-3 <?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($message['subject']); ?></h5>
                                                        <p class="mb-1">
                                                            From: <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                            &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;
                                                        </p>
                                                        <p class="message-preview mb-2"><?php echo htmlspecialchars($message['message']); ?></p>
                                                        <span class="message-timestamp"><?php echo $message['formatted_date']; ?></span>
                                                    </div>
                                                    <div class="ms-3">
                                                        <?php if ($message['status'] === 'unread'): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                                <button type="submit" name="mark_read" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-envelope-open"></i> Mark as Read
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info ms-2" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#messageModal" 
                                                                data-message="<?php echo htmlspecialchars($message['message']); ?>"
                                                                data-subject="<?php echo htmlspecialchars($message['subject']); ?>"
                                                                data-name="<?php echo htmlspecialchars($message['name']); ?>"
                                                                data-email="<?php echo htmlspecialchars($message['email']); ?>"
                                                                data-date="<?php echo $message['formatted_date']; ?>">
                                                            <i class="bi bi-eye"></i> View
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Pagination -->
                                    <?php if ($total_pages > 1): ?>
                                    <nav aria-label="Messages navigation" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                            </li>
                                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message View Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Subject:</strong>
                        <p class="modal-subject mb-1"></p>
                    </div>
                    <div class="mb-3">
                        <strong>From:</strong>
                        <p class="modal-from mb-1"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Date:</strong>
                        <p class="modal-date mb-1"></p>
                    </div>
                    <div>
                        <strong>Message:</strong>
                        <p class="modal-message" style="white-space: pre-wrap;"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle message modal
        const messageModal = document.getElementById('messageModal')
        messageModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget
            const subject = button.getAttribute('data-subject')
            const message = button.getAttribute('data-message')
            const name = button.getAttribute('data-name')
            const email = button.getAttribute('data-email')
            const date = button.getAttribute('data-date')

            const modalTitle = this.querySelector('.modal-title')
            const modalSubject = this.querySelector('.modal-subject')
            const modalFrom = this.querySelector('.modal-from')
            const modalDate = this.querySelector('.modal-date')
            const modalMessage = this.querySelector('.modal-message')

            modalTitle.textContent = subject
            modalSubject.textContent = subject
            modalFrom.textContent = `${name} <${email}>`
            modalDate.textContent = date
            modalMessage.textContent = message
        })
    </script>
</body>
</html>