<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/schema.php';

// Ensure the status and program columns exist for this page
ensureSmfStatusColumn($pdo);
ensureSmfProgramColumn($pdo);

// Fetch current user's transactions
$stmt = $pdo->prepare('SELECT id, student_identifier, program, amount, photo_path, created_at, IFNULL(status, "pending") AS status FROM smf_transactions WHERE user_id = :uid ORDER BY created_at DESC');
$stmt->execute([':uid' => (int)$currentUser['id']]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Transactions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="<?= htmlspecialchars(url_for('/image/image.png')) ?>" type="image/png" />
</head>
<body class="min-h-screen bg-slate-50">
  <header class="bg-emerald-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="<?= htmlspecialchars(url_for('/image/image.png')) ?>" alt="Clarendon College Logo" class="h-8 w-8 object-contain" />
        <h1 class="text-lg font-semibold">Student Dashboard</h1>
      </div>
      <div class="text-sm">Signed in as <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['email']) ?>)</div>
    </div>
  </header>
  <main class="max-w-6xl mx-auto p-6">
    <div class="grid grid-cols-12 gap-6">
      <!-- Sidebar Nav -->
      <?php include __DIR__ . '/../includes/student_nav.php'; ?>

      <!-- Content: Transactions List -->
      <section class="col-span-12 md:col-span-9">
        <div class="rounded-lg border bg-white p-6 shadow-sm">
          <h2 class="text-base font-semibold mb-3">Transactions</h2>
          <p class="text-sm text-gray-600 mb-4">See the status of your SMF submissions as reviewed by CCSC.</p>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="px-3 py-2">ID</th>
                  <th class="px-3 py-2">Student ID</th>
                  <th class="px-3 py-2">Program</th>
                  <th class="px-3 py-2">Amount</th>
                  <th class="px-3 py-2">Photo</th>
                  <th class="px-3 py-2">Submitted</th>
                  <th class="px-3 py-2">Status</th>
                  <th class="px-3 py-2">Action</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!$transactions): ?>
                <tr>
                  <td colspan="8" class="px-3 py-4 text-center text-gray-500">No transactions yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                <tr class="border-b">
                  <td class="px-3 py-2">#<?= (int)$t['id'] ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($t['student_identifier']) ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($t['program'] ?? '—') ?></td>
                  <td class="px-3 py-2">₱<?= number_format((float)$t['amount'], 2) ?></td>
                  <td class="px-3 py-2">
                    <?php if (!empty($t['photo_path'])): ?>
                      <a href="<?= htmlspecialchars(url_for('/' . ltrim($t['photo_path'], '/'))) ?>" target="_blank" class="text-emerald-700 hover:underline">View</a>
                    <?php else: ?>
                      <span class="text-gray-400">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-3 py-2 text-gray-600"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($t['created_at']))) ?></td>
                  <td class="px-3 py-2">
                    <?php
                      $status = strtolower($t['status']);
                      $map = [
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'under_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                        'updated' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                      ];
                      $cls = $map[$status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                    ?>
                    <span class="inline-block rounded border px-2 py-1 text-xs <?= $cls ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ', $status))) ?></span>
                  </td>
                  <td class="px-3 py-2">
                    <?php if (strtolower($t['status']) === 'approved'): ?>
                      <a href="<?= htmlspecialchars(url_for('/ccsc/receipt.php?id=' . (int)$t['id'])) ?>" target="_blank" class="inline-flex items-center gap-1 rounded bg-emerald-600 text-white px-3 py-1 text-xs hover:bg-emerald-700" aria-label="PDF Receipt" title="PDF Receipt">
                        <!-- PDF-style document icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true">
                          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
                          <path d="M14 2v6h6" />
                          <path d="M7 14h10v5a1 1 0 01-1 1H8a1 1 0 01-1-1v-5z" />
                        </svg>
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </main>
</body>
</html>