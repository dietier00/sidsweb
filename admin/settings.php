<?php
session_start();
require_once '../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['admin_role'], ['owner', 'admin'])) {
    header("Location: index.php");
    exit();
}

// Fetch all admin users
$sql = "SELECT * FROM admins ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch site settings
$settings_sql = "SELECT * FROM site_settings";
$settings_stmt = $pdo->prepare($settings_sql);
$settings_stmt->execute();
$settings = [];
while ($row = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
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
                    <a href="products.php"><i class="bi bi-box-seam"></i> Products</a>
                </li>
                <li>
                    <a href="messages.php"><i class="bi bi-envelope"></i> Messages</a>
                </li>
                <li class="active">
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
                <h2 class="mb-4">Settings</h2>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Site Settings -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Site Settings</h5>
                            </div>
                            <div class="card-body">
                                <form action="settings/update_site_settings.php" method="POST">
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="site_email" class="form-label">Site Email</label>
                                        <input type="email" class="form-control" id="site_email" name="site_email" 
                                               value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                                   name="maintenance_mode" value="1" 
                                                   <?php echo ($settings['maintenance_mode'] ?? '') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </form>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Security Settings</h5>
                            </div>
                            <div class="card-body">
                                <form action="settings/update_security_settings.php" method="POST">
                                    <div class="mb-3">
                                        <label for="login_attempts" class="form-label">Max Login Attempts</label>
                                        <input type="number" class="form-control" id="login_attempts" name="login_attempts" 
                                               value="<?php echo htmlspecialchars($settings['login_attempts'] ?? '3'); ?>" 
                                               min="1" max="10" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                        <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                               value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '30'); ?>" 
                                               min="5" max="120" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password_expiry" class="form-label">Password Expiry (days)</label>
                                        <input type="number" class="form-control" id="password_expiry" name="password_expiry" 
                                               value="<?php echo htmlspecialchars($settings['password_expiry'] ?? '90'); ?>" 
                                               min="30" max="180" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Security Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Users -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Admin Users</h5>
                                    <?php if ($_SESSION['admin_role'] === 'owner'): ?>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteAdminModal">
                                        <i class="bi bi-person-plus"></i> Invite Admin
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Created</th>
                                                <?php if ($_SESSION['admin_role'] === 'owner'): ?>
                                                <th>Actions</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($admins as $admin): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($admin['name']); ?></td>
                                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $admin['role'] === 'owner' ? 'danger' : 
                                                            ($admin['role'] === 'admin' ? 'primary' : 'success'); 
                                                    ?>">
                                                        <?php echo ucfirst(htmlspecialchars($admin['role'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                                <?php if ($_SESSION['admin_role'] === 'owner' && $admin['role'] !== 'owner'): ?>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-admin" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editAdminModal"
                                                            data-id="<?php echo $admin['id']; ?>"
                                                            data-role="<?php echo htmlspecialchars($admin['role']); ?>"
                                                            data-name="<?php echo htmlspecialchars($admin['name']); ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-admin"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteAdminModal"
                                                            data-id="<?php echo $admin['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($admin['name']); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invite Admin Modal -->
    <div class="modal fade" id="inviteAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invite Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings/invite_admin.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="invite_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="invite_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="invite_role" class="form-label">Role</label>
                            <select class="form-select" id="invite_role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="agent">Agent</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Invite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings/update_admin.php" method="POST">
                    <input type="hidden" name="admin_id" id="edit_admin_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="agent">Agent</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Admin Modal -->
    <div class="modal fade" id="deleteAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings/delete_admin.php" method="POST">
                    <input type="hidden" name="admin_id" id="delete_admin_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong><span id="delete_admin_name"></span></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle Edit Admin Modal
        document.querySelectorAll('.edit-admin').forEach(button => {
            button.addEventListener('click', function() {
                const data = this.dataset;
                document.getElementById('edit_admin_id').value = data.id;
                document.getElementById('edit_role').value = data.role;
            });
        });

        // Handle Delete Admin Modal
        document.querySelectorAll('.delete-admin').forEach(button => {
            button.addEventListener('click', function() {
                const data = this.dataset;
                document.getElementById('delete_admin_id').value = data.id;
                document.getElementById('delete_admin_name').textContent = data.name;
            });
        });
    </script>
</body>
</html>