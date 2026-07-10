<?php
$siteSettings = getSiteSettings($conn);
$headerMenuItems = getHeaderMenuItems($conn);
?>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/">
      <?php if (!empty($siteSettings['logo_url'])): ?>
        <img src="<?php echo htmlspecialchars($siteSettings['logo_url']); ?>" alt="<?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Sagar Art'); ?>" style="height: 40px; width: auto;">
      <?php endif; ?>
      <span><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Sagar Art'); ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php foreach ($headerMenuItems as $headerMenuItem): ?>
          <?php $children = getMenuChildren($conn, (int)$headerMenuItem['id']); ?>
          <?php if (!empty($children) && !empty($headerMenuItem['has_dropdown'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="<?php echo htmlspecialchars(normalizeRoute($headerMenuItem['link'] ?? '#')); ?>" role="button" data-bs-toggle="dropdown"><?php echo htmlspecialchars($headerMenuItem['label']); ?></a>
              <ul class="dropdown-menu">
                <?php foreach ($children as $child): ?>
                  <li><a class="dropdown-item" href="<?php echo htmlspecialchars(normalizeRoute($child['link'] ?? '#')); ?>"><?php echo htmlspecialchars($child['label']); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo htmlspecialchars(normalizeRoute($headerMenuItem['link'] ?? '#')); ?>"><?php echo htmlspecialchars($headerMenuItem['label']); ?></a>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.getElementById('navbarNav');
  const toggler = document.querySelector('.navbar-toggler');

  if (!navbar || !toggler || typeof window.bootstrap === 'undefined') {
    return;
  }

  const collapse = bootstrap.Collapse.getOrCreateInstance(navbar, { toggle: false });

  const closeNavbar = function () {
    if (navbar.classList.contains('show')) {
      collapse.hide();
    }
  };

  const isMobileMenu = function () {
    return window.innerWidth < 992;
  };

  const shouldCloseOnLinkClick = function (link) {
    if (!isMobileMenu()) {
      return false;
    }

    return !link.closest('.dropdown-toggle') && !link.closest('.dropdown-menu');
  };

  toggler.addEventListener('click', function () {
    if (isMobileMenu()) {
      if (navbar.classList.contains('show')) {
        collapse.hide();
      } else {
        collapse.show();
      }
    }
  });

  navbar.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (shouldCloseOnLinkClick(link)) {
        closeNavbar();
      }
    });
  });

  document.addEventListener('click', function (event) {
    if (isMobileMenu() && navbar.classList.contains('show') && !navbar.contains(event.target) && !toggler.contains(event.target)) {
      closeNavbar();
    }
  });
});
</script>
<?php if (!empty($siteSettings['header_text']) || !empty($siteSettings['header_cta_text'])): ?>
  <div class="header-banner py-3">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <?php if (!empty($siteSettings['header_text'])): ?>
        <div class="header-banner-text text-muted"><?php echo htmlspecialchars($siteSettings['header_text']); ?></div>
      <?php endif; ?>
      <?php if (!empty($siteSettings['header_cta_text']) && !empty($siteSettings['header_cta_link'])): ?>
        <a href="<?php echo htmlspecialchars(normalizeRoute($siteSettings['header_cta_link'] ?? '/contact')); ?>" class="btn btn-sm btn-primary px-4"><?php echo htmlspecialchars($siteSettings['header_cta_text']); ?></a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
