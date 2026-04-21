<?php
// admin/photographer-edit.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/Photographer.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$photographer = new Photographer($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

// Handle image upload
function uploadImage($file) {
    $target_dir = "../uploads/photographers/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $fileName = time() . '_' . uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $fileName;
    
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB.'];
    }
    
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed.'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'path' => 'uploads/photographers/' . $fileName];
    } else {
        return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'name' => $_POST['name'],
        'bio' => $_POST['bio'],
        'sort_order' => $_POST['sort_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Handle image upload
    if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] == 0) {
        $uploadResult = uploadImage($_FILES['photo_file']);
        if ($uploadResult['success']) {
            $data['photo'] = $uploadResult['path'];
            
            // Delete old image if editing
            if ($isEdit) {
                $oldData = $photographer->getById($id);
                if ($oldData && !empty($oldData['photo']) && strpos($oldData['photo'], 'uploads/') !== false) {
                    $oldFile = '../' . $oldData['photo'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
            }
        } else {
            $error = $uploadResult['message'];
        }
    } elseif (isset($_POST['photo_url']) && !empty($_POST['photo_url'])) {
        $data['photo'] = $_POST['photo_url'];
    }
    
    if (!isset($error)) {
        if ($isEdit) {
            if (!isset($data['photo'])) {
                $oldData = $photographer->getById($id);
                $data['photo'] = $oldData['photo'];
            }
            
            if ($photographer->update($id, $data)) {
                header("Location: photographers.php?msg=updated");
                exit();
            }
        } else {
            if ($photographer->create($data)) {
                header("Location: photographers.php?msg=created");
                exit();
            }
        }
    }
}

// Get data if editing
$itemData = [];
if ($isEdit) {
    $itemData = $photographer->getById($id);
    if (!$itemData) {
        header("Location: photographers.php");
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit Photographer' : 'Add New Photographer'; ?></h2>
    <a href="photographers.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Photographers
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required 
                               value="<?php echo htmlspecialchars($itemData['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($itemData['bio'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Settings</h6>
                            
                            <?php if ($isEdit && !empty($itemData['photo'])): 
                                $photoUrl = $itemData['photo'];
                                if (strpos($photoUrl, 'uploads/') === 0) {
                                    $photoUrl = '../' . $photoUrl;
                                }
                            ?>
                                <div class="mb-3 text-center">
                                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" 
                                         alt="Current photo" 
                                         class="img-fluid border"
                                         style="max-height: 150px; width: auto; border-radius: 50%;">
                                    <p class="text-muted small mt-1">Current Photo</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Photo URL</label>
                                <input type="url" name="photo_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($itemData['photo'] ?? ''); ?>"
                                       placeholder="https://example.com/photo.jpg">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Or Upload Photo</label>
                                <input type="file" name="photo_file" class="form-control" accept="image/*">
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
                    <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Photographer
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once 'footer.php';
ob_end_flush();
?>