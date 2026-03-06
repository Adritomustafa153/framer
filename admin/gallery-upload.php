<?php
// admin/gallery-upload.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/Gallery.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$gallery = new Gallery($db);

// Handle multiple image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_multiple'])) {
    $uploadedCount = 0;
    $errors = [];
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/gallery/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $totalFiles = count($files['name']);
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($files['error'][$i] == 0) {
                $fileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $files['name'][$i]);
                $targetPath = $uploadDir . $fileName;
                
                // Check file type
                $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($imageFileType, $allowedTypes)) {
                    $errors[] = "File {$files['name'][$i]} is not an allowed image type.";
                    continue;
                }
                
                // Check file size (5MB max)
                if ($files['size'][$i] > 5000000) {
                    $errors[] = "File {$files['name'][$i]} is too large. Max size is 5MB.";
                    continue;
                }
                
                if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                    // Create thumbnail
                    $thumbPath = $uploadDir . 'thumb_' . $fileName;
                    createThumbnail($targetPath, $thumbPath, 300, 200);
                    
                    // Save to database
                    $data = [
                        'title' => pathinfo($files['name'][$i], PATHINFO_FILENAME),
                        'description' => '',
                        'image_url' => 'uploads/gallery/' . $fileName,
                        'thumbnail_url' => 'uploads/gallery/thumb_' . $fileName,
                        'category' => $_POST['category'] ?? '',
                        'sort_order' => 0,
                        'is_active' => 1,
                        'is_featured' => 0
                    ];
                    
                    if ($gallery->create($data)) {
                        $uploadedCount++;
                    } else {
                        $errors[] = "Failed to save {$files['name'][$i]} to database.";
                    }
                } else {
                    $errors[] = "Failed to upload {$files['name'][$i]}.";
                }
            }
        }
    }
    
    if ($uploadedCount > 0) {
        header("Location: gallery.php?msg=created");
        exit();
    }
}

// Function to create thumbnail
function createThumbnail($source, $destination, $width, $height) {
    list($origWidth, $origHeight) = getimagesize($source);
    
    // Calculate aspect ratio
    $ratio = min($width / $origWidth, $height / $origHeight);
    $newWidth = $origWidth * $ratio;
    $newHeight = $origHeight * $ratio;
    
    // Create image resource based on file type
    $imageInfo = pathinfo($source);
    $extension = strtolower($imageInfo['extension']);
    
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $srcImage = imagecreatefromjpeg($source);
            break;
        case 'png':
            $srcImage = imagecreatefrompng($source);
            break;
        case 'gif':
            $srcImage = imagecreatefromgif($source);
            break;
        case 'webp':
            $srcImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    // Create thumbnail
    $thumbImage = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG
    if ($extension == 'png') {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
        $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
        imagefilledrectangle($thumbImage, 0, 0, $width, $height, $transparent);
    }
    
    // Resize and crop
    imagecopyresampled($thumbImage, $srcImage, 
        ($width - $newWidth) / 2, ($height - $newHeight) / 2, 0, 0, 
        $newWidth, $newHeight, $origWidth, $origHeight);
    
    // Save thumbnail
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($thumbImage, $destination, 85);
            break;
        case 'png':
            imagepng($thumbImage, $destination, 6);
            break;
        case 'gif':
            imagegif($thumbImage, $destination);
            break;
        case 'webp':
            imagewebp($thumbImage, $destination, 85);
            break;
    }
    
    imagedestroy($srcImage);
    imagedestroy($thumbImage);
    return true;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Upload Images to Gallery</h2>
    <a href="gallery.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Gallery
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Select Images (Multiple allowed)</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple required id="imageInput">
                        <small class="text-muted">You can select multiple images at once. Max file size: 5MB per image. Allowed: JPG, PNG, GIF, WEBP</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category (Optional)</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g., Wedding, Portrait, Event">
                        <small class="text-muted">Images will be grouped by this category</small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Preview</h6>
                            <div id="preview-container" class="text-center">
                                <p class="text-muted">No images selected</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" name="upload_multiple" class="btn btn-dark">
                    <i class="bi bi-upload"></i> Upload Images
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview-container');
    previewContainer.innerHTML = '';
    
    if (this.files.length > 0) {
        const row = document.createElement('div');
        row.className = 'row g-2';
        
        for (let i = 0; i < Math.min(this.files.length, 6); i++) {
            const file = this.files[i];
            const reader = new FileReader();
            
            const col = document.createElement('div');
            col.className = 'col-4';
            
            reader.onload = function(e) {
                col.innerHTML = '<img src="' + e.target.result + '" class="img-fluid border" style="height: 80px; width: 100%; object-fit: cover;">';
            }
            
            reader.readAsDataURL(file);
            row.appendChild(col);
        }
        
        if (this.files.length > 6) {
            const more = document.createElement('div');
            more.className = 'col-12 text-center mt-2';
            more.innerHTML = '<small class="text-muted">+' + (this.files.length - 6) + ' more images</small>';
            row.appendChild(more);
        }
        
        previewContainer.appendChild(row);
    } else {
        previewContainer.innerHTML = '<p class="text-muted">No images selected</p>';
    }
});
</script>

<?php 
require_once 'footer.php';
ob_end_flush();
?>