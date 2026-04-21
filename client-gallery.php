<?php
// client-gallery.php
require_once 'config/database.php';
require_once 'models/ClientGallery.php';
require_once 'models/ClientGalleryImage.php';

$code = $_GET['code'] ?? '';
if (!$code) die('No gallery code provided.');

$db = (new Database())->getConnection();
$galleryModel = new ClientGallery($db);
$gallery = $galleryModel->getByCode($code);
if (!$gallery) die('Gallery not found.');

// Check expiry & active
if ($galleryModel->isExpired($gallery)) die('This gallery has expired.');
if (!$gallery['is_active']) die('Gallery is currently inactive.');

// Password protection
if ($gallery['password']) {
    session_start();
    if (!isset($_SESSION['client_gallery_pass_' . $gallery['id']]) && isset($_POST['password'])) {
        if (password_verify($_POST['password'], $gallery['password'])) {
            $_SESSION['client_gallery_pass_' . $gallery['id']] = true;
        } else $error = "Wrong password.";
    }
    if (!isset($_SESSION['client_gallery_pass_' . $gallery['id']])) {
        echo '<form method="POST"><input type="password" name="password"><button>Submit</button></form>';
        if (isset($error)) echo $error;
        exit;
    }
}

$imageModel = new ClientGalleryImage($db);
$cover = $imageModel->getCover($gallery['id']);
$logo = $gallery['logo_path'] ?? null;

$categories = [
    'highlights'   => 'Highlights',
    'couple_shots' => 'Couple Shots',
    'event'        => 'Event',
    'family'       => 'Family'
];
$imagesByCat = [];
foreach (array_keys($categories) as $cat) {
    $imagesByCat[$cat] = $imageModel->getByGalleryAndCategory($gallery['id'], $cat);
}

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
function gallery_url($path, $base) {
    $path = ltrim($path, './');
    return $base . '/' . $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($gallery['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .logo-img { max-height: 60px; margin-bottom: 20px; }
        .cover-landscape { width: 100%; max-height: 500px; object-fit: cover; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .cover-portrait-container { display: flex; gap: 30px; align-items: center; flex-wrap: wrap; }
        .cover-portrait-img { flex: 1; min-width: 250px; }
        .cover-portrait-img img { width: 100%; max-height: 400px; object-fit: contain; border-radius: 15px; }
        .cover-portrait-text { flex: 1; text-align: center; }
        .story-text { font-size: 1.1rem; line-height: 1.6; color: #555; }
        .gallery-tabs .nav-link { color: #333; font-weight: 500; }
        .gallery-tabs .nav-link.active { background-color: #87CEEB; color: white; border: none; }
        .gallery-img { transition: transform 0.2s; margin-bottom: 20px; position: relative; }
        .gallery-img:hover { transform: scale(1.02); }
        .btn-download {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 5px 12px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
            backdrop-filter: blur(4px);
        }
        .btn-download:hover { background: #000; color: white; }
        .btn-download i { margin-right: 5px; }
        .view-gallery-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #87CEEB;
            color: white;
            border-radius: 40px;
            padding: 12px 30px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
        }
        .view-gallery-btn:hover { background: #5ba3c7; color: white; }
        .no-images { padding: 40px; text-align: center; color: #888; background: #f0f0f0; border-radius: 10px; }
        .load-more-wrapper { text-align: center; margin: 30px 0; }
        .btn-load-more {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 10px 24px;
            font-weight: 500;
            transition: 0.2s;
        }
        .btn-load-more:hover { background: #5a6268; color: white; }
        .btn-load-more:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="container py-4">
    <?php if ($logo): ?>
        <div class="text-center mb-4">
            <img src="<?= gallery_url($logo, $basePath) ?>" class="logo-img" alt="Logo">
        </div>
    <?php endif; ?>

    <?php if ($cover): ?>
        <?php if ($gallery['cover_orientation'] == 'landscape'): ?>
            <img src="<?= gallery_url($cover['file_path'], $basePath) ?>" class="cover-landscape mb-4" alt="Cover">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold"><?= htmlspecialchars($gallery['title']) ?></h1>
                <?php if ($gallery['headline']): ?><h3 class="text-muted mt-2"><?= htmlspecialchars($gallery['headline']) ?></h3><?php endif; ?>
                <?php if ($gallery['story']): ?><div class="story-text mt-4"><?= nl2br(htmlspecialchars($gallery['story'])) ?></div><?php endif; ?>
                <div class="mt-3 text-muted small">Shared on <?= date('F d, Y', strtotime($gallery['share_date'])) ?></div>
                <a href="#gallery-tabs" class="view-gallery-btn mt-3" id="viewGalleryBtn"><i class="bi bi-images"></i> View Gallery</a>
            </div>
        <?php else: ?>
            <div class="cover-portrait-container mb-5">
                <div class="cover-portrait-img">
                    <img src="<?= gallery_url($cover['file_path'], $basePath) ?>" alt="Cover">
                </div>
                <div class="cover-portrait-text">
                    <?php if ($logo): ?>
                        <img src="<?= gallery_url($logo, $basePath) ?>" class="logo-img" style="max-height:50px; margin-bottom:15px;" alt="Logo">
                    <?php endif; ?>
                    <h1 class="display-5 fw-bold"><?= htmlspecialchars($gallery['title']) ?></h1>
                    <?php if ($gallery['headline']): ?><h3 class="text-muted mt-2"><?= htmlspecialchars($gallery['headline']) ?></h3><?php endif; ?>
                    <div class="story-text mt-3"><?= nl2br(htmlspecialchars($gallery['story'])) ?></div>
                    <div class="mt-3 text-muted small">Shared on <?= date('F d, Y', strtotime($gallery['share_date'])) ?></div>
                    <a href="#gallery-tabs" class="view-gallery-btn mt-3" id="viewGalleryBtn"><i class="bi bi-images"></i> View Gallery</a>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold"><?= htmlspecialchars($gallery['title']) ?></h1>
            <?php if ($gallery['headline']): ?><h3 class="text-muted mt-2"><?= htmlspecialchars($gallery['headline']) ?></h3><?php endif; ?>
            <?php if ($gallery['story']): ?><div class="story-text mt-4"><?= nl2br(htmlspecialchars($gallery['story'])) ?></div><?php endif; ?>
            <div class="mt-3 text-muted small">Shared on <?= date('F d, Y', strtotime($gallery['share_date'])) ?></div>
            <a href="#gallery-tabs" class="view-gallery-btn mt-3" id="viewGalleryBtn"><i class="bi bi-images"></i> View Gallery</a>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs gallery-tabs justify-content-center mb-4" id="galleryTab" role="tablist">
        <?php foreach ($categories as $key => $label): 
            $tabId = str_replace('_', '-', $key);
            $active = ($key == 'highlights') ? 'active' : '';
        ?>
            <li class="nav-item"><button class="nav-link <?= $active ?>" id="<?= $tabId ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $tabId ?>" type="button" role="tab"><?= $label ?></button></li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content" id="galleryTabContent">
        <?php foreach ($categories as $key => $label): 
            $tabId = str_replace('_', '-', $key);
            $active = ($key == 'highlights') ? 'show active' : '';
        ?>
            <div class="tab-pane fade <?= $active ?>" id="<?= $tabId ?>" role="tabpanel">
                <div class="row">
                    <?php if (!empty($imagesByCat[$key])): ?>
                        <?php foreach ($imagesByCat[$key] as $img): 
                            $fullUrl = gallery_url($img['file_path'], $basePath);
                            $thumbUrl = !empty($img['thumbnail_path']) ? gallery_url($img['thumbnail_path'], $basePath) : $fullUrl;
                            $socialUrl = !empty($img['social_path']) ? gallery_url($img['social_path'], $basePath) : $fullUrl;
                        ?>
                            <div class="col-md-4 col-sm-6 gallery-img" data-full="<?= $fullUrl ?>" data-social="<?= $socialUrl ?>">
                                <a href="<?= $fullUrl ?>" data-lightbox="gallery-<?= $key ?>" data-title="<?= htmlspecialchars($img['original_name']) ?>">
                                    <img src="<?= $thumbUrl ?>" class="img-fluid rounded shadow-sm" style="width:100%; height:260px; object-fit:cover;">
                                </a>
                                <button class="btn-download download-trigger" data-full="<?= $fullUrl ?>" data-social="<?= $socialUrl ?>"><i class="bi bi-download"></i> Download</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12"><div class="no-images"><i class="bi bi-camera"></i> No images in this category yet.</div></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="load-more-wrapper">
        <button id="loadMoreBtn" class="btn-load-more"><i class="bi bi-arrow-down-circle"></i> Load More</button>
    </div>
</div>

<div class="modal fade" id="downloadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-download"></i> Download Options</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center">
                <button id="downloadOriginalBtn" class="btn btn-primary w-100 mb-2"><i class="bi bi-file-image"></i> Original Quality</button>
                <button id="downloadSocialBtn" class="btn btn-success w-100"><i class="bi bi-instagram"></i> Social Media Size</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox-plus-jquery.min.js"></script>
<script>
    lightbox.option({ 'resizeDuration': 200, 'wrapAround': true, 'showImageNumberLabel': false });
    const tabButtons = Array.from(document.querySelectorAll('#galleryTab .nav-link'));
    let currentTabIndex = 0;
    function activateTab(index) {
        if (index >= tabButtons.length) return false;
        new bootstrap.Tab(tabButtons[index]).show();
        currentTabIndex = index;
        const btn = document.getElementById('loadMoreBtn');
        if (index === tabButtons.length - 1) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> All sections loaded';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Load More';
        }
        return true;
    }
    tabButtons.forEach((btn, idx) => btn.addEventListener('click', () => {
        currentTabIndex = idx;
        const loadBtn = document.getElementById('loadMoreBtn');
        if (idx === tabButtons.length - 1) {
            loadBtn.disabled = true;
            loadBtn.innerHTML = '<i class="bi bi-check-circle"></i> All sections loaded';
        } else {
            loadBtn.disabled = false;
            loadBtn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Load More';
        }
    }));
    document.getElementById('loadMoreBtn').addEventListener('click', () => {
        if (currentTabIndex < tabButtons.length - 1) {
            activateTab(currentTabIndex + 1);
            document.querySelector('.tab-pane.active')?.scrollIntoView({ behavior: 'smooth' });
        }
    });
    document.getElementById('viewGalleryBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('galleryTab').scrollIntoView({ behavior: 'smooth' });
    });
    let currentFull = '', currentSocial = '';
    const downloadModal = new bootstrap.Modal(document.getElementById('downloadModal'));
    document.querySelectorAll('.download-trigger').forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        currentFull = btn.dataset.full;
        currentSocial = btn.dataset.social;
        downloadModal.show();
    }));
    document.getElementById('downloadOriginalBtn').onclick = () => { if (currentFull) window.location.href = currentFull; };
    document.getElementById('downloadSocialBtn').onclick = () => { if (currentSocial) window.location.href = currentSocial; };
</script>
</body>
</html>