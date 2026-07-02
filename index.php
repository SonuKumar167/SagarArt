<?php
require 'includes/config.php';

$page = getPageContent($conn, 'home');
$services = getServices($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sagar Art</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <section class="hero-section text-white d-flex align-items-center">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7">
          <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($page['hero_title']); ?></h1>
          <p class="lead"><?php echo htmlspecialchars($page['hero_text']); ?></p>
          <a href="services.php" class="btn btn-light btn-lg">Explore Services</a>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <h2><?php echo htmlspecialchars($page['title']); ?></h2>
          <p><?php echo nl2br(htmlspecialchars($page['content'])); ?></p>
        </div>
        <div class="col-lg-6">
          <img src="<?php echo htmlspecialchars($page['image_url']); ?>" alt="Hero" class="img-fluid rounded shadow">
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4">Featured Services</h2>
      <div class="row g-4">
        <?php foreach (array_slice($services, 0, 4) as $service): ?>
          <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($service['summary']); ?></p>
                <a href="service.php?slug=<?php echo urlencode($service['slug']); ?>" class="btn btn-outline-primary">View Details</a>
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
