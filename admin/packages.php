<?php
// admin/packages.php
require_once '../config/database.php';
require_once '../models/Package.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$package = new Package($db);

// Handle delete
if (isset($_GET['delete'])) {
    if ($package->delete($_GET['delete'])) {
        header("Location: packages.php?msg=deleted");
        exit();
    }
}

// Get all packages
$packages = $package->getAll('sort_order ASC, created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Packages</h2>
    <a href="package-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Package
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Package created successfully!";
        if ($_GET['msg'] == 'updated') echo "Package updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Package deleted successfully!";
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
                        <th>Package Name</th>
                        <th>Code</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($packages->rowCount() > 0): ?>
                        <?php while ($row = $packages->fetch()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['package_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['package_code']); ?></td>
                                <td><?php echo $row['currency']; ?> <?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['duration']); ?></td>
                                <td>
                                    <?php if ($row['is_featured']): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['sort_order']; ?></td>
                                <td>
                                    <a href="package-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="packages.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this package?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No packages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>