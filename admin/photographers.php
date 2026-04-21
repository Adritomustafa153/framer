<?php
// admin/photographers.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/Photographer.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$photographer = new Photographer($db);

// Handle delete
if (isset($_GET['delete'])) {
    if ($photographer->delete($_GET['delete'])) {
        header("Location: photographers.php?msg=deleted");
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $item = $photographer->getById($_GET['toggle']);
    if ($item) {
        $newStatus = $item['is_active'] ? 0 : 1;
        $photographer->update($_GET['toggle'], ['is_active' => $newStatus]);
        header("Location: photographers.php?msg=toggled");
        exit();
    }
}

// Get all photographers
$photographers = $photographer->getAll('sort_order ASC, name ASC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Photographers</h2>
    <a href="photographer-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Photographer
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Photographer created successfully!";
        if ($_GET['msg'] == 'updated') echo "Photographer updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Photographer deleted successfully!";
        if ($_GET['msg'] == 'toggled') echo "Photographer status updated successfully!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Bio</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($photographers && $photographers->rowCount() > 0): ?>
                        <?php while ($row = $photographers->fetch()): 
                            $photoUrl = $row['photo'] ?: 'https://via.placeholder.com/50x50?text=No+Photo';
                            if (strpos($photoUrl, 'uploads/') === 0) {
                                $photoUrl = '../' . $photoUrl;
                            }
                        ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['bio'] ?? '', 0, 50)) . '...'; ?></td>
                                <td><span class="badge bg-dark"><?php echo $row['sort_order']; ?></span></td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="photographer-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="photographers.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </a>
                                    <a href="photographers.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this photographer? Any images assigned to them will be unassigned.')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No photographers found. <a href="photographer-edit.php">Add your first photographer</a></td>
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