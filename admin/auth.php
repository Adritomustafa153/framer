<?php
// admin/auth.php
// Prevent multiple inclusions
if (!defined('AUTH_PHP')) {
    define('AUTH_PHP', true);

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Redirect if not logged in
    function requireLogin() {
        if (!isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }

    // Check if user has admin role
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Require admin role
    function requireAdmin() {
        requireLogin();
        if (!isAdmin()) {
            header("Location: dashboard.php?error=unauthorized");
            exit();
        }
    }

    // Get current user ID
    function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Get current username
    function getCurrentUsername() {
        return $_SESSION['username'] ?? 'Admin';
    }

    // Logout
    function logout() {
        $_SESSION = array();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
// NO closing PHP tag