<?php
// admin/faq.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/FAQ.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$faq = new FAQ($db);

// Handle delete
if (isset($_GET['delete'])) {
    if ($faq->delete($_GET['delete'])) {
        header("Location: faq.php?msg=deleted");
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $item = $faq->getById($_GET['toggle']);
    if ($item) {
        $newStatus = $item['is_active'] ? 0 : 1;
        $faq->update($_GET['toggle'], ['is_active' => $newStatus]);
        header("Location: faq.php?msg=toggled");
        exit();
    }
}

// Get all items
$items = $faq->getAll('sort_order ASC, created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage FAQs</h2>
    <a href="faq-edit.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New FAQ
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "FAQ created successfully!";
        if ($_GET['msg'] == 'updated') echo "FAQ updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "FAQ deleted successfully!";
        if ($_GET['msg'] == 'toggled') echo "FAQ status updated successfully!";
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
                        <th>Question</th>
                        <th>Category</th>
                        <th>Answer Preview</th>
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
                                <td><?php echo htmlspecialchars($row['question']); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($row['category'] ?: 'General'); ?></span></td>
                                <td><?php echo htmlspecialchars(substr($row['answer'], 0, 50)) . '...'; ?></td>
                                <td><span class="badge bg-dark"><?php echo $row['sort_order']; ?></span></td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="faq-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="faq.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </a>
                                    <a href="faq.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this FAQ?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No FAQs found. <a href="faq-edit.php">Add your first FAQ</a></td>
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