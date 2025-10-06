<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/schema.php';

// Ensure the status and program columns exist for metrics
ensureSmfStatusColumn($pdo);
ensureSmfProgramColumn($pdo);

// Compute approved amount collected across all transactions
$stmt = $pdo->query('SELECT COALESCE(SUM(amount), 0) AS total_amount, COUNT(*) AS total_count FROM smf_transactions WHERE status = "approved"');
$metrics = $stmt->fetch() ?: ['total_amount' => 0, 'total_count' => 0];
$totalAmount = (float)($metrics['total_amount'] ?? 0);
$totalCount = (int)($metrics['total_count'] ?? 0);

// Compute the current user's total approved amount (your total paid)
$ustmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) AS user_total FROM smf_transactions WHERE user_id = :uid AND status = "approved"');
$ustmt->execute([':uid' => (int)$currentUser['id']]);
$userMetrics = $ustmt->fetch() ?: ['user_total' => 0];
$userTotalPaid = (float)($userMetrics['user_total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50">
  <header class="bg-emerald-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="text-lg font-semibold">Student Dashboard</h1>
      <div class="text-sm">Signed in as <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['email']) ?>)</div>
    </div>
  </header>
  <main class="max-w-6xl mx-auto p-6">
    <div class="grid grid-cols-12 gap-6">
      <!-- Sidebar Nav -->
      <?php include __DIR__ . '/../includes/student_nav.php'; ?>

      <!-- Content: Metrics -->
      <section class="col-span-12 md:col-span-9">
        <div class="rounded-lg border bg-white p-6 shadow-sm">
          <h2 class="text-base font-semibold mb-3">Overview</h2>
          <p class="text-sm text-gray-600 mb-4">Approved amount collected across all transactions.</p>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">Approved Amount (CCSSC)</div>
              <div class="text-2xl font-semibold">₱<?= number_format($totalAmount, 2) ?></div>
            </div>
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">Your Total Paid</div>
              <div class="text-2xl font-semibold">₱<?= number_format($userTotalPaid, 2) ?></div>
            </div>
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">View Details</div>
              <a href="<?= htmlspecialchars(url_for('/student/transactions.php')) ?>" class="inline-block mt-1 rounded bg-emerald-600 text-white px-3 py-1 text-sm hover:bg-emerald-700">Go to Transactions</a>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
</body>
</html>