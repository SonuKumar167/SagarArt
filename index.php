<?php
require 'includes/config.php';

$page = getPageContent($conn, 'home');
$services = getServices($conn);
$sections = getPageSections($conn, 'home');
$sliderSections = [];
$otherSections = [];
foreach ($sections as $section) {
    if (($section['section_type'] ?? 'content') === 'slider') {
        $sliderSections[] = $section;
    } else {
        $otherSections[] = $section;
    }
}
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

  <section class="hero-section text-white d-flex align-items-center" style="background-image: linear-gradient(135deg, rgba(13,110,253,0.9), rgba(102,16,242,0.9)), url('<?php echo htmlspecialchars($page['image_url'] ?? ''); ?>'); background-size: cover; background-position: center;">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7">
          <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($page['hero_title'] ?? $page['title']); ?></h1>
          <p class="lead"><?php echo htmlspecialchars($page['hero_text'] ?? ''); ?></p>
          <a href="services.php" class="btn btn-light btn-lg">Explore Services</a>
        </div>
        <?php if (!empty($page['hero_video_url'])): ?>
          <div class="col-lg-5 mt-4 mt-lg-0">
            <video class="hero-video shadow" autoplay muted loop playsinline controls>
              <source src="<?php echo htmlspecialchars($page['hero_video_url']); ?>" type="video/mp4">
            </video>
          </div>
        <?php endif; ?>
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
          <img src="<?php echo htmlspecialchars($page['image_url'] ?? ''); ?>" alt="Hero" class="img-fluid rounded shadow">
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($sliderSections)): ?>
    <section class="py-5 bg-light">
      <div class="container">
        <div id="homeSlider" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php foreach ($sliderSections as $index => $slide): ?>
              <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <?php if (!empty($slide['video_url'])): ?>
                  <video class="d-block w-100 rounded shadow" autoplay muted loop controls>
                    <source src="<?php echo htmlspecialchars($slide['video_url']); ?>" type="video/mp4">
                  </video>
                <?php else: ?>
                  <img src="<?php echo htmlspecialchars($slide['image_url'] ?? ''); ?>" class="d-block w-100 rounded shadow" alt="<?php echo htmlspecialchars($slide['title'] ?? 'Slide'); ?>">
                <?php endif; ?>
                <?php if (!empty($slide['title']) || !empty($slide['content'])): ?>
                  <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                    <?php if (!empty($slide['title'])): ?><h5><?php echo htmlspecialchars($slide['title']); ?></h5><?php endif; ?>
                    <?php if (!empty($slide['content'])): ?><p><?php echo htmlspecialchars($slide['content']); ?></p><?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#homeSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#homeSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
          </button>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php foreach ($otherSections as $section): ?>
    <section class="py-5">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <?php if (!empty($section['title'])): ?><h3><?php echo htmlspecialchars($section['title']); ?></h3><?php endif; ?>
            <?php if (!empty($section['content'])): ?><p><?php echo nl2br(htmlspecialchars($section['content'])); ?></p><?php endif; ?>
            <?php if (!empty($section['button_text'])): ?><a href="<?php echo htmlspecialchars($section['button_link'] ?? '#'); ?>" class="btn btn-primary"><?php echo htmlspecialchars($section['button_text']); ?></a><?php endif; ?>
          </div>
          <?php if (!empty($section['image_url']) || !empty($section['video_url'])): ?>
            <div class="col-lg-5">
              <?php if (!empty($section['video_url'])): ?>
                <video class="img-fluid rounded shadow" autoplay muted loop controls>
                  <source src="<?php echo htmlspecialchars($section['video_url']); ?>" type="video/mp4">
                </video>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($section['image_url']); ?>" alt="Section image" class="img-fluid rounded shadow">
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>

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
