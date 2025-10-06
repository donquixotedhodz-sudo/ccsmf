<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/RoleController.php';
require_once __DIR__ . '/includes/url.php';

// Only accept POST for login; otherwise redirect to home
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_for('/index.php'));
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: ' . url_for('/index.php'));
    exit;
}

try {
    $user = AuthController::attemptLogin($pdo, $email, $password);
    if (!$user) {
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: ' . url_for('/index.php'));
        exit;
    }
    $role = $user['role'] ?? '';
    if ($role === '') {
        // Safety: if no role, log out and return to login
        $_SESSION['error'] = 'Account is missing a role.';
        header('Location: ' . url_for('/logout.php'));
        exit;
    }
    // Redirect based on role
    RoleController::redirectToRoleDashboard($role);
} catch (Throwable $e) {
    $_SESSION['error'] = 'Login failed: ' . $e->getMessage();
    header('Location: ' . url_for('/index.php'));
    exit;
}