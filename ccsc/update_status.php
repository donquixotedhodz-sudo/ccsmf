<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/schema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Not Found']);
    exit;
}

// Ensure status column exists before updating
ensureSmfStatusColumn($pdo);

// Only CCSC or Admin can update statuses
if (!in_array(strtolower($currentUser['role']), ['ccsc','admin'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';
$allowed = ['pending','under_review','approved','rejected','updated'];

if ($id <= 0 || !in_array($status, $allowed, true)) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ' . url_for('/ccsc/'));
    exit;
}

$stmt = $pdo->prepare('UPDATE smf_transactions SET status = :status WHERE id = :id');
$stmt->execute([':status' => $status, ':id' => $id]);

$_SESSION['flash_success'] = 'Status updated successfully.';
header('Location: ' . url_for('/ccsc/transactions.php'));
exit;