<?php
session_start();
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
require_once __DIR__ . '/includes/url.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Clarendon College Student Mutual Fund Payment System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
  <div class="w-full max-w-md bg-white shadow-lg rounded-lg p-6">
     <img src="<?= htmlspecialchars(url_for('/image/image.png')) ?>" alt="Clarendon College Logo" class="mx-auto mb-4 h-20 w-20 object-contain" />
    <h1 class="text-2xl font-semibold text-center mb-2">Student Mutual Fund Payment System</h1>
    <p class="text-center text-gray-600 mb-6">Transparency System for Student Mutual Funds</p>

    <?php if ($error): ?>
      <div class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="you@example.com" />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" id="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="••••••••" />
      </div>
      <button type="submit" class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Sign In</button>
    </form>

    <div class="mt-6 text-center text-sm">
      Don’t have an account?
      <a href="register.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign Up</a>
    </div>
  </div>
</body>
</html>