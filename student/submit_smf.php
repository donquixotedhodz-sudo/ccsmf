<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/SmfController.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/schema.php';

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Submit endpoint not found.']);
        exit;
    }
    header('Location: ' . url_for('/student/'));
    exit;
}

$name = $_POST['name'] ?? '';
$sid = $_POST['student_id'] ?? '';
$program = $_POST['program'] ?? '';
$amount = $_POST['amount'] ?? '';
$photo = $_FILES['photo'] ?? null;

try {
    // Ensure program column exists before insert
    ensureSmfProgramColumn($pdo);
    $tx = SmfController::createTransaction($pdo, (int)$currentUser['id'], $name, $sid, $program, $amount, $photo);
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'message' => 'SMF transaction submitted successfully.']);
        exit;
    }
    $_SESSION['flash_success'] = 'SMF transaction submitted successfully.';
    header('Location: ' . url_for('/student/'));
    exit;
} catch (Throwable $e) {
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
    $_SESSION['error'] = $e->getMessage();
    header('Location: ' . url_for('/student/'));
    exit;
}