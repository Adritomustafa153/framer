<?php
// admin/client-gallery-edit.php
require_once '../config/database.php';
require_once '../models/ClientGallery.php';
require_once '../models/ClientGalleryImage.php';
require_once 'header.php';

$id = (int)$_GET['id'];
$db = (new Database())->getConnection();
$galleryModel = new ClientGallery($db);
$gallery = $galleryModel->getById($id);
if (!$gallery) die("Gallery not found");

$imageModel = new ClientGalleryImage($db);
$coverImages = $imageModel->getByGalleryAndCategory($id, 'cover');

// Handle logo upload
$logo_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../uploads/client-gallery/logos/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    $logoName = 'logo_' . $id . '_' . time() . '.' . $ext;
    $logoPath = $uploadDir . $logoName;
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
        $data['logo_path'] = "uploads/client-gallery/logos/" . $logoName;
    } else {
        $logo_error = "Logo upload failed.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title']),
        'headline' => trim($_POST['headline']),
        'story' => trim($_POST['story']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'cover_image_id' => !empty($_POST['cover_image_id']) ? (int)$_POST['cover_image_id'] : null,
        'cover_orientation' => $_POST['cover_orientation'],
        'share_date' => $_POST['share_date'],
    ];
    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    // Set expiry date based on duration
    if (!empty($_POST['expiry_duration'])) {
        $data['expiry_date'] = $galleryModel->setExpiryDate($_POST['expiry_duration']);
    } elseif (isset($_POST['expiry_date']) && !empty($_POST['expiry_date'])) {
        $data['expiry_date'] = $_POST['expiry_date'];
    }
    $galleryModel->update($id, $data);
    $success = "Gallery updated.";
    $gallery = $galleryModel->getById($id);
}
?>

<h2>Edit Gallery: <?= htmlspecialchars($gallery['title']) ?></h2>
<?php if (isset($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($logo_error): ?><div class="alert alert-danger"><?= $logo_error ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($gallery['title']) ?>" required></div>
            <div class="mb-3"><label>Headline</label><input type="text" name="headline" class="form-control" value="<?= htmlspecialchars($gallery['headline']) ?>"></div>
            <div class="mb-3"><label>Story</label><textarea name="story" rows="4" class="form-control"><?= htmlspecialchars($gallery['story']) ?></textarea></div>
            <div class="mb-3"><label>Cover Photo</label>
                <select name="cover_image_id" class="form-control">
                    <option value="">-- None --</option>
                    <?php foreach ($coverImages as $img): ?>
                        <option value="<?= $img['id'] ?>" <?= ($gallery['cover_image_id'] == $img['id']) ? 'selected' : '' ?>><?= htmlspecialchars($img['original_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label>Cover Orientation</label>
                <select name="cover_orientation" class="form-control">
                    <option value="landscape" <?= $gallery['cover_orientation'] == 'landscape' ? 'selected' : '' ?>>Landscape (full width)</option>
                    <option value="portrait" <?= $gallery['cover_orientation'] == 'portrait' ? 'selected' : '' ?>>Portrait (image left, text right)</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3"><label>Logo (optional)</label><br>
                <?php if ($gallery['logo_path']): ?>
                    <img src="../<?= $gallery['logo_path'] ?>" style="max-height:60px; margin-bottom:10px;"><br>
                <?php endif; ?>
                <input type="file" name="logo" class="form-control" accept="image/*">
                <small class="text-muted">Leave empty to keep current logo.</small>
            </div>
            <div class="mb-3"><label>Share Date</label><input type="date" name="share_date" class="form-control" value="<?= $gallery['share_date'] ?>"></div>
            <div class="mb-3"><label>Expiry Duration (set automatically)</label>
                <select name="expiry_duration" class="form-control">
                    <option value="">-- No expiry --</option>
                    <option value="7days">7 days</option>
                    <option value="1month">1 month</option>
                    <option value="6months">6 months</option>
                    <option value="1year">1 year</option>
                </select>
                <small>Current expiry: <?= $gallery['expiry_date'] ? date('Y-m-d', strtotime($gallery['expiry_date'])) : 'No expiry' ?></small>
            </div>
            <div class="mb-3"><label>Or set exact expiry date</label><input type="datetime-local" name="expiry_date" class="form-control" value="<?= $gallery['expiry_date'] ? date('Y-m-d\TH:i', strtotime($gallery['expiry_date'])) : '' ?>"></div>
            <div class="mb-3"><label>New Password (optional)</label><input type="text" name="password" class="form-control"></div>
            <div class="mb-3 form-check"><input type="checkbox" name="is_active" <?= $gallery['is_active'] ? 'checked' : '' ?>> <label>Active</label></div>
        </div>
    </div>
    <button type="submit" class="btn btn-dark">Save Changes</button>
</form>

<?php require_once 'footer.php'; ?>