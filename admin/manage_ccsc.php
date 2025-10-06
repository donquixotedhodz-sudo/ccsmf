<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../config/db.php';

// Only Admins can manage CCSC users
if (strtolower($currentUser['role']) !== 'admin') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><body><p>Forbidden: Admins only.</p></body></html>';
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Flash helpers
$flash = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if ($type === 'success') { $_SESSION['flash_success'] = $message; }
    else { $_SESSION['error'] = $message; }
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $email = trim(strtolower($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            if ($name === '' || $email === '' || $password === '') {
                throw new InvalidArgumentException('All fields are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email address.');
            }
            if (strlen($password) < 8) {
                throw new InvalidArgumentException('Password must be at least 8 characters.');
            }
            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $check->execute([':email' => $email]);
            if ($check->fetch()) {
                throw new RuntimeException('An account with this email already exists.');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (:name, :email, :role, :hash)');
            $ins->execute([':name' => $name, ':email' => $email, ':role' => 'ccsc', ':hash' => $hash]);
            set_flash('success', 'CCSC user created successfully.');
            header('Location: ' . url_for('/admin/manage_ccsc.php'));
            exit;
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim(strtolower($_POST['email'] ?? ''));
            if ($id <= 0 || $name === '' || $email === '') {
                throw new InvalidArgumentException('Invalid update request.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email address.');
            }
            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
            $check->execute([':email' => $email, ':id' => $id]);
            if ($check->fetch()) {
                throw new RuntimeException('Another account already uses this email.');
            }
            $upd = $pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id AND role = "ccsc"');
            $upd->execute([':name' => $name, ':email' => $email, ':id' => $id]);
            set_flash('success', 'CCSC user updated successfully.');
            header('Location: ' . url_for('/admin/manage_ccsc.php'));
            exit;
        } elseif ($action === 'password') {
            $id = (int)($_POST['id'] ?? 0);
            $password = (string)($_POST['password'] ?? '');
            if ($id <= 0 || $password === '') {
                throw new InvalidArgumentException('Invalid password reset request.');
            }
            if (strlen($password) < 8) {
                throw new InvalidArgumentException('Password must be at least 8 characters.');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id AND role = "ccsc"');
            $upd->execute([':hash' => $hash, ':id' => $id]);
            set_flash('success', 'Password updated successfully.');
            header('Location: ' . url_for('/admin/manage_ccsc.php'));
            exit;
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { throw new InvalidArgumentException('Invalid delete request.'); }
            // Prevent deleting self if admin also has CCSC role (unlikely but safe)
            if ((int)$currentUser['id'] === $id) {
                throw new RuntimeException('You cannot delete your own account.');
            }
            $del = $pdo->prepare('DELETE FROM users WHERE id = :id AND role = "ccsc"');
            $del->execute([':id' => $id]);
            set_flash('success', 'CCSC user deleted.');
            header('Location: ' . url_for('/admin/manage_ccsc.php'));
            exit;
        }
    } catch (Throwable $e) {
        set_flash('error', $e->getMessage());
        header('Location: ' . url_for('/admin/manage_ccsc.php'));
        exit;
    }
}

// Fetch CCSC users
$stmt = $pdo->query('SELECT id, name, email, created_at FROM users WHERE role = "ccsc" ORDER BY created_at DESC');
$ccscUsers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage CCSC Users</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 overflow-y-scroll">
  <header class="bg-blue-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="text-lg font-semibold">Admin — Manage CCSC Users</h1>
      <div class="text-sm">Signed in as <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['email']) ?>)</div>
    </div>
  </header>
  <main class="max-w-6xl mx-auto p-6 min-h-[80vh]">
    <div class="grid grid-cols-12 gap-6">
      <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

      <section class="col-span-12 md:col-span-9">
        <div class="rounded-lg border bg-white p-6 shadow-sm min-h-[70vh]">
          <h2 class="text-base font-semibold mb-3">Manage CCSC Users</h2>
          <p class="text-sm text-gray-600 mb-4">Create, update, reset passwords, and remove CCSC accounts.</p>
          <?php if ($flash): ?>
            <div class="mb-3 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2 text-sm"><?= htmlspecialchars($flash) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="mb-3 rounded bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="rounded border p-4">
              <h3 class="text-sm font-semibold mb-2">Create CCSC User</h3>
              <form method="POST" action="<?= htmlspecialchars(url_for('/admin/manage_ccsc.php')) ?>" class="space-y-2 text-sm">
                <input type="hidden" name="action" value="create" />
                <div>
                  <label class="block mb-1">Name</label>
                  <input type="text" name="name" class="w-full rounded border px-3 py-2" required />
                </div>
                <div>
                  <label class="block mb-1">Email</label>
                  <input type="email" name="email" class="w-full rounded border px-3 py-2" required />
                </div>
                <div>
                  <label class="block mb-1">Password</label>
                  <input type="password" name="password" class="w-full rounded border px-3 py-2" minlength="8" required />
                </div>
                <div>
                  <button type="submit" class="rounded bg-blue-600 text-white px-3 py-2 hover:bg-blue-700">Create</button>
                </div>
              </form>
            </div>

            <div class="rounded border p-4">
              <h3 class="text-sm font-semibold mb-2">Notes</h3>
              <ul class="list-disc pl-5 text-xs text-gray-600 space-y-1">
                <li>Passwords must be at least 8 characters.</li>
                <li>Emails must be unique across all users.</li>
                <li>Only Admins can access this page.</li>
              </ul>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="px-3 py-2">Name</th>
                  <th class="px-3 py-2">Email</th>
                  <th class="px-3 py-2">Created</th>
                  <th class="px-3 py-2">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$ccscUsers): ?>
                  <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">No CCSC users found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($ccscUsers as $u): ?>
                    <tr class="border-b">
                      <td class="px-3 py-2">
                        <form method="POST" action="<?= htmlspecialchars(url_for('/admin/manage_ccsc.php')) ?>" class="flex items-center gap-2">
                          <input type="hidden" name="action" value="update" />
                          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
                          <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" class="rounded border px-2 py-1 w-44" />
                          <button type="submit" class="rounded bg-slate-600 text-white px-2 py-1 text-xs hover:bg-slate-700">Save</button>
                        </form>
                      </td>
                      <td class="px-3 py-2">
                        <form method="POST" action="<?= htmlspecialchars(url_for('/admin/manage_ccsc.php')) ?>" class="flex items-center gap-2">
                          <input type="hidden" name="action" value="update" />
                          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
                          <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" class="rounded border px-2 py-1 w-60" />
                          <button type="submit" class="rounded bg-slate-600 text-white px-2 py-1 text-xs hover:bg-slate-700">Save</button>
                        </form>
                      </td>
                      <td class="px-3 py-2 text-gray-600"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($u['created_at']))) ?></td>
                      <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                          <form method="POST" action="<?= htmlspecialchars(url_for('/admin/manage_ccsc.php')) ?>" class="flex items-center gap-2">
                            <input type="hidden" name="action" value="password" />
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
                            <input type="password" name="password" placeholder="New password" class="rounded border px-2 py-1 w-44" minlength="8" required />
                            <button type="submit" class="rounded bg-blue-600 text-white px-2 py-1 text-xs hover:bg-blue-700">Update Password</button>
                          </form>
                          <form method="POST" action="<?= htmlspecialchars(url_for('/admin/manage_ccsc.php')) ?>" onsubmit="return confirm('Delete this CCSC user? This action cannot be undone.');">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
                            <button type="submit" class="rounded bg-red-600 text-white px-2 py-1 text-xs hover:bg-red-700">Delete</button>
                          </form>
                        </div>
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

  <script>
    // No extra JS needed beyond form submissions
  </script>
</body>
</html>