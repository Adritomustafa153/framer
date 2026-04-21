<?php
// admin/addon-edit.php
ob_start();
require_once '../config/database.php';
require_once '../models/AddonService.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$addonModel = new AddonService($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'service_name' => $_POST['service_name'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'price_type' => $_POST['price_type'],
        'sort_order' => $_POST['sort_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    if ($isEdit) {
        if ($addonModel->update($id, $data)) {
            header("Location: addons.php?msg=updated");
            exit();
        }
    } else {
        if ($addonModel->create($data)) {
            header("Location: addons.php?msg=created");
            exit();
        }
    }
}

// Get data if editing
$itemData = [];
if ($isEdit) {
    $itemData = $addonModel->getById($id);
    if (!$itemData) {
        header("Location: addons.php");
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit Add-on Service' : 'Add New Add-on Service'; ?></h2>
    <a href="addons.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Add-ons
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" name="service_name" class="form-control" required 
                               value="<?php echo htmlspecialchars($itemData['service_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($itemData['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (BDT) *</label>
                            <input type="number" step="0.01" name="price" class="form-control" required 
                                   value="<?php echo htmlspecialchars($itemData['price'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price Type</label>
                            <select name="price_type" class="form-control">
                                <option value="fixed" <?php echo ($itemData['price_type'] ?? 'fixed') == 'fixed' ? 'selected' : ''; ?>>Fixed Price</option>
                                <option value="per_hour" <?php echo ($itemData['price_type'] ?? '') == 'per_hour' ? 'selected' : ''; ?>>Per Hour</option>
                                <option value="per_unit" <?php echo ($itemData['price_type'] ?? '') == 'per_unit' ? 'selected' : ''; ?>>Per Unit</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Settings</h6>
                            
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
                    <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Service
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once 'footer.php';
ob_end_flush();
?>