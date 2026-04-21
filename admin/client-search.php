<?php
// admin/client-search.php
require_once '../config/database.php';
require_once '../models/Client.php';

$database = new Database();
$db = $database->getConnection();
$clientModel = new Client($db);

$keyword = $_GET['q'] ?? '';
$clients = $clientModel->search($keyword);

header('Content-Type: application/json');
echo json_encode($clients);
?>