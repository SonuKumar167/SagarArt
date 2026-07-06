<?php
require 'includes/config.php';

$siteSettings = getSiteSettings($conn);
$page = getPageContent($conn, 'home');
$services = getServices($conn);
// map services by id for quick lookup
$servicesById = [];
foreach ($services as $s) {
  $servicesById[(int)$s['id']] = $s;
}
$sections = getPageSections($conn, 'home');
$pageTitle = !empty($siteSettings['meta_title']) ? $siteSettings['meta_title'] : (($page['title'] ?? 'Home') . ' - ' . ($siteSettings['site_name'] ?? 'Sagar Art'));
$pageDescription = !empty($siteSettings['meta_description']) ? $siteSettings['meta_description'] : substr(strip_tags($page['content'] ?? ''), 0, 160);
$sliderSections = [];
$otherSections = [];
$serviceSections = [];
foreach ($sections as $section) {
    $type = $section['section_type'] ?? 'content';
    if ($type === 'slider') {
        $sliderSections[] = $section;
    } elseif ($type === 'services') {
        $serviceSections[] = $section;
    } else {
        $otherSections[] = $section;
    }
}
$featuredServices = array_values(array_filter($services, function ($item) {
    return !empty($item['is_featured']);
}));
if (empty($featuredServices)) {
    $featuredServices = array_slice($services, 0, 3);
}
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
        <?php if (!empty($page['image_url'])): ?>
          <div class="col-lg-6">
            <img src="<?php echo htmlspecialchars($page['image_url'] ?? ''); ?>" alt="Hero" class="img-fluid rounded shadow">
          </div>
        <?php endif; ?>
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
                  <video class="d-block w-100 rounded shadow slider-media" autoplay muted loop playsinline>
                    <source src="<?php echo htmlspecialchars($slide['video_url']); ?>" type="video/mp4">
                  </video>
                <?php else: ?>
                  <img src="<?php echo htmlspecialchars($slide['image_url'] ?? ''); ?>" class="d-block w-100 rounded shadow slider-media" alt="<?php echo htmlspecialchars($slide['title'] ?? 'Slide'); ?>">
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
                <video class="img-fluid rounded shadow" autoplay muted loop playsinline>
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

  <?php if (!empty($serviceSections)): ?>
    <?php foreach ($serviceSections as $section): ?>
      <section class="py-5 bg-light">
        <div class="container">
          <div class="row align-items-center mb-4">
            <div class="col-lg-8">
              <?php if (!empty($section['title'])): ?><h2><?php echo htmlspecialchars($section['title']); ?></h2><?php endif; ?>
              <?php if (!empty($section['content'])): ?><p><?php echo nl2br(htmlspecialchars($section['content'])); ?></p><?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end">
              <?php if (!empty($section['button_text'])): ?><a href="<?php echo htmlspecialchars($section['button_link'] ?? 'services.php'); ?>" class="btn btn-primary"><?php echo htmlspecialchars($section['button_text']); ?></a><?php endif; ?>
            </div>
          </div>
          <div class="row g-4">
            <?php
              // determine which services to show for this section
              $displayServices = [];
              $displayCount = 3;
              if (!empty($section['settings'])) {
                  $s = json_decode($section['settings'], true);
                  if (is_array($s)) {
                      $ids = $s['service_ids'] ?? [];
                      $displayCount = (int)($s['count'] ?? $displayCount);
                      foreach ($ids as $id) {
                          $id = (int)$id;
                          if (isset($servicesById[$id])) {
                              $displayServices[] = $servicesById[$id];
                          }
                      }
                  }
              }
              if (empty($displayServices)) {
                  $displayServices = array_slice($featuredServices, 0, $displayCount);
              } else {
                  $displayServices = array_slice($displayServices, 0, $displayCount);
              }
            ?>
            <?php foreach ($displayServices as $service): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                  <?php if (!empty($service['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($service['image_url']); ?>" class="card-img-top rounded-top" alt="<?php echo htmlspecialchars($service['title']); ?>">
                  <?php endif; ?>
                  <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($service['summary']); ?></p>
                    <a href="service.php?slug=<?php echo urlencode($service['slug']); ?>" class="stretched-link text-decoration-none">Learn More</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
  <?php else: ?>
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
  <?php endif; ?>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
