<?php
session_start();
require_once __DIR__ . '/controllers/AuthController.php';
AuthController::logout();
header('Location: index.php');
exit;