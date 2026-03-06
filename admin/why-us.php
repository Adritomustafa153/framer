<?php
// admin/why-us.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/WhyUs.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$whyUs = new WhyUs($db);

// Handle delete
if (isset($_GET['delete'])) {
    if ($whyUs->delete($_GET['delete'])) {
        header("Location: why-us.php?msg=deleted");
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $item = $whyUs->getById($_GET['toggle']);
    if ($item) {
        $newStatus = $item['is_active'] ? 0 : 1;
        $whyUs->update($_GET['toggle'], ['is_active' => $newStatus]);
        header("Location: why-us.php?msg=toggled");
        exit();
    }
}

// Get all items
$items = $whyUs->getAll('sort_order ASC, created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Why Us Section</h2>
    <a href="why-us-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Item
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Item created successfully!";
        if ($_GET['msg'] == 'updated') echo "Item updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Item deleted successfully!";
        if ($_GET['msg'] == 'toggled') echo "Item status updated successfully!";
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
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items && $items->rowCount() > 0): ?>
                        <?php while ($row = $items->fetch()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><span style="font-size: 1.5rem;"><?php echo htmlspecialchars($row['icon'] ?? '⚫'); ?></span></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></td>
                                <td><span class="badge bg-dark"><?php echo $row['sort_order']; ?></span></td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="why-us-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="why-us.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </a>
                                    <a href="why-us.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this item?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No items found. <a href="why-us-edit.php">Add your first item</a></td>
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