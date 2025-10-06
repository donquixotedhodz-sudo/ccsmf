<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/schema.php';
$flash = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// Fetch all transactions for review
// Ensure status and program columns exist for metrics
ensureSmfStatusColumn($pdo);
ensureSmfProgramColumn($pdo);
$stmt = $pdo->query('SELECT id, user_id, student_name, student_identifier, program, amount, photo_path, created_at, IFNULL(status, "pending") AS status FROM smf_transactions ORDER BY created_at DESC');
$transactions = $stmt->fetchAll();

// Compute simple metrics for dashboards
$counts = [
  'pending' => 0,
  'under_review' => 0,
  'approved' => 0,
  'rejected' => 0,
  'updated' => 0,
];
$approvedAmount = 0.0;
foreach ($transactions as $t) {
  $s = strtolower($t['status'] ?? 'pending');
  if (isset($counts[$s])) { $counts[$s]++; } else { $counts['pending']++; }
  if ($s === 'approved') {
    $approvedAmount += (float)$t['amount'];
  }
}
// Build approved counts per program for dashboard chart
$allowedPrograms = ['BSBA','BSIS','BMMA','BSA','BSTM','BSED','BEED','BCAED'];
$approvedProgramCounts = array_fill_keys($allowedPrograms, 0);
foreach ($transactions as $t) {
  $prog = strtoupper(trim($t['program'] ?? ''));
  $status = strtolower($t['status'] ?? 'pending');
  if ($status === 'approved' && isset($approvedProgramCounts[$prog])) {
    $approvedProgramCounts[$prog]++;
  }
}
$approvedProgramLabels = array_keys($approvedProgramCounts);
$approvedProgramData = array_values($approvedProgramCounts);

// Active users over time (unique users per day, last 14 days)
$days = [];
for ($i = 13; $i >= 0; $i--) {
  $d = date('Y-m-d', strtotime("-$i days"));
  $days[$d] = [];
}
foreach ($transactions as $t) {
  $d = date('Y-m-d', strtotime($t['created_at']));
  if (isset($days[$d])) {
    $uid = (int)$t['user_id'];
    $days[$d][$uid] = true;
  }
}
$activeUserLabels = array_keys($days);
$activeUserData = [];
foreach ($days as $set) { $activeUserData[] = count($set); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CCSC Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="icon" href="<?= htmlspecialchars(url_for('/image/image.png')) ?>" type="image/png" />
</head>
<body class="min-h-screen bg-slate-50 overflow-y-scroll">
  <header class="bg-blue-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="<?= htmlspecialchars(url_for('/image/image.png')) ?>" alt="Clarendon College Logo" class="h-8 w-8 object-contain" />
        <h1 class="text-lg font-semibold">Admin Dashboard</h1>
      </div>
      <div class="text-sm">Signed in as <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['email']) ?>)</div>
    </div>
  </header>
  <main class="max-w-6xl mx-auto p-6 min-h-[80vh]">
    <div class="grid grid-cols-12 gap-6">
      <!-- Sidebar Nav -->
      <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

      <!-- Dashboards Content -->
      <section id="dashboards-section" class="col-span-12 md:col-span-9">
        <div class="rounded-lg border bg-white p-6 shadow-sm min-h-[70vh]">
          <h2 class="text-base font-semibold mb-3">Dashboards</h2>
          <p class="text-sm text-gray-600 mb-4">Overview of SMF submissions and statuses.</p>

          <!-- Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">Total Transactions</div>
              <div class="text-2xl font-semibold"><?= count($transactions) ?></div>
            </div>
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">Approved Amount</div>
              <div class="text-2xl font-semibold">₱<?= number_format($approvedAmount, 2) ?></div>
            </div>
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">Pending</div>
              <div class="text-2xl font-semibold"><?= (int)$counts['pending'] ?></div>
            </div>
            <div class="rounded-lg border p-4">
              <div class="text-sm text-gray-500">Approved</div>
              <div class="text-2xl font-semibold"><?= (int)$counts['approved'] ?></div>
            </div>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-lg border p-4">
              <h3 class="text-sm font-semibold mb-2">Approved Submissions by Program</h3>
              <div class="h-60">
                <canvas id="statusChart" class="w-full h-full"></canvas>
              </div>
            </div>
            <div class="rounded-lg border p-4">
              <h3 class="text-sm font-semibold mb-2">Active Users Over Time</h3>
              <div class="h-60">
                <canvas id="approvalChart" class="w-full h-full"></canvas>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Transactions Content moved to ccsc/transactions.php -->
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
      // Sidebar toggle
      const navDash = document.getElementById('nav-dash');
      const navTrans = document.getElementById('nav-trans');
      const dash = document.getElementById('dashboards-section');
      const trans = document.getElementById('transactions-section');
      function activate(section) {
        if (section === 'dash') {
          dash.classList.remove('hidden');
          trans.classList.add('hidden');
          navDash.classList.add('bg-blue-50','text-blue-700','font-medium');
          navTrans.classList.remove('bg-blue-50','text-blue-700','font-medium');
        } else {
          trans.classList.remove('hidden');
          dash.classList.add('hidden');
          navTrans.classList.add('bg-blue-50','text-blue-700','font-medium');
          navDash.classList.remove('bg-blue-50','text-blue-700','font-medium');
        }
      }
      // Navigation now uses normal links; no interception

      // Charts
      const statusCtx = document.getElementById('statusChart');
      const approvalCtx = document.getElementById('approvalChart');
      const labels = <?= json_encode($approvedProgramLabels) ?>;
      const dataVals = <?= json_encode($approvedProgramData) ?>;
      if (statusCtx) {
        new Chart(statusCtx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{ label: 'Count', data: dataVals, backgroundColor: '#3b82f6' }]
          },
          options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
        });
      }
      if (approvalCtx) {
        const userLabels = <?= json_encode($activeUserLabels) ?>;
        const userCounts = <?= json_encode($activeUserData) ?>;
        new Chart(approvalCtx, {
          type: 'line',
          data: {
            labels: userLabels,
            datasets: [{
              label: 'Active Users',
              data: userCounts,
              borderColor: '#8b5cf6',
              backgroundColor: '#8b5cf6',
              tension: 0.3,
              fill: false
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: { beginAtZero: true, ticks: { precision: 0 } }
            }
          }
        });
      }

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