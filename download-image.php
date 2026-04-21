<?php
// download-image.php
require_once 'config/database.php';
require_once 'models/Gallery.php';

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $gallery = new Gallery($db);
    
    $id = (int)$_GET['id'];
    $result = $gallery->incrementDownload($id);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
} else {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['success' => false, 'error' => 'No image ID provided']);
}
?>