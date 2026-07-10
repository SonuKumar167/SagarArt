<?php
require 'includes/config.php';

$page = [
    'title' => 'Reviews',
    'content' => 'Client reviews and testimonials for Sagar Art.'
];
$siteSettings = getSiteSettings($conn);
$pageTitle = !empty($siteSettings['meta_title']) ? $siteSettings['meta_title'] : (($page['title'] ?? 'Reviews') . ' - ' . ($siteSettings['site_name'] ?? 'Sagar Art'));
$pageDescription = !empty($siteSettings['meta_description']) ? $siteSettings['meta_description'] : substr(strip_tags($page['content'] ?? 'Client reviews and testimonials for Sagar Art.'), 0, 160);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <?php if (!empty($siteSettings['favicon_url'])): ?><link rel="icon" href="<?php echo htmlspecialchars($siteSettings['favicon_url']); ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://static.elfsight.com/platform/platform.js" async></script>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <section class="py-5 bg-light text-white">
    <div class="container">
        <div class="elfsight-app-f0f35b72-3b20-4ce4-b864-f41012e33e5b" data-elfsight-app-lazy></div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
