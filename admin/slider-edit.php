<?php
// admin/slider-edit.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/Slider.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$slider = new Slider($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$isEdit = $id !== null;

// Handle image upload
function uploadImage($file) {
    $target_dir = "../uploads/slider/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $fileName = time() . '_' . uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $fileName;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB.'];
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed.'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'path' => 'uploads/slider/' . $fileName];
    } else {
        return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'link' => $_POST['link'],
            'sort_order' => $_POST['sort_order'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Handle image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $uploadResult = uploadImage($_FILES['image_file']);
            if ($uploadResult['success']) {
                $data['image_url'] = $uploadResult['path'];
                
                // Delete old image if editing
                if ($isEdit) {
                    $oldData = $slider->getById($id);
                    if ($oldData && !empty($oldData['image_url']) && strpos($oldData['image_url'], 'uploads/') !== false) {
                        $oldFile = '../' . $oldData['image_url'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                }
            } else {
                $error = $uploadResult['message'];
            }
        } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
            $data['image_url'] = $_POST['image_url'];
        } elseif (!$isEdit) {
            $error = 'Please provide an image (upload or URL)';
        }
        
        if (!isset($error)) {
            if ($isEdit) {
                // If editing and no new image, keep old image
                if (!isset($data['image_url'])) {
                    $oldData = $slider->getById($id);
                    $data['image_url'] = $oldData['image_url'];
                }
                
                if ($slider->update($id, $data)) {
                    header("Location: slider.php?msg=updated");
                    exit();
                }
            } else {
                if ($slider->create($data)) {
                    header("Location: slider.php?msg=created");
                    exit();
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get slide data if editing
$slideData = [];
if ($isEdit) {
    $slideData = $slider->getById($id);
    if (!$slideData) {
        header("Location: slider.php");
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isEdit ? 'Edit Slide' : 'Add New Slide'; ?></h2>
    <a href="slider.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Slider
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
        <form method="POST" enctype="multipart/form-data" id="sliderForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($slideData['title'] ?? ''); ?>"
                                   placeholder="e.g., Welcome to Framer">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Brief description of the slide"><?php echo htmlspecialchars($slideData['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link (Optional)</label>
                            <input type="url" name="link" class="form-control" 
                                   value="<?php echo htmlspecialchars($slideData['link'] ?? ''); ?>"
                                   placeholder="https://example.com/page">
                            <small class="text-muted">Where users will be redirected when clicking the slide</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="<?php echo htmlspecialchars($slideData['sort_order'] ?? 0); ?>"
                                   min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Image</h6>
                            
                            <?php if ($isEdit && !empty($slideData['image_url'])): ?>
                                <div class="mb-3 text-center">
                                    <img src="<?php echo htmlspecialchars($slideData['image_url']); ?>" 
                                         alt="Current slide" 
                                         class="img-fluid border"
                                         style="max-height: 150px; width: auto;">
                                    <p class="text-muted small mt-1">Current Image</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3" id="image-preview-container" style="display: none;">
                                <div class="text-center">
                                    <img id="image-preview" src="#" alt="Preview" class="img-fluid border" style="max-height: 150px; width: auto;">
                                    <p class="text-muted small mt-1">Preview</p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Image URL</label>
                                <input type="url" name="image_url" id="image_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($slideData['image_url'] ?? ''); ?>"
                                       placeholder="https://example.com/image.jpg">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Or Upload Image</label>
                                <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                                <small class="text-muted">Max size: 5MB. Supported: JPG, PNG, GIF, WEBP</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                           <?php echo ($slideData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active (visible on website)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Slide
                    </button>
                    <a href="slider.php" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Preview image when URL is entered
document.getElementById('image_url').addEventListener('blur', function() {
    var url = this.value;
    var previewContainer = document.getElementById('image-preview-container');
    var preview = document.getElementById('image-preview');
    
    if (url) {
        preview.src = url;
        previewContainer.style.display = 'block';
    } else {
        previewContainer.style.display = 'none';
    }
});

// Preview image when file is selected
document.getElementById('image_file').addEventListener('change', function(e) {
    var previewContainer = document.getElementById('image-preview-container');
    var preview = document.getElementById('image-preview');
    
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
            
            // Clear URL field
            document.getElementById('image_url').value = '';
        }
        reader.readAsDataURL(e.target.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
});

// Show preview if URL exists on page load
window.addEventListener('load', function() {
    var urlField = document.getElementById('image_url');
    if (urlField.value) {
        var previewContainer = document.getElementById('image-preview-container');
        var preview = document.getElementById('image-preview');
        preview.src = urlField.value;
        previewContainer.style.display = 'block';
    }
});
</script>

<?php 
require_once 'footer.php';
ob_end_flush();
?>