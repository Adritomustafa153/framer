<?php
// admin/client-gallery-upload.php
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

// Helper: create thumbnail
function makeThumb($src, $dest, $width) {
    $info = getimagesize($src);
    if (!$info) return false;
    switch ($info[2]) {
        case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($src); break;
        case IMAGETYPE_PNG: $img = imagecreatefrompng($src); break;
        case IMAGETYPE_WEBP: $img = imagecreatefromwebp($src); break;
        default: return false;
    }
    $ow = imagesx($img); $oh = imagesy($img);
    $h = ($oh / $ow) * $width;
    $thumb = imagecreatetruecolor($width, $h);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $h, $ow, $oh);
    imagejpeg($thumb, $dest, 85);
    imagedestroy($img); imagedestroy($thumb);
    return true;
}

// AJAX handler for uploads (used by Dropzone)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        $category = $_POST['category'] ?? 'highlights';
        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'Upload error']);
            exit;
        }
        $uploadDir = "../uploads/client-gallery/{$id}/{$category}/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $thumbPath = $uploadDir . 'thumb_' . $filename;
            makeThumb($targetPath, $thumbPath, 300);
            $imageModel->create([
                'gallery_id' => $id,
                'category' => $category,
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_path' => "uploads/client-gallery/{$id}/{$category}/{$filename}",
                'thumbnail_path' => "uploads/client-gallery/{$id}/{$category}/thumb_{$filename}",
                'size' => $file['size']
            ]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Move failed']);
        }
        exit;
    }
    // Move category (AJAX)
    if ($action === 'move') {
        $image_id = (int)$_POST['image_id'];
        $new_cat = $_POST['new_cat'];
        $imageModel->updateCategory($image_id, $new_cat);
        echo json_encode(['success' => true]);
        exit;
    }
    // Delete image (AJAX)
    if ($action === 'delete') {
        $image_id = (int)$_POST['image_id'];
        $img = $imageModel->getById($image_id);
        if ($img) {
            @unlink('../' . $img['file_path']);
            @unlink('../' . $img['thumbnail_path']);
            $imageModel->deleteById($image_id);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

// Fetch existing images grouped by category
$categories = ['cover', 'highlights', 'couple_shots', 'event', 'family'];
$imagesByCat = [];
foreach ($categories as $cat) {
    $imagesByCat[$cat] = $imageModel->getByGalleryAndCategory($id, $cat);
}
?>

<style>
    .dropzone { border: 2px dashed #007bff; background: #f8f9fa; padding: 20px; text-align: center; cursor: pointer; margin-bottom: 15px; }
    .category-buttons { margin-bottom: 15px; }
    .category-btn.active { background-color: #007bff; color: white; }
    .image-card { position: relative; margin-bottom: 20px; border: 1px solid #ddd; padding: 5px; border-radius: 5px; background: #fff; }
    .image-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
    .image-actions { margin-top: 5px; font-size: 12px; }
    .upload-controls { display: flex; gap: 10px; align-items: center; margin-top: 10px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Gallery: <?= htmlspecialchars($gallery['title']) ?></h2>
    <div>
        <a href="../client-gallery.php?code=<?= $gallery['gallery_code'] ?>" target="_blank" class="btn btn-info"><i class="bi bi-eye"></i> View Gallery</a>
        <a href="client-galleries.php" class="btn btn-secondary">Back to Galleries</a>
    </div>
</div>

<div class="category-buttons">
    <?php foreach ($categories as $cat): ?>
        <button class="btn btn-outline-primary category-btn" data-cat="<?= $cat ?>"><?= ucfirst(str_replace('_', ' ', $cat)) ?></button>
    <?php endforeach; ?>
</div>

<div id="dropzone" class="dropzone">
    <p>📁 Drag & drop images here or click to select files.</p>
</div>

<div class="upload-controls">
    <button id="uploadBtn" class="btn btn-success"><i class="bi bi-cloud-upload"></i> Upload Selected Images</button>
    <button id="folderBtn" class="btn btn-secondary"><i class="bi bi-folder2"></i> Select Folder</button>
    <span id="uploadStatus" class="text-muted"></span>
</div>

<hr>
<h4>Existing Images</h4>
<div id="existing-images">
    <?php foreach ($categories as $cat): ?>
        <h5 class="mt-3"><?= ucfirst(str_replace('_', ' ', $cat)) ?></h5>
        <div class="row" id="cat-<?= $cat ?>">
            <?php foreach ($imagesByCat[$cat] as $img): ?>
                <div class="col-md-3 image-card" data-id="<?= $img['id'] ?>" data-cat="<?= $cat ?>">
                    <img src="../<?= $img['thumbnail_path'] ?: $img['file_path'] ?>" alt="">
                    <div class="image-actions">
                        <select class="form-select form-select-sm move-cat">
                            <option value="">Move to...</option>
                            <?php foreach ($categories as $c): if ($c == $cat) continue; ?>
                                <option value="<?= $c ?>"><?= ucfirst(str_replace('_', ' ', $c)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a href="../<?= $img['file_path'] ?>" download class="btn btn-sm btn-secondary mt-1"><i class="bi bi-download"></i> Download</a>
                        <button class="btn btn-sm btn-danger mt-1 delete-img"><i class="bi bi-trash"></i> Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.css">
<script>
let currentCategory = 'highlights';
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCategory = btn.dataset.cat;
    });
});

Dropzone.autoDiscover = false;
let myDropzone = new Dropzone("#dropzone", {
    url: window.location.href + "?id=<?= $id ?>",
    paramName: "file",
    autoProcessQueue: false,
    uploadMultiple: false,
    parallelUploads: 1,
    addRemoveLinks: true,
    init: function() {
        let dz = this;
        dz.on("sending", (file, xhr, formData) => {
            formData.append("action", "upload");
            formData.append("category", currentCategory);
        });
        document.getElementById("uploadBtn").onclick = () => {
            if (dz.getQueuedFiles().length === 0) {
                document.getElementById("uploadStatus").innerHTML = "No files selected.";
                return;
            }
            document.getElementById("uploadStatus").innerHTML = "Uploading...";
            dz.processQueue();
        };
        dz.on("queuecomplete", () => {
            document.getElementById("uploadStatus").innerHTML = "✅ Upload complete. Reloading...";
            setTimeout(() => location.reload(), 1500);
        });
        dz.on("error", (file, msg) => document.getElementById("uploadStatus").innerHTML = "❌ Error: " + msg);
    }
});

// Folder upload support
document.getElementById("folderBtn").addEventListener("click", function() {
    let input = document.createElement("input");
    input.type = "file";
    input.webkitdirectory = true;
    input.multiple = true;
    input.onchange = function(e) {
        let files = Array.from(e.target.files);
        files.forEach(file => myDropzone.addFile(file));
    };
    input.click();
});

// Move category (AJAX)
document.querySelectorAll('.move-cat').forEach(select => {
    select.addEventListener('change', function() {
        let imgDiv = this.closest('.image-card');
        let imgId = imgDiv.dataset.id;
        let newCat = this.value;
        if (!newCat) return;
        fetch(window.location.href + "?id=<?= $id ?>", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `action=move&image_id=${imgId}&new_cat=${newCat}`
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert('Move failed');
        });
    });
});

// Delete image (AJAX)
document.querySelectorAll('.delete-img').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this image?')) return;
        let imgDiv = this.closest('.image-card');
        let imgId = imgDiv.dataset.id;
        fetch(window.location.href + "?id=<?= $id ?>", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `action=delete&image_id=${imgId}`
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert('Delete failed');
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>