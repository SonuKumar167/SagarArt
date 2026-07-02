<?php
require 'includes/config.php';

$page = getPageContent($conn, 'services');
$services = getServices($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services - Sagar Art</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <section class="page-hero text-white d-flex align-items-center">
    <div class="container">
      <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($page['hero_title']); ?></h1>
      <p class="lead"><?php echo htmlspecialchars($page['hero_text']); ?></p>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <p class="mb-4"><?php echo nl2br(htmlspecialchars($page['content'])); ?></p>
      <div class="row g-4">
        <?php foreach ($services as $service): ?>
          <div class="col-md-6">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <h4 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h4>
                <p class="card-text"><?php echo htmlspecialchars($service['summary']); ?></p>
                <a href="service.php?slug=<?php echo urlencode($service['slug']); ?>" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
