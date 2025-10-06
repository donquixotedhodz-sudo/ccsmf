<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/schema.php';

$flash = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// Ensure status and program columns exist for review UI
ensureSmfStatusColumn($pdo);
ensureSmfProgramColumn($pdo);

// Fetch all transactions for review
$stmt = $pdo->query('SELECT id, user_id, student_name, student_identifier, program, amount, photo_path, created_at, IFNULL(status, "pending") AS status FROM smf_transactions ORDER BY created_at DESC');
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CCSC Transactions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="<?= htmlspecialchars(url_for('/image/image.png')) ?>" type="image/png" />
</head>
<body class="min-h-screen bg-slate-50 overflow-y-scroll">
  <header class="bg-blue-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="<?= htmlspecialchars(url_for('/image/image.png')) ?>" alt="Clarendon College Logo" class="h-8 w-8 object-contain" />
        <h1 class="text-lg font-semibold">CCSC Dashboard</h1>
      </div>
      <div class="text-sm">Signed in as <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['email']) ?>)</div>
    </div>
  </header>
  <main class="max-w-6xl mx-auto p-6 min-h-[80vh]">
    <div class="grid grid-cols-12 gap-6">
      <!-- Sidebar Nav -->
      <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

      <!-- Transactions Content -->
      <section class="col-span-12 md:col-span-9">
        <div class="rounded-lg border bg-white p-6 shadow-sm min-h-[70vh]">
          <h2 class="text-base font-semibold mb-3">Transactions Review</h2>
          <p class="text-sm text-gray-600 mb-4">Review student SMF submissions and update their status.</p>
          <?php if ($flash): ?>
            <div class="mb-3 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2 text-sm"><?= htmlspecialchars($flash) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="mb-3 rounded bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="px-3 py-2">Student</th>
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
                    <td colspan="8" class="px-3 py-4 text-center text-gray-500">No transactions to review.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($transactions as $t): ?>
                  <tr class="border-b">
                    <td class="px-3 py-2"><?= htmlspecialchars($t['student_name']) ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($t['student_identifier']) ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($t['program'] ?? '—') ?></td>
                    <td class="px-3 py-2">₱<?= number_format((float)$t['amount'], 2) ?></td>
                    <td class="px-3 py-2">
                      <?php if (!empty($t['photo_path'])): ?>
                        <button type="button" data-photo-src="<?= htmlspecialchars(url_for('/' . ltrim($t['photo_path'], '/'))) ?>" class="text-blue-700 hover:underline">View</button>
                      <?php else: ?>
                        <span class="text-gray-400">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 text-gray-600"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($t['created_at']))) ?></td>
                    <td class="px-3 py-2">
                      <span class="inline-block rounded border px-2 py-1 text-xs">
                        <?= htmlspecialchars(ucwords(str_replace('_',' ', strtolower($t['status'])))) ?>
                      </span>
                    </td>
                    <td class="px-3 py-2">
                      <form method="POST" action="<?= htmlspecialchars(url_for('/ccsc/update_status.php')) ?>" class="flex items-center gap-2">
                        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>" />
                        <select name="status" class="rounded border px-2 py-1 text-xs">
                          <?php foreach (['pending','under_review','approved','rejected','updated'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= strtolower($t['status']) === $opt ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_',' ', $opt))) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button type="submit" class="rounded bg-blue-600 text-white px-3 py-1 text-xs hover:bg-blue-700">Save</button>
                      </form>
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

  <!-- Photo Modal -->
  <div id="photo-modal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/60">
    <div class="bg-white rounded shadow-lg max-w-xl w-[80%] md:w-[60%]">
      <div class="p-2 border-b flex justify-end">
        <button id="photo-close" class="text-sm rounded px-3 py-1 bg-gray-100 hover:bg-gray-200">Close</button>
      </div>
      <div class="p-2">
        <img id="photo-img" src="" alt="Transaction Photo" class="max-h-[60vh] w-auto mx-auto object-contain" />
      </div>
    </div>
  </div>

  <script>
    (function() {
      // Photo modal
      const modal = document.getElementById('photo-modal');
      const img = document.getElementById('photo-img');
      const closeBtn = document.getElementById('photo-close');
      document.addEventListener('click', function(e){
        const btn = e.target.closest('[data-photo-src]');
        if (btn) {
          const src = btn.getAttribute('data-photo-src');
          if (src) { img.src = src; modal.classList.remove('hidden'); }
        }
      });
      closeBtn.addEventListener('click', function(){ modal.classList.add('hidden'); img.src = ''; });
      modal.addEventListener('click', function(e){ if (e.target === modal) { modal.classList.add('hidden'); img.src = ''; } });
    })();
  </script>
</body>
</html>