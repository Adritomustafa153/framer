<?php
// admin/client-gallery-share-ajax.php
require_once '../config/database.php';
require_once '../models/ClientGallery.php';
require_once '../models/ClientGalleryImage.php';
require_once '../includes/mail.php';

header('Content-Type: application/json');

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$email = trim($_POST['email'] ?? '');
$personalMessage = trim($_POST['message'] ?? '');

if (!$id || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing gallery ID or email']);
    exit;
}

$db = (new Database())->getConnection();
$galleryModel = new ClientGallery($db);
$gallery = $galleryModel->getById($id);
if (!$gallery) {
    echo json_encode(['success' => false, 'message' => 'Gallery not found']);
    exit;
}

// Get cover image data for embedding
$imageModel = new ClientGalleryImage($db);
$coverImage = $imageModel->getCover($id);
$coverBase64 = '';
if ($coverImage && !empty($coverImage['file_path'])) {
    $localPath = __DIR__ . '/../' . $coverImage['file_path'];
    if (file_exists($localPath)) {
        $imageData = file_get_contents($localPath);
        $base64 = base64_encode($imageData);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $localPath);
        finfo_close($finfo);
        $coverBase64 = 'data:' . $mime . ';base64,' . $base64;
    }
}

// Get logo
$logoBase64 = '';
if (!empty($gallery['logo_path'])) {
    $logoPath = __DIR__ . '/../' . $gallery['logo_path'];
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
}

// Build gallery URL (absolute)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . '/framer/'; // adjust folder name if needed
$galleryUrl = $baseUrl . 'client-gallery.php?code=' . $gallery['gallery_code'];

$mailer = new Mailer($db);
$result = $mailer->sendGalleryEmail($email, $gallery, $galleryUrl, $personalMessage, $coverBase64, $logoBase64);

echo json_encode($result);
?>