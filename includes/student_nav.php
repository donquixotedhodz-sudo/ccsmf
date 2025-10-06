<?php
// Assumes url_for() is available from includes/url.php loaded by parent page
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

function nav_item($href, $label, $active) {
    $base = 'block rounded px-3 py-2';
    $activeCls = ' flex items-center justify-between bg-emerald-50 text-emerald-700 font-medium';
    $inactiveCls = ' hover:bg-emerald-50';
    $cls = $base . ($active ? $activeCls : $inactiveCls);
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L13.586 11H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>';
    echo '<li>';
    echo '<a href="' . htmlspecialchars($href) . '" class="' . $cls . '">';
    echo '<span>' . htmlspecialchars($label) . '</span>';
    if ($active) echo $icon;
    echo '</a>';
    echo '</li>';
}
?>
<aside class="col-span-12 md:col-span-3 md:sticky md:top-6 self-start">
  <nav class="rounded-lg border bg-white shadow-sm">
    <div class="px-4 py-3 border-b">
      <h2 class="text-sm font-semibold">Navigation</h2>
    </div>
    <ul class="p-2 space-y-1 text-sm">
      <?php
        nav_item(url_for('/student/dashboard.php'), 'Dashboard', $current === 'dashboard.php');
        nav_item(url_for('/student/'), 'SMF Form', $current === 'index.php');
        nav_item(url_for('/student/transactions.php'), 'Transactions', $current === 'transactions.php');
      ?>
      <li>
        <a href="<?= htmlspecialchars(url_for('/logout.php')) ?>" class="block rounded px-3 py-2 text-red-600 hover:bg-red-50">Logout</a>
      </li>
    </ul>
  </nav>
</aside>