<?php
// Simple auth guard. Include this at the top of protected pages.
require_once __DIR__ . '/url.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ' . url_for('/index.php'));
    exit;
}

$currentUser = $_SESSION['user'];