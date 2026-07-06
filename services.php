<?php
require 'includes/config.php';

$siteSettings = getSiteSettings($conn);
$page = getPageContent($conn, 'services');
$services = getServices($conn);
$sections = getPageSections($conn, 'services');
$pageTitle = !empty($siteSettings['meta_title']) ? $siteSettings['meta_title'] : (($page['title'] ?? 'Services') . ' - ' . ($siteSettings['site_name'] ?? 'Sagar Art'));
$pageDescription = !empty($siteSettings['meta_description']) ? $siteSettings['meta_description'] : substr(strip_tags($page['content'] ?? ''), 0, 160);
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
      <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($page['hero_title'] ?? $page['title']); ?></h1>
      <p class="lead"><?php echo htmlspecialchars($page['hero_text'] ?? ''); ?></p>
      <?php if (!empty($page['hero_video_url'])): ?>
        <video class="hero-video mt-4" autoplay muted loop playsinline controls>
          <source src="<?php echo htmlspecialchars($page['hero_video_url']); ?>" type="video/mp4">
        </video>
      <?php endif; ?>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-4"><?php echo nl2br(htmlspecialchars($page['content'])); ?></p>
        </div>
      </div>
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

  <?php foreach ($sections as $section): ?>
    <section class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <?php if (!empty($section['title'])): ?><h3><?php echo htmlspecialchars($section['title']); ?></h3><?php endif; ?>
            <?php if (!empty($section['content'])): ?><p><?php echo nl2br(htmlspecialchars($section['content'])); ?></p><?php endif; ?>
          </div>
        </div>
        <?php if (!empty($section['image_url']) || !empty($section['video_url'])): ?>
          <div class="row mt-4">
            <div class="col-lg-10 mx-auto text-center">
              <?php if (!empty($section['video_url'])): ?>
                <video class="section-media" autoplay muted loop playsinline>
                  <source src="<?php echo htmlspecialchars($section['video_url']); ?>" type="video/mp4">
                </video>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($section['image_url']); ?>" alt="Section" class="section-media">
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
      <section class="py-5">
        <div class="container">
          <div class="row align-items-center g-4">
            <div class="col-lg-7">
              <?php if (!empty($section['title'])): ?><h3><?php echo htmlspecialchars($section['title']); ?></h3><?php endif; ?>
              <?php if (!empty($section['content'])): ?><p><?php echo nl2br(htmlspecialchars($section['content'])); ?></p><?php endif; ?>
            </div>
            <?php if (!empty($section['image_url']) || !empty($section['video_url'])): ?>
              <div class="col-lg-5">
                <?php if (!empty($section['video_url'])): ?>
                  <video class="img-fluid rounded shadow" autoplay muted loop controls>
                    <source src="<?php echo htmlspecialchars($section['video_url']); ?>" type="video/mp4">
                  </video>
                <?php else: ?>
                  <img src="<?php echo htmlspecialchars($section['image_url']); ?>" alt="Section" class="img-fluid rounded shadow">
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
  <?php endforeach; ?>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
