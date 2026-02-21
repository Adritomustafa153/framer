<?php
// admin/package-edit.php

// Start output buffering to prevent header errors
ob_start();

require_once '../config/database.php';
require_once '../models/Package.php';

// Handle form submission BEFORE including header (to allow redirects)
$database = new Database();
$db = $database->getConnection();
$package = new Package($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $data = [
            'package_name' => $_POST['package_name'],
            'package_code' => $_POST['package_code'],
            'price' => $_POST['price'],
            'currency' => $_POST['currency'],
            'duration' => $_POST['duration'],
            'description' => $_POST['description'],
            'features' => explode("\n", trim($_POST['features'])),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => $_POST['sort_order'] ?? 0
        ];
        
        // Clean up features (remove empty lines and trim)
        $data['features'] = array_filter(array_map('trim', $data['features']));
        
        if ($isEdit) {
            if ($package->update($id, $data)) {
                header("Location: packages.php?msg=updated");
                exit();
            }
        } else {
            if ($package->create($data)) {
                header("Location: packages.php?msg=created");
                exit();
            }
        }
    } catch (PDOException $e) {
        // Check for duplicate entry error
        if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'package_code') !== false) {
            $error = "Package code '" . htmlspecialchars($_POST['package_code']) . "' already exists. Please use a different code.";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get package data if editing (after form submission check)
$packageData = [];
if ($isEdit) {
    $packageData = $package->getById($id);
    if (!$packageData) {
        header("Location: packages.php");
        exit();
    }
}

// Now include header (after all potential redirects)
require_once 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit Package' : 'Add New Package'; ?></h2>
    <a href="packages.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Packages
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" onsubmit="return validateForm()">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Package Name *</label>
                    <input type="text" name="package_name" id="package_name" class="form-control" required 
                           value="<?php echo htmlspecialchars($packageData['package_name'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Package Code *</label>
                    <input type="text" name="package_code" id="package_code" class="form-control" required 
                           value="<?php echo htmlspecialchars($packageData['package_code'] ?? ($_POST['package_code'] ?? '')); ?>"
                           placeholder="e.g., ESS-001, WED-002">
                    <small class="text-muted">Must be unique. Use format like ESS-001, PRE-002, etc.</small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Price *</label>
                    <input type="number" step="0.01" name="price" class="form-control" required 
                           value="<?php echo htmlspecialchars($packageData['price'] ?? ''); ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-control">
                        <option value="USD" <?php echo ($packageData['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                        <option value="BDT" <?php echo ($packageData['currency'] ?? '') == 'BDT' ? 'selected' : ''; ?>>BDT (৳)</option>
                        <option value="EUR" <?php echo ($packageData['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" class="form-control" 
                           value="<?php echo htmlspecialchars($packageData['duration'] ?? ''); ?>"
                           placeholder="e.g., 2 hours, Full day">
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($packageData['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Features (one per line)</label>
                    <textarea name="features" class="form-control" rows="5" placeholder="Enter each feature on a new line"><?php 
                        if (isset($packageData['features'])) {
                            $features = is_string($packageData['features']) ? json_decode($packageData['features'], true) : $packageData['features'];
                            if (is_array($features)) {
                                echo htmlspecialchars(implode("\n", $features));
                            }
                        }
                    ?></textarea>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                               <?php echo ($packageData['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Package</label>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                               <?php echo ($packageData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" 
                           value="<?php echo htmlspecialchars($packageData['sort_order'] ?? 0); ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Package
                    </button>
                    <a href="packages.php" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateForm() {
    var packageCode = document.getElementById('package_code').value;
    var packageName = document.getElementById('package_name').value;
    
    if (!packageCode.trim()) {
        alert('Please enter a package code');
        return false;
    }
    
    if (!packageName.trim()) {
        alert('Please enter a package name');
        return false;
    }
    
    return true;
}

// Optional: Auto-generate package code from name
document.getElementById('package_name').addEventListener('blur', function() {
    var codeField = document.getElementById('package_code');
    if (!codeField.value.trim()) {
        // Generate a code from the name
        var name = this.value.trim();
        if (name) {
            // Take first 3 letters and add random number
            var prefix = name.substring(0, 3).toUpperCase();
            var randomNum = Math.floor(Math.random() * 900 + 100);
            codeField.value = prefix + '-' + randomNum;
        }
    }
});
</script>

<?php 
require_once 'footer.php';
ob_end_flush(); // End output buffering
?>