<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/RegistrationController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/RoleController.php';
require_once __DIR__ . '/includes/url.php';

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: ' . url_for('/register.php'));
        exit;
    }

    try {
        $user = RegistrationController::createStudent($pdo, $name, $email, $password);
        // Auto-login newly registered student
        AuthController::attemptLogin($pdo, $user['email'], $password);
        RoleController::redirectToRoleDashboard('student');
    } catch (Throwable $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ' . url_for('/register.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Sign Up</title>
  <link rel="icon" href="<?= htmlspecialchars(url_for('/image/image.png')) ?>" type="image/png" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
  <div class="w-full max-w-md bg-white shadow-lg rounded-lg p-6">
     <img src="<?= htmlspecialchars(url_for('/image/image.png')) ?>" alt="Clarendon College Logo" class="mx-auto mb-4 h-20 w-20 object-contain" />
    <h1 class="text-2xl font-semibold text-center mb-2">Create Student Account</h1>
    <p class="text-center text-gray-600 mb-6">Register to access your student dashboard</p>

    <?php if ($error): ?>
      <div class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars(url_for('/register.php')) ?>" method="POST" class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
        <input type="text" id="name" name="name" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Jane Doe" />
      </div>
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="student@example.com" />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" id="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="At least 8 characters" />
      </div>
      <div>
        <label for="confirm" class="block text-sm font-medium text-gray-700">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Re-enter password" />
      </div>
      <button type="submit" class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Sign Up</button>
    </form>

    <div class="mt-6 text-center text-sm">
      Already have an account?
      <a href="<?= htmlspecialchars(url_for('/index.php')) ?>" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign In</a>
    </div>
  </div>
</body>
</html>