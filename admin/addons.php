<?php
// admin/addons.php
ob_start();
require_once '../config/database.php';
require_once '../models/AddonService.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$addonModel = new AddonService($db);

// Handle delete
if (isset($_GET['delete'])) {
    if ($addonModel->delete($_GET['delete'])) {
        header("Location: addons.php?msg=deleted");
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $item = $addonModel->getById($_GET['toggle']);
    if ($item) {
        $newStatus = $item['is_active'] ? 0 : 1;
        $addonModel->update($_GET['toggle'], ['is_active' => $newStatus]);
        header("Location: addons.php?msg=toggled");
        exit();
    }
}

// Get all addons
$addons = $addonModel->getAll('sort_order ASC, service_name ASC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Add-on Services</h2>
    <a href="addon-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Service
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Add-on service created successfully!";
        if ($_GET['msg'] == 'updated') echo "Add-on service updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Add-on service deleted successfully!";
        if ($_GET['msg'] == 'toggled') echo "Add-on service status updated successfully!";
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
                        <th>Service Name</th>
                        <th>Description</th>
                        <th>Price (BDT)</th>
                        <th>Price Type</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($addons && $addons->rowCount() > 0): ?>
                        <?php while ($row = $addons->fetch()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['description'] ?? '', 0, 50)) . '...'; ?></td>
                                <td>৳ <?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['price_type'])); ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-dark"><?php echo $row['sort_order']; ?></span></td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="addon-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="addons.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </a>
                                    <a href="addons.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this add-on service?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No add-on services found. <a href="addon-edit.php">Add your first service</a></td>
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