<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php'],
    ['label' => 'Pages', 'url' => 'page_form.php'],
    ['label' => 'Services', 'url' => 'service_form.php'],
    ['label' => 'Menus', 'url' => 'menu_form.php'],
    ['label' => 'Analytics', 'url' => 'analytics.php'],
    ['label' => 'Header & Footer', 'url' => 'settings_form.php'],
    ['label' => 'Messages', 'url' => 'contact_submissions.php'],
];
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <div>
      <h5 class="mb-1">Sagar Art</h5>
      <p class="mb-0 small text-muted">Admin Workspace</p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <?php foreach ($navItems as $item): ?>
      <?php
      $isActive = $currentPage === $item['url'] || ($item['url'] === 'page_form.php' && in_array($currentPage, ['page_form.php', 'page_section_form.php'], true));
      ?>
      <a class="sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($item['url']); ?>">
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php" class="btn btn-outline-light btn-sm w-100">Logout</a>
  </div>
</aside>
