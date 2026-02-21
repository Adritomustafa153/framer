<?php
// admin/settings.php
require_once '../config/database.php';
require_once '../models/Settings.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            $settings->set($key, $value);
        }
    }
    $success = "Settings updated successfully!";
}

// Get all settings
$allSettings = [];
$result = $settings->getAll();
foreach ($result as $row) {
    $allSettings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Website Settings</h2>
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
                <h5 class="mb-0">General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Site Title</label>
                        <input type="text" name="site_title" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['site_title'] ?? 'Framer Photography'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Site Description</label>
                        <textarea name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($allSettings['site_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['contact_email'] ?? 'framer.wedding@gmail.com'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['contact_phone'] ?? '+8801829093616'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['whatsapp_number'] ?? '8801829093616'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($allSettings['address'] ?? 'Rajonigondha Vally, 178/B Khilgaon Chowdhurypara, Matirmoshjheed jheelpar, Dhaka 1219'); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Business Hours</label>
                        <input type="text" name="business_hours" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['business_hours'] ?? 'Monday - Saturday: 9am to 7pm'); ?>">
                    </div>
                    
                    <button type="submit" name="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Save General Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Social Media Links</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Facebook URL</label>
                        <input type="url" name="facebook_url" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['facebook_url'] ?? 'https://www.facebook.com/profile.php?id=100091517055172'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Instagram URL</label>
                        <input type="url" name="instagram_url" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['instagram_url'] ?? 'https://www.instagram.com/framer.wedding/'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">YouTube URL</label>
                        <input type="url" name="youtube_url" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['youtube_url'] ?? 'https://www.youtube.com/channel/UCmAlhSDX7kyi2eYllgitGRw'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pinterest URL</label>
                        <input type="url" name="pinterest_url" class="form-control" 
                               value="<?php echo htmlspecialchars($allSettings['pinterest_url'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" name="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Save Social Media Links
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Map Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Google Maps Embed URL</label>
                        <textarea name="map_embed_url" class="form-control" rows="3"><?php echo htmlspecialchars($allSettings['map_embed_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.767150611156!2d90.4158229749629!3d23.75568147866682!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x265d1f3a711b2773%3A0xf6aef7cf05e4bba4!2sFramer!5e0!3m2!1sen!2sbd!4v1771669824226!5m2!1sen!2sbd'); ?></textarea>
                    </div>
                    
                    <button type="submit" name="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Save Map Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>