<?php
$siteSettings = getSiteSettings($conn);
$headerMenuItems = getHeaderMenuItems($conn);
?>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Sagar Art'); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php foreach ($headerMenuItems as $headerMenuItem): ?>
          <?php $children = getMenuChildren($conn, (int)$headerMenuItem['id']); ?>
          <?php if (!empty($children) && !empty($headerMenuItem['has_dropdown'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="<?php echo htmlspecialchars($headerMenuItem['link']); ?>" role="button" data-bs-toggle="dropdown"><?php echo htmlspecialchars($headerMenuItem['label']); ?></a>
              <ul class="dropdown-menu">
                <?php foreach ($children as $child): ?>
                  <li><a class="dropdown-item" href="<?php echo htmlspecialchars($child['link']); ?>"><?php echo htmlspecialchars($child['label']); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo htmlspecialchars($headerMenuItem['link']); ?>"><?php echo htmlspecialchars($headerMenuItem['label']); ?></a>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</nav>
<?php if (!empty($siteSettings['header_text']) || !empty($siteSettings['header_cta_text'])): ?>
  <div class="header-banner py-3">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <?php if (!empty($siteSettings['header_text'])): ?>
        <div class="header-banner-text text-muted"><?php echo htmlspecialchars($siteSettings['header_text']); ?></div>
      <?php endif; ?>
      <?php if (!empty($siteSettings['header_cta_text']) && !empty($siteSettings['header_cta_link'])): ?>
        <a href="<?php echo htmlspecialchars($siteSettings['header_cta_link']); ?>" class="btn btn-sm btn-primary px-4"><?php echo htmlspecialchars($siteSettings['header_cta_text']); ?></a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
