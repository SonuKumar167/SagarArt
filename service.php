<?php
require 'includes/config.php';

$siteSettings = getSiteSettings($conn);
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$service = getServiceBySlug($conn, $slug);
if (!$service) {
    header('Location: services.php');
    exit;
}
$pageTitle = !empty($siteSettings['meta_title']) ? $siteSettings['meta_title'] : (($service['title'] ?? 'Service') . ' - ' . ($siteSettings['site_name'] ?? 'Sagar Art'));
$pageDescription = !empty($siteSettings['meta_description']) ? $siteSettings['meta_description'] : substr(strip_tags($service['content'] ?? ''), 0, 160);
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
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <section class="page-hero text-white d-flex align-items-center">
    <div class="container">
      <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($service['title']); ?></h1>
      <p class="lead"><?php echo htmlspecialchars($service['summary']); ?></p>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <p><?php echo nl2br(htmlspecialchars($service['content'])); ?></p>
          <a href="services.php" class="btn btn-outline-primary mt-3">Back to Services</a>
        </div>
        <?php if (!empty($service['image_url'])): ?>
          <div class="col-lg-5">
            <img src="<?php echo htmlspecialchars($service['image_url']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="img-fluid rounded shadow">
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
