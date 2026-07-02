<?php
require 'includes/config.php';
$page = getPageContent($conn, 'about');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - Sagar Art</title>
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
      <div class="row align-items-center">
        <div class="col-lg-7">
          <h2><?php echo htmlspecialchars($page['title']); ?></h2>
          <p><?php echo nl2br(htmlspecialchars($page['content'])); ?></p>
        </div>
        <div class="col-lg-5">
          <img src="<?php echo htmlspecialchars($page['image_url']); ?>" alt="About us" class="img-fluid rounded shadow">
        </div>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
