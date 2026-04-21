<?php
// admin/users.php
ob_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

// Get all users - this returns an array, not a statement object
$users = $userModel->getAll();

// Get user statistics
$stats = $userModel->getStats();

// Display success/error messages
if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>User Management</h2>
    <a href="user-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New User
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Active Users</h5>
                <h2 class="mb-0"><?php echo $stats['active'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Inactive Users</h5>
                <h2 class="mb-0"><?php echo $stats['inactive'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Admins</h5>
                <h2 class="mb-0"><?php echo $stats['admins'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">All Users</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                                <td>
                                    <?php
                                    $roleColors = [
                                        'admin' => 'danger',
                                        'manager' => 'warning',
                                        'photographer' => 'info',
                                        'editor' => 'primary',
                                        'accounts' => 'success'
                                    ];
                                    $color = $roleColors[$user['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($user['role']); ?></span>
                                </td>
                                <td>৳ <?php echo number_format($user['salary'] ?? 0, 2); ?></td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="user-delete.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete your own account">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();