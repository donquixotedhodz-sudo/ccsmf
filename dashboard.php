<?php
session_start();
require_once __DIR__ . '/controllers/RoleController.php';
require_once __DIR__ . '/includes/url.php';
if (!isset($_SESSION['user'])) {
    header('Location: ' . url_for('/index.php'));
    exit;
}

$role = $_SESSION['user']['role'] ?? '';
if (!$role) {
    header('Location: ' . url_for('/logout.php'));
    exit;
}

RoleController::redirectToRoleDashboard($role);