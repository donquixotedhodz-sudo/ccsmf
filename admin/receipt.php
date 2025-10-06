<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/schema.php';

// Ensure columns used by this page exist
ensureSmfStatusColumn($pdo);
ensureSmfProgramColumn($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo '<!DOCTYPE html><html><body><p>Invalid receipt request.</p></body></html>';
  exit;
}

$stmt = $pdo->prepare('SELECT id, user_id, student_identifier, program, amount, photo_path, created_at, IFNULL(status, "pending") AS status FROM smf_transactions WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$txn = $stmt->fetch();

if (!$txn) {
  http_response_code(404);
  echo '<!DOCTYPE html><html><body><p>Receipt not found.</p></body></html>';
  exit;
}

// Only allow the owner of the transaction to view the receipt
if ((int)$txn['user_id'] !== (int)$currentUser['id']) {
  http_response_code(403);
  echo '<!DOCTYPE html><html><body><p>Not authorized to view this receipt.</p></body></html>';
  exit;
}

$status = strtolower($txn['status'] ?? 'pending');
if ($status !== 'approved') {
  http_response_code(409);
  echo '<!DOCTYPE html><html><body><p>This receipt is only available once approved.</p></body></html>';
  exit;
}

// Render a print-friendly HTML that the browser can save as PDF
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Acknowledgement Receipt #<?= (int)$txn['id'] ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      .no-print { display: none !important; }
      html, body { background: #fff; }
    }
  </style>
  <link rel="icon" href="<?= htmlspecialchars(url_for('/image/image.png')) ?>" type="image/png" />
  <meta name="robots" content="noindex" />
</head>
<body class="bg-slate-50">
  <div class="max-w-2xl mx-auto my-8 bg-white rounded-lg border shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b no-print">
      <div class="flex items-center gap-2">
        <img src="<?= htmlspecialchars(url_for('/image/image.png')) ?>" alt="Clarendon College Logo" class="h-8 w-8 object-contain" />
        <h1 class="text-lg font-semibold">Acknowledgement Receipt</h1>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="window.print()" class="inline-flex items-center gap-1 rounded bg-emerald-600 text-white px-3 py-2 text-sm hover:bg-emerald-700" title="Download PDF">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
            <path d="M14 2v6h6" />
          </svg>
          <span>Save as PDF</span>
        </button>
        <a href="<?= htmlspecialchars(url_for('/student/transactions.php')) ?>" class="text-sm text-emerald-700 hover:underline">Back to Transactions</a>
      </div>
    </div>

    <div class="px-6 py-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <p class="text-xs text-gray-500">Receipt No.</p>
          <p class="text-base font-semibold">#<?= (int)$txn['id'] ?></p>
        </div>
        <div class="text-right">
          <p class="text-xs text-gray-500">Issued On</p>
          <p class="text-base font-semibold"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($txn['created_at']))) ?></p>
        </div>
      </div>

      <div class="mt-4 flex items-start justify-between gap-6">
        <div class="space-y-4 flex-1">
          <div>
            <p class="text-xs text-gray-500">Student Name</p>
            <p class="font-medium"><?= htmlspecialchars($currentUser['name']) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Student Number</p>
            <p class="font-medium"><?= htmlspecialchars($txn['student_identifier']) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Status</p>
            <span class="inline-block rounded border px-2 py-1 text-xs bg-emerald-50 text-emerald-700 border-emerald-200">Approved</span>
          </div>
        </div>

        <div class="shrink-0 text-right">
          <p class="text-xs text-gray-500 mb-2">Verification QR</p>
          <div id="qr-code" class="inline-block border rounded p-2"></div>
        </div>
      </div>
    </div>

    <div class="px-6 py-4 border-t">
      <p class="text-xs text-gray-500">This acknowledgement receipt confirms your approved SMF submission with CCSSC.</p>
    </div>
  </div>
  <script src="../assets/js/qrcode.min.js"></script>
  <script>
    (function() {
      var el = document.getElementById('qr-code');
      if (!el) return;
      if (typeof QRCode === 'undefined') {
        el.innerHTML = '<span class="text-xs text-red-600">QR unavailable — check internet</span>';
        return;
      }
      var text = <?= json_encode(
        'Paid and approved by CCSSC — Receipt #' . (int)$txn['id'] .
        ' — Student: ' . ($currentUser['name'] ?? '') .
        ' — Student No: ' . ($txn['student_identifier'] ?? '') .
        ' — Status: Approved'
      ) ?>;
      new QRCode(el, { text: text, width: 128, height: 128, correctLevel: QRCode.CorrectLevel.M });
    })();
  </script>
</body>
</html>