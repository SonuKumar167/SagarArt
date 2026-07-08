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

  <section class="page-hero text-white d-flex align-items-center" style="background: linear-gradient(180deg, rgba(0,0,0,0.32), rgba(0,0,0,0.16)), <?php echo htmlspecialchars($page['hero_bg_color'] ?? '#4f46e5'); ?>; background-image: linear-gradient(135deg, rgba(0,0,0,0.32), rgba(0,0,0,0.12)), url('<?php echo htmlspecialchars($page['image_url'] ?? ''); ?>'); background-size: cover; background-position: center; color: <?php echo htmlspecialchars($page['hero_text_color'] ?? '#ffffff'); ?>;">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($page['hero_title'] ?? $page['title']); ?></h1>
          <p class="lead"><?php echo htmlspecialchars($page['hero_text'] ?? ''); ?></p>
        </div>
        <?php if (!empty($page['hero_video_url'])): ?>
          <div class="col-lg-5">
            <video class="hero-video shadow" autoplay muted loop playsinline controls>
              <source src="<?php echo htmlspecialchars($page['hero_video_url']); ?>" type="video/mp4">
            </video>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php foreach ($sections as $section): ?>
    <section class="py-5<?php echo ($section['section_type'] === 'slider' ? ' text-white' : ''); ?>">
      <div class="container">
        <?php if (!empty($section['title'])): ?><h3><?php echo htmlspecialchars($section['title']); ?></h3><?php endif; ?>
        <?php if (!empty($section['content'])): ?><p><?php echo nl2br(htmlspecialchars($section['content'])); ?></p><?php endif; ?>
        <?php if ($section['section_type'] === 'slider'): ?>
          <?php $sliderSettings = json_decode($section['settings'] ?? '', true); $slides = is_array($sliderSettings['slides'] ?? null) ? $sliderSettings['slides'] : []; ?>
          <?php if (!empty($slides)): ?>
            <div id="sectionSlider<?php echo (int)$section['id']; ?>" class="carousel slide text-white" data-bs-ride="carousel">
              <div class="carousel-inner rounded-4 overflow-hidden">
                <?php foreach ($slides as $index => $slideData): ?>
                  <?php $slideUrl = is_array($slideData) ? ($slideData['url'] ?? '') : $slideData; ?>
                  <?php $slideLink = is_array($slideData) ? trim($slideData['link'] ?? '') : ''; ?>
                  <?php if ($slideLink === '') { $slideLink = trim($section['button_link'] ?? ''); } ?>
                  <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                    <?php if ($slideLink !== ''): ?>
                      <a href="<?php echo htmlspecialchars($slideLink); ?>">
                        <img src="<?php echo htmlspecialchars($slideUrl); ?>" class="d-block w-100 slider-media" alt="Slide <?php echo $index + 1; ?>">
                      </a>
                    <?php else: ?>
                      <img src="<?php echo htmlspecialchars($slideUrl); ?>" class="d-block w-100 slider-media" alt="Slide <?php echo $index + 1; ?>">
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if (count($slides) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#sectionSlider<?php echo (int)$section['id']; ?>" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#sectionSlider<?php echo (int)$section['id']; ?>" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Next</span>
                </button>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <?php if (!empty($section['image_url']) || !empty($section['video_url'])): ?>
            <div class="row mt-4">
              <div class="col-lg-5">
                <?php $sectionLink = trim($section['button_link'] ?? ''); ?>
                <?php if (!empty($section['video_url'])): ?>
                  <?php if ($sectionLink !== ''): ?><a href="<?php echo htmlspecialchars($sectionLink); ?>"><?php endif; ?>
                    <video class="section-media" autoplay muted loop playsinline controls>
                      <source src="<?php echo htmlspecialchars($section['video_url']); ?>" type="video/mp4">
                    </video>
                  <?php if ($sectionLink !== ''): ?></a><?php endif; ?>
                <?php else: ?>
                  <?php if ($sectionLink !== ''): ?><a href="<?php echo htmlspecialchars($sectionLink); ?>"><?php endif; ?>
                    <img src="<?php echo htmlspecialchars($section['image_url']); ?>" alt="Section" class="section-media">
                  <?php if ($sectionLink !== ''): ?></a><?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($section['button_text'])): ?><a href="<?php echo htmlspecialchars($section['button_link'] ?? '#'); ?>" class="btn btn-primary mt-4"><?php echo htmlspecialchars($section['button_text']); ?></a><?php endif; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
