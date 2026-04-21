<?php
// admin/user-edit.php
ob_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/ActivityLog.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$activityLog = new ActivityLog($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

$error = '';

// Get user data if editing
$userData = [];
if ($isEdit) {
    $userData = $userModel->getById($id);
    if (!$userData) {
        header("Location: users.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'full_name' => trim($_POST['full_name']),
        'role' => $_POST['role'],
        'salary' => $_POST['salary'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Validate
    if (empty($data['username'])) {
        $error = "Username is required";
    } elseif (empty($data['email'])) {
        $error = "Email is required";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            if ($isEdit) {
                // Check if username/email already exists for other users
                $checkQuery = "SELECT COUNT(*) as count FROM users WHERE (username = ? OR email = ?) AND id != ?";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([$data['username'], $data['email'], $id]);
                $exists = $checkStmt->fetch()['count'];
                
                if ($exists > 0) {
                    $error = "Username or email already exists";
                } else {
                    if ($userModel->update($id, $data)) {
                        // Update password if provided
                        if (!empty($_POST['password'])) {
                            $userModel->updatePassword($id, $_POST['password']);
                        }
                        $activityLog->log($_SESSION['user_id'], 'update', 'users', $id, null, $data, 'Updated user: ' . $data['username']);
                        header("Location: users.php?msg=updated");
                        exit();
                    }
                }
            } else {
                // Check if username/email already exists
                $checkQuery = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([$data['username'], $data['email']]);
                $exists = $checkStmt->fetch()['count'];
                
                if ($exists > 0) {
                    $error = "Username or email already exists";
                } elseif (empty($_POST['password'])) {
                    $error = "Password is required for new users";
                } else {
                    $data['password'] = $_POST['password'];
                    if ($userModel->create($data)) {
                        $newUserId = $db->lastInsertId();
                        $activityLog->log($_SESSION['user_id'], 'create', 'users', $newUserId, null, $data, 'Created user: ' . $data['username']);
                        header("Location: users.php?msg=created");
                        exit();
                    }
                }
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit User' : 'Add New User'; ?></h2>
    <a href="users.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required 
                                   value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary (Monthly)</label>
                            <input type="number" step="0.01" name="salary" class="form-control" 
                                   value="<?php echo htmlspecialchars($userData['salary'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo $isEdit ? 'New Password' : 'Password *'; ?></label>
                            <input type="password" name="password" class="form-control" <?php echo !$isEdit ? 'required' : ''; ?>>
                            <?php if ($isEdit): ?>
                                <small class="text-muted">Leave blank to keep current password</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role *</label>
                            <select name="role" class="form-control" required>
                                <option value="admin" <?php echo ($userData['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="manager" <?php echo ($userData['role'] ?? '') == 'manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="photographer" <?php echo ($userData['role'] ?? '') == 'photographer' ? 'selected' : ''; ?>>Photographer</option>
                                <option value="editor" <?php echo ($userData['role'] ?? '') == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="accounts" <?php echo ($userData['role'] ?? '') == 'accounts' ? 'selected' : ''; ?>>Accounts</option>
                                <option value="Cinematographer" <?php echo ($userData['role'] ?? '') == 'Cinematographer' ? 'selected' : ''; ?>>Cinematographer</option>
                                <option value="customerservice" <?php echo ($userData['role'] ?? '') == 'customerservice' ? 'selected' : ''; ?>>Customer service</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                   <?php echo ($userData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> User
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Role Permissions</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Admin</h6>
                    <small class="text-muted">Full access to all features</small>
                </div>
                <div class="mb-3">
                    <h6>Manager</h6>
                    <small class="text-muted">Manage projects, packages, add-ons, blog, gallery</small>
                </div>
                <div class="mb-3">
                    <h6>Photographer</h6>
                    <small class="text-muted">View-only access to projects, packages, gallery</small>
                </div>
                <div class="mb-3">
                    <h6>Editor</h6>
                    <small class="text-muted">Edit projects, blog posts, gallery uploads</small>
                </div>
                <div class="mb-3">
                    <h6>Accounts</h6>
                    <small class="text-muted">Manage orders, payments, and reports</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>