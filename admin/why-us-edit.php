<?php
// admin/why-us-edit.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/WhyUs.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$whyUs = new WhyUs($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'icon' => $_POST['icon'],
        'sort_order' => $_POST['sort_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    if ($isEdit) {
        if ($whyUs->update($id, $data)) {
            header("Location: why-us.php?msg=updated");
            exit();
        }
    } else {
        if ($whyUs->create($data)) {
            header("Location: why-us.php?msg=created");
            exit();
        }
    }
}

// Get data if editing
$itemData = [];
if ($isEdit) {
    $itemData = $whyUs->getById($id);
    if (!$itemData) {
        header("Location: why-us.php");
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit Why Us Item' : 'Add New Why Us Item'; ?></h2>
    <a href="why-us.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required 
                               value="<?php echo htmlspecialchars($itemData['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($itemData['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Settings</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control" 
                                       value="<?php echo htmlspecialchars($itemData['icon'] ?? ''); ?>"
                                       placeholder="e.g., ⚫, bi-star, 🔲">
                                <small class="text-muted">You can use emoji or Bootstrap icon class</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" 
                                       value="<?php echo htmlspecialchars($itemData['sort_order'] ?? 0); ?>" min="0">
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                           <?php echo ($itemData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Item
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once 'footer.php';
ob_end_flush();
?>