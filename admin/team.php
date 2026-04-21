<?php
// admin/team.php
ob_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

// Get all users except admin (or all users)
$users = $userModel->getAll('role ASC, username ASC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Team Members</h2>
    <a href="user-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add Team Member
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->rowCount() > 0): ?>
                        <?php while ($user = $users->fetch()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active'] ?? 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No team members found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>