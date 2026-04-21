<?php
// admin/client-gallery-share.php
require_once '../config/database.php';
require_once '../models/ClientGallery.php';
require_once '../models/ClientGalleryImage.php';
require_once 'header.php';

$id = (int)$_GET['id'];
$db = (new Database())->getConnection();
$galleryModel = new ClientGallery($db);
$gallery = $galleryModel->getWithDetails($id);
if (!$gallery) die("Gallery not found");

$imageModel = new ClientGalleryImage($db);
$cover = $imageModel->getCover($id);
$coverImageUrl = $cover ? 'http://' . $_SERVER['HTTP_HOST'] . '/framer/' . ltrim($cover['file_path'], './') : '';
?>

<style>
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .btn-loading { pointer-events: none; opacity: 0.7; }
</style>

<h2>Share Gallery: <?= htmlspecialchars($gallery['title']) ?></h2>
<div class="row">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <p><strong>Gallery Code:</strong> <?= $gallery['gallery_code'] ?></p>
            <p><strong>Public Link:</strong> <a href="../client-gallery.php?code=<?= $gallery['gallery_code'] ?>" target="_blank"><?= $gallery['gallery_code'] ?></a></p>
            <p><strong>Client Email:</strong> <?= $gallery['email'] ?? 'Not available' ?></p>
            <?php if ($cover): ?>
                <p><strong>Cover Image Preview:</strong></p>
                <img src="<?= $coverImageUrl ?>" style="max-width:100%; max-height:150px; border-radius:5px;" alt="Cover preview">
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <div id="emailResult"></div>
            <form id="shareForm">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="mb-3"><label>Recipient Email *</label><input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($gallery['email'] ?? '') ?>"></div>
                <div class="mb-3"><label>Personal Message (optional)</label><textarea name="message" rows="4" class="form-control" placeholder="Dear client, here are your beautiful memories..."></textarea></div>
                <button type="submit" id="sendBtn" class="btn btn-primary"><i class="bi bi-envelope"></i> Send Email</button>
            </form>
        </div></div>
    </div>
</div>

<script>
document.getElementById('shareForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const sendBtn = document.getElementById('sendBtn');
    const originalHtml = sendBtn.innerHTML;
    sendBtn.innerHTML = '<span class="loading-spinner"></span> Sending...';
    sendBtn.disabled = true;
    sendBtn.classList.add('btn-loading');
    
    fetch('client-gallery-share-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('emailResult');
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
        sendBtn.innerHTML = originalHtml;
        sendBtn.disabled = false;
        sendBtn.classList.remove('btn-loading');
        setTimeout(() => resultDiv.innerHTML = '', 5000);
    })
    .catch(error => {
        document.getElementById('emailResult').innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
        sendBtn.innerHTML = originalHtml;
        sendBtn.disabled = false;
        sendBtn.classList.remove('btn-loading');
    });
});
</script>
<?php require_once 'footer.php'; ?>