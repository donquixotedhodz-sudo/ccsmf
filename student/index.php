<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
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

      <!-- Content: SMF Form -->
      <section class="col-span-12 md:col-span-9">
        <div class="rounded-lg border bg-white p-6 shadow-sm">
          <h2 class="text-base font-semibold mb-3">SMF Form</h2>
          <p class="text-sm text-gray-600 mb-4">Submit your Student Mutual Fund payment details.</p>
          <div id="alerts"></div>

          <?php if ($success): ?>
            <div class="mb-4 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2 text-sm">
              <?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form id="smf-form" action="<?= htmlspecialchars(url_for('/student/submit_smf.php')) ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" name="name" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="Jane Doe" />
              </div>
              <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700">Student ID</label>
                <input type="text" id="student_id" name="student_id" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="e.g., 2025-00123" />
              </div>
              <div>
                <label for="program" class="block text-sm font-medium text-gray-700">Program</label>
                <select id="program" name="program" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                  <option value="" disabled selected>Select program</option>
                  <option value="BSBA">BSBA</option>
                  <option value="BSIS">BSIS</option>
                  <option value="BMMA">BMMA</option>
                  <option value="BSA">BSA</option>
                  <option value="BSTM">BSTM</option>
                  <option value="BSED">BSED</option>
                  <option value="BEED">BEED</option>
                  <option value="BCAED">BCAED</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                <input type="number" step="0.01" min="0" id="amount" name="amount" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="0.00" />
              </div>
              <div>
                <label for="photo" class="block text-sm font-medium text-gray-700">Upload Photo</label>
                <input type="file" id="photo" name="photo" accept="image/png, image/jpeg" class="mt-1 w-full text-sm" />
                <p class="mt-1 text-xs text-gray-500">Accepted: JPG or PNG.</p>
              </div>
            </div>
            <div>
              <button type="submit" class="inline-flex justify-center rounded-md bg-emerald-600 px-4 py-2 text-white font-medium hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600">Submit</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </main>
  <script>
    (function() {
      const form = document.getElementById('smf-form');
      if (!form) return;
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const original = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const data = new FormData(form);
        try {
          const resp = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: data,
          });
          const contentType = resp.headers.get('content-type') || '';
          if (resp.status === 404) {
            showError('Submit endpoint not found. Please try again or contact support.');
          } else if (contentType.includes('application/json')) {
            const json = await resp.json();
            if (json.ok) {
              showSuccess(json.message || 'Submitted successfully.');
              form.reset();
            } else {
              showError(json.error || 'Submission failed.');
            }
          } else if (resp.ok) {
            // Fallback for non-JSON success responses
            showSuccess('Submitted successfully.');
            form.reset();
          } else {
            showError('Submission failed. Please try again.');
          }
        } catch (err) {
          showError('Network error. Please check your connection.');
        } finally {
          submitBtn.disabled = false;
          submitBtn.textContent = original;
        }
      });

      function showError(msg) {
        const el = document.createElement('div');
        el.className = 'mb-4 rounded bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm';
        el.textContent = msg;
        insertAlert(el);
      }
      function showSuccess(msg) {
        const el = document.createElement('div');
        el.className = 'mb-4 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2 text-sm';
        el.textContent = msg;
        insertAlert(el);
      }
      function insertAlert(el) {
        const container = document.getElementById('alerts');
        if (container) {
          container.innerHTML = '';
          container.appendChild(el);
        }
      }
    })();
  </script>
</body>
</html>