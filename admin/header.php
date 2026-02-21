<?php
// admin/header.php
require_once 'auth.php'; // Using require_once to prevent multiple inclusions
requireLogin();

// Get unread messages count
require_once '../config/database.php';
require_once '../models/Message.php';

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);
$unreadCount = $message->getUnreadCount();

// IMPORTANT: No output before this point - no HTML, no echo, no whitespace
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Framer Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #000;
            color: white;
        }
        .sidebar .nav-link {
            color: #ccc;
            border-radius: 0;
            padding: 12px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: #333;
        }
        .sidebar .nav-link.active {
            color: white;
            background: #444;
            border-left: 4px solid white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            padding: 20px;
            background: #f8f9fa;
            min-height: calc(100vh - 56px);
        }
        .stat-card {
            border: none;
            border-radius: 0;
            box-shadow: 5px 5px 0 rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translate(-3px, -3px);
            box-shadow: 8px 8px 0 rgba(0,0,0,0.15);
        }
        .badge-unread {
            background: #dc3545;
            color: white;
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../logo.png" alt="Framer" style="height: 40px; width: auto; filter: brightness(0) invert(1);" onerror="this.style.display='none'">
                <span class="ms-2">Admin Panel</span>
            </a>
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getCurrentUsername()); ?>
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge-unread"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 sidebar">
                <div class="nav flex-column">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'package') !== false ? 'active' : ''; ?>" href="packages.php">
                        <i class="bi bi-box"></i> Packages
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'blog') !== false ? 'active' : ''; ?>" href="blog.php">
                        <i class="bi bi-pencil-square"></i> Blog Posts
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'gallery') !== false ? 'active' : ''; ?>" href="gallery.php">
                        <i class="bi bi-images"></i> Gallery
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'messages') !== false ? 'active' : ''; ?>" href="messages.php">
                        <i class="bi bi-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge-unread"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>" href="users.php">
                        <i class="bi bi-people"></i> Users
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>" href="settings.php">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'activity') !== false ? 'active' : ''; ?>" href="activity.php">
                        <i class="bi bi-clock-history"></i> Activity Log
                    </a>
                </div>
            </div>
            <div class="col-md-10 main-content">