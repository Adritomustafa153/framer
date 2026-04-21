<?php
// admin/user-delete.php
ob_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/ActivityLog.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$activityLog = new ActivityLog($db);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$confirmed = isset($_GET['confirm']) ? $_GET['confirm'] : false;

// Prevent deleting your own account
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account!";
    header("Location: users.php");
    exit();
}

// Get user details for logging
$user = $userModel->getById($id);

if (!$user) {
    $_SESSION['error'] = "User not found!";
    header("Location: users.php");
    exit();
}

// If not confirmed, show confirmation page
if (!$confirmed) {
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Delete User</h2>
        <a href="users.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Confirm Deletion</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 48px; color: #dc3545;"></i>
                    </div>
                    <h4>Are you sure you want to delete this user?</h4>
                    <p class="text-muted">This action cannot be undone.</p>
                    
                    <div class="alert alert-warning text-start">
                        <strong>User Details:</strong><br>
                        <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
                        <strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?><br>
                        <strong>Role:</strong> <?php echo ucfirst($user['role']); ?><br>
                        <strong>Status:</strong> <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <a href="user-delete.php?id=<?php echo $id; ?>&confirm=yes" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Yes, Delete User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once 'footer.php';
    exit();
}

// Process deletion
if ($confirmed == 'yes') {
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Check if user has any projects assigned
        $checkProjects = "SELECT COUNT(*) as count FROM project_team WHERE user_id = ?";
        $stmt = $db->prepare($checkProjects);
        $stmt->execute([$id]);
        $projectsCount = $stmt->fetch()['count'];
        
        if ($projectsCount > 0) {
            // Option 1: Reassign projects or prevent deletion
            $_SESSION['error'] = "Cannot delete user because they are assigned to $projectsCount project(s). Please reassign these projects first.";
            header("Location: users.php");
            exit();
        }
        
        // Check if user has created any projects
        $checkCreatedProjects = "SELECT COUNT(*) as count FROM projects WHERE created_by = ?";
        $stmt = $db->prepare($checkCreatedProjects);
        $stmt->execute([$id]);
        $createdCount = $stmt->fetch()['count'];
        
        if ($createdCount > 0) {
            // Reassign projects to admin
            $adminId = 1; // Default admin ID
            $updateProjects = "UPDATE projects SET created_by = ? WHERE created_by = ?";
            $stmt = $db->prepare($updateProjects);
            $stmt->execute([$adminId, $id]);
            
            $activityLog->log(
                $_SESSION['user_id'], 
                'reassign', 
                'projects', 
                null, 
                null, 
                ['user_id' => $id, 'reassigned_to' => $adminId], 
                "Reassigned $createdCount projects from user: " . $user['username']
            );
        }
        
        // Log the deletion before deleting
        $activityLog->log(
            $_SESSION['user_id'], 
            'delete', 
            'users', 
            $id, 
            $user, 
            null, 
            'Deleted user: ' . $user['username']
        );
        
        // Delete the user
        $result = $userModel->delete($id);
        
        if ($result) {
            $db->commit();
            $_SESSION['success'] = "User '{$user['username']}' has been deleted successfully!";
        } else {
            throw new Exception("Failed to delete user");
        }
        
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
    }
    
    header("Location: users.php");
    exit();
}
?>