<?php
$siteSettings = getSiteSettings($conn);
$footerLinks = getFooterMenuItems($conn);
?>
<footer class="site-footer">
  <div class="footer-top">
    <div class="container">
      <div class="row align-items-center gap-4">
        <div class="col-lg-8">
          <h2 class="text-white mb-3"><?php echo htmlspecialchars($siteSettings['footer_cta_heading'] ?? 'Build memorable digital experiences with a modern agency feel.'); ?></h2>
          <p class="text-white-75"><?php echo htmlspecialchars($siteSettings['footer_cta_text'] ?? 'Create more persuasive pages, polished service showcases, and faster contact flows with a website designed for conversions.'); ?></p>
        </div>
        <div class="col-lg-auto">
          <a href="<?php echo htmlspecialchars(normalizeRoute($siteSettings['footer_cta_button_link'] ?? '/contact')); ?>" class="btn btn-primary"><?php echo htmlspecialchars($siteSettings['footer_cta_button_text'] ?? 'Get in touch'); ?></a>
        </div>
      </div>
    </div>
  </div>
  <div class="container py-5">
    <div class="row footer-grid">
      <div class="col-md-4 footer-widget">
        <h5><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Sagar Art'); ?></h5>
        <p><?php echo htmlspecialchars($siteSettings['footer_about'] ?? 'We help digital brands grow with premium websites, app experiences, and creative marketing solutions.'); ?></p>
      </div>
      <div class="col-md-4 footer-widget">
        <h5>Quick Links</h5>
        <ul class="footer-links">
          <?php if (!empty($footerLinks)): ?>
            <?php foreach ($footerLinks as $link): ?>
              <li><a href="<?php echo htmlspecialchars(normalizeRoute($link['link'] ?? '#')); ?>"><?php echo htmlspecialchars($link['label']); ?></a></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No quick links have been configured yet.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-md-4 footer-widget">
        <h5>Contact</h5>
        <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($siteSettings['footer_email'] ?? ''); ?></p>
        <p class="mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($siteSettings['footer_phone'] ?? ''); ?></p>
        <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($siteSettings['footer_address'] ?? ''); ?></p>
        <?php $socialLinks = [
          ['label' => 'Facebook', 'url' => $siteSettings['facebook_url'] ?? ''],
          ['label' => 'Instagram', 'url' => $siteSettings['instagram_url'] ?? ''],
          ['label' => 'Twitter', 'url' => $siteSettings['twitter_url'] ?? ''],
          ['label' => 'YouTube', 'url' => $siteSettings['youtube_url'] ?? '']
        ]; ?>
        <?php $activeSocialLinks = array_filter($socialLinks, static function ($link) { return !empty($link['url']); }); ?>
        <?php if (!empty($activeSocialLinks)): ?>
          <div class="mt-3">
            <h6 class="text-white">Follow us</h6>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($activeSocialLinks as $socialLink): ?>
                <a href="<?php echo htmlspecialchars($socialLink['url']); ?>" target="_blank" rel="noreferrer" class="btn btn-sm btn-outline-light"><?php echo htmlspecialchars($socialLink['label']); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <p class="mb-0"><?php echo htmlspecialchars($siteSettings['footer_copyright'] ?? '© 2026 Sagar Art. All rights reserved.'); ?></p>
    </div>
  </div>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(function () {
    $('.navbar').addClass('shadow-sm');
  });
</script>
