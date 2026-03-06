<?php
// admin/gallery-settings.php

// Start output buffering
ob_start();

require_once '../config/database.php';
require_once '../models/GallerySettings.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$settings = new GallerySettings($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings->set('slideshow_music_url', $_POST['slideshow_music_url'], 'URL of background music for slideshow');
    $settings->set('slideshow_autoplay', isset($_POST['slideshow_autoplay']) ? '1' : '0', 'Autoplay slideshow');
    $settings->set('slideshow_delay', $_POST['slideshow_delay'], 'Delay between slides in milliseconds');
    $settings->set('gallery_title', $_POST['gallery_title'], 'Title for gallery section');
    $settings->set('images_per_row', $_POST['images_per_row'], 'Number of images per row on desktop');
    
    $success = "Gallery settings updated successfully!";
}

// Get current settings
$musicUrl = $settings->get('slideshow_music_url') ?? '';
$autoplay = $settings->get('slideshow_autoplay') ?? '1';
$delay = $settings->get('slideshow_delay') ?? '3000';
$galleryTitle = $settings->get('gallery_title') ?? 'Our Gallery';
$imagesPerRow = $settings->get('images_per_row') ?? '4';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gallery Settings</h2>
    <a href="gallery.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Gallery
    </a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Slideshow Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Background Music URL</label>
                        <input type="url" name="slideshow_music_url" class="form-control" 
                               value="<?php echo htmlspecialchars($musicUrl); ?>"
                               placeholder="https://example.com/music.mp3">
                        <small class="text-muted">Enter URL of an audio file (MP3, OGG, etc.)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="slideshow_autoplay" class="form-check-input" id="autoplay"
                                   <?php echo $autoplay == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="autoplay">Autoplay slideshow</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slideshow Delay (milliseconds)</label>
                        <input type="number" name="slideshow_delay" class="form-control" 
                               value="<?php echo htmlspecialchars($delay); ?>" min="1000" step="500">
                        <small class="text-muted">1000ms = 1 second</small>
                    </div>
                    
                    <button type="submit" class="btn btn-dark">Save Slideshow Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Display Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Gallery Title</label>
                        <input type="text" name="gallery_title" class="form-control" 
                               value="<?php echo htmlspecialchars($galleryTitle); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Images Per Row (Desktop)</label>
                        <select name="images_per_row" class="form-control">
                            <option value="3" <?php echo $imagesPerRow == '3' ? 'selected' : ''; ?>>3 Images</option>
                            <option value="4" <?php echo $imagesPerRow == '4' ? 'selected' : ''; ?>>4 Images</option>
                            <option value="5" <?php echo $imagesPerRow == '5' ? 'selected' : ''; ?>>5 Images</option>
                            <option value="6" <?php echo $imagesPerRow == '6' ? 'selected' : ''; ?>>6 Images</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-dark">Save Display Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'footer.php';
ob_end_flush();
?>