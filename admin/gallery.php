<?php
// admin/gallery.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/Gallery.php';
require_once '../models/GallerySettings.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$gallery = new Gallery($db);
$gallerySettings = new GallerySettings($db);

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image path to delete file
    $image = $gallery->getById($id);
    if ($image && !empty($image['image_url']) && strpos($image['image_url'], 'uploads/gallery/') !== false) {
        $filePath = '../' . $image['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        // Also delete thumbnail if exists
        if (!empty($image['thumbnail_url']) && $image['thumbnail_url'] != $image['image_url']) {
            $thumbPath = '../' . $image['thumbnail_url'];
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
        }
    }
    
    if ($gallery->delete($id)) {
        header("Location: gallery.php?msg=deleted");
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $image = $gallery->getById($_GET['toggle']);
    if ($image) {
        $newStatus = $image['is_active'] ? 0 : 1;
        $gallery->update($_GET['toggle'], ['is_active' => $newStatus]);
        header("Location: gallery.php?msg=toggled");
        exit();
    }
}

// Handle featured toggle
if (isset($_GET['featured'])) {
    $image = $gallery->getById($_GET['featured']);
    if ($image) {
        $newFeatured = $image['is_featured'] ? 0 : 1;
        $gallery->update($_GET['featured'], ['is_featured' => $newFeatured]);
        header("Location: gallery.php?msg=featured");
        exit();
    }
}

// Get all gallery images
$galleryImages = $gallery->getAll('sort_order ASC, id DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Gallery</h2>
    <a href="gallery-upload.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Upload New Images
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Image uploaded successfully!";
        if ($_GET['msg'] == 'updated') echo "Image updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Image deleted successfully!";
        if ($_GET['msg'] == 'toggled') echo "Image status updated successfully!";
        if ($_GET['msg'] == 'featured') echo "Featured status updated successfully!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Gallery Preview</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php if ($galleryImages && $galleryImages->rowCount() > 0): ?>
                <?php while ($row = $galleryImages->fetch()): 
                    $imageUrl = $row['image_url'];
                    if (strpos($imageUrl, 'uploads/') === 0) {
                        $imageUrl = '../' . $imageUrl;
                    }
                ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 <?php echo !$row['is_active'] ? 'opacity-50' : ''; ?>">
                            <div style="height: 180px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; padding: 5px; border-bottom: 1px solid #ddd;">
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     class="img-fluid" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>"
                                     style="max-height: 170px; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                     onerror="this.src='https://via.placeholder.com/200x150?text=Error'; this.onerror=null;">
                            </div>
                            <div class="card-body p-2">
                                <h6 class="card-title small mb-1"><?php echo htmlspecialchars($row['title'] ?: 'Untitled'); ?></h6>
                                <div class="small">
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                    <?php if ($row['is_featured']): ?>
                                        <span class="badge bg-warning">Featured</span>
                                    <?php endif; ?>
                                    <span class="badge bg-dark">Order: <?php echo $row['sort_order']; ?></span>
                                </div>
                                <div class="mt-2">
                                    <a href="gallery-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="gallery.php?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="bi bi-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </a>
                                    <a href="gallery.php?featured=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="<?php echo $row['is_featured'] ? 'Remove Featured' : 'Make Featured'; ?>">
                                        <i class="bi bi-star<?php echo $row['is_featured'] ? '-fill' : ''; ?>"></i>
                                    </a>
                                    <a href="gallery.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this image?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No gallery images yet. <a href="gallery-upload.php">Upload your first image</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>