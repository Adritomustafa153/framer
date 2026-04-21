<?php
require_once '../config/database.php';
require_once '../models/ClientGallery.php';
require_once 'header.php';

$db = (new Database())->getConnection();
$galleryModel = new ClientGallery($db);
$galleries = $galleryModel->getAll('created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Client Galleries</h2>
    <a href="client-gallery-create.php" class="btn btn-primary">+ New Gallery</a>
</div>

<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Code</th><th>Title</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($galleries as $g): ?>
    <tr>
        <td><?= $g['id'] ?></td>
        <td><?= htmlspecialchars($g['gallery_code']) ?></td>
        <td><?= htmlspecialchars($g['title']) ?></td>
        <td><?= $g['is_active'] ? 'Active' : 'Inactive' ?></td>
        <td><?= date('d M Y', strtotime($g['created_at'])) ?></td>
        <td>
            <a href="client-gallery-edit.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-info">Edit</a>
            <a href="client-gallery-upload.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-primary">Upload</a>
            <a href="../client-gallery.php?code=<?= $g['gallery_code'] ?>" target="_blank" class="btn btn-sm btn-success">View</a>
            <a href="client-gallery-share.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-warning">Share</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>