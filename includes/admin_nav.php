<?php
// Admin side navigation include
// Ensures url_for() is available
require_once __DIR__ . '/url.php';

$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

function admin_nav_item($href, $label, $active) {
    $base = 'block rounded px-3 py-2';
    $activeCls = ' flex items-center justify-between bg-slate-100 text-slate-800 font-medium';
    $inactiveCls = ' hover:bg-slate-100';
    $cls = $base . ($active ? $activeCls : $inactiveCls);
    echo '<li>';
    echo '<a href="' . htmlspecialchars($href) . '" class="' . $cls . '">';
    echo '<span>' . htmlspecialchars($label) . '</span>';
    echo '</a>';
    echo '</li>';
}
?>
<aside class="col-span-12 md:col-span-3 md:sticky md:top-6 self-start">
  <nav class="rounded-lg border bg-white shadow-sm">
    <div class="px-4 py-3 border-b">
      <h2 class="text-sm font-semibold">Admin Navigation</h2>
    </div>
    <ul class="p-2 space-y-1 text-sm">
      <?php
        admin_nav_item(url_for('/admin/'), 'Dashboard', $current === 'index.php');
        admin_nav_item(url_for('/admin/transactions.php'), 'Transactions', $current === 'transactions.php');
        admin_nav_item(url_for('/admin/manage_ccsc.php'), 'Manage CCSC', $current === 'manage_ccsc.php');
        admin_nav_item(url_for('/admin/manage_admin.php'), 'Manage Admin', $current === 'manage_admin.php');
      ?>
      <li>
        <a href="<?= htmlspecialchars(url_for('/logout.php')) ?>" class="block rounded px-3 py-2 text-red-600 hover:bg-red-50">Logout</a>
      </li>
    </ul>
  </nav>
  
</aside>