<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$slug = $_GET['slug'] ?? '';
$sectionId = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;
$deleteSectionId = isset($_GET['delete_section']) ? (int)$_GET['delete_section'] : 0;
$removeSlideIndex = isset($_GET['remove_slide']) ? (int)$_GET['remove_slide'] : null;
$removeClientIndex = isset($_GET['remove_client']) ? (int)$_GET['remove_client'] : null;
$section = null;
if ($deleteSectionId) {
    $stmt = $conn->prepare('DELETE FROM service_sections WHERE id = ? AND service_slug = ? LIMIT 1');
    $stmt->bind_param('is', $deleteSectionId, $slug);
    $stmt->execute();
    header('Location: service_section_form.php?slug=' . urlencode($slug));
    exit;
}
if ($sectionId) {
    $stmt = $conn->prepare('SELECT id, service_slug, title, content, section_type, image_url, video_url, button_text, button_link, sort_order, settings FROM service_sections WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
    $section = $stmt->get_result()->fetch_assoc();
}
$section = $section ?: [];

if ($section && $removeSlideIndex !== null && $section['section_type'] === 'slider') {
    $settingsDecoded = json_decode($section['settings'] ?? '', true) ?: [];
    $slides = is_array($settingsDecoded['slides'] ?? null) ? $settingsDecoded['slides'] : [];
    if (isset($slides[$removeSlideIndex])) {
        array_splice($slides, $removeSlideIndex, 1);
        $settingsDecoded['slides'] = array_values($slides);
        $stmt = $conn->prepare('UPDATE service_sections SET settings = ? WHERE id = ?');
        $settingsJson = json_encode($settingsDecoded);
        $stmt->bind_param('si', $settingsJson, $sectionId);
        $stmt->execute();
    }
    header('Location: service_section_form.php?slug=' . urlencode($slug) . '&section_id=' . $sectionId);
    exit;
}

if ($section && $removeClientIndex !== null && $section['section_type'] === 'clients') {
    $settingsDecoded = json_decode($section['settings'] ?? '', true) ?: [];
    $clients = is_array($settingsDecoded['clients'] ?? null) ? $settingsDecoded['clients'] : [];
    if (isset($clients[$removeClientIndex])) {
        array_splice($clients, $removeClientIndex, 1);
        $settingsDecoded['clients'] = array_values($clients);
        $stmt = $conn->prepare('UPDATE service_sections SET settings = ? WHERE id = ?');
        $settingsJson = json_encode($settingsDecoded);
        $stmt->bind_param('si', $settingsJson, $sectionId);
        $stmt->execute();
    }
    header('Location: service_section_form.php?slug=' . urlencode($slug) . '&section_id=' . $sectionId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $service_slug = trim($_POST['service_slug'] ?? $slug);
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $section_type = trim($_POST['section_type'] ?? 'content');
  $image_url = $section['image_url'] ?? '';
  $video_url = $section['video_url'] ?? '';
  $button_text = trim($_POST['button_text'] ?? '');
  $button_link = trim($_POST['button_link'] ?? '');
  $sort_order = (int)($_POST['sort_order'] ?? 0);
  $settingsJson = '';
  if ($section_type === 'slider') {
    $existingSlideUrls = $_POST['existing_slide_urls'] ?? [];
    $existingSlideLinks = $_POST['existing_slide_links'] ?? [];
    if (!is_array($existingSlideUrls)) {
      $existingSlideUrls = [$existingSlideUrls];
    }
    if (!is_array($existingSlideLinks)) {
      $existingSlideLinks = [$existingSlideLinks];
    }
    $slides = [];
    foreach ($existingSlideUrls as $index => $slideUrl) {
      $slideUrl = trim($slideUrl);
      if ($slideUrl === '') {
        continue;
      }
      $slideLink = trim($existingSlideLinks[$index] ?? '');
      $slides[] = ['url' => $slideUrl, 'link' => $slideLink];
    }
    if (!empty($_FILES['slide_image_file']['name'])) {
        $uploadedSlide = uploadFile($_FILES['slide_image_file']);
        if ($uploadedSlide !== '') {
            $slides[] = [
              'url' => $uploadedSlide,
              'link' => trim($_POST['new_slide_link'] ?? '')
            ];
        }
    }
    $settings = ['slides' => $slides];
    $settingsJson = json_encode($settings);
  } elseif ($section_type === 'services') {
    $selected = $_POST['selected_services'] ?? [];
    if (!is_array($selected)) {
      $selected = [$selected];
    }
    $serviceIds = array_map('intval', $selected);
    $displayCount = (int)($_POST['service_count'] ?? count($serviceIds));
    $settings = ['service_ids' => $serviceIds, 'count' => $displayCount];
    $settingsJson = json_encode($settings);
  } elseif ($section_type === 'clients') {
    $existingClientLogos = $_POST['existing_client_logos'] ?? [];
    if (!is_array($existingClientLogos)) {
      $existingClientLogos = [$existingClientLogos];
    }
    $clientLogos = [];
    foreach ($existingClientLogos as $clientLogo) {
      $clientLogo = trim((string)$clientLogo);
      if ($clientLogo !== '') {
        $clientLogos[] = $clientLogo;
      }
    }
    if (!empty($_FILES['client_logo_files']['name']) && is_array($_FILES['client_logo_files']['name'])) {
      foreach ($_FILES['client_logo_files']['name'] as $index => $fileName) {
        if (empty($fileName)) {
          continue;
        }
        $uploadedClientLogo = uploadFile([
          'name' => $fileName,
          'tmp_name' => $_FILES['client_logo_files']['tmp_name'][$index] ?? '',
          'error' => $_FILES['client_logo_files']['error'][$index] ?? UPLOAD_ERR_NO_FILE,
        ]);
        if ($uploadedClientLogo !== '') {
          $clientLogos[] = $uploadedClientLogo;
        }
      }
    }
    $settings = ['clients' => $clientLogos];
    $settingsJson = json_encode($settings);
  }

  if ($section_type !== 'slider' && !empty($_FILES['file_upload']['name'])) {
    $uploadedFile = uploadFile($_FILES['file_upload']);
    if ($uploadedFile !== '') {
      $mediaType = detectMediaType($uploadedFile);
      if ($mediaType === 'video') {
        $video_url = $uploadedFile;
        $image_url = '';
      } else {
        $image_url = $uploadedFile;
        $video_url = '';
      }
    }
  }

  if ($section_type === 'slider') {
      $image_url = '';
      $video_url = '';
  }

  if ($sectionId) {
    $stmt = $conn->prepare('UPDATE service_sections SET service_slug = ?, title = ?, content = ?, section_type = ?, image_url = ?, video_url = ?, button_text = ?, button_link = ?, settings = ?, sort_order = ? WHERE id = ?');
    $stmt->bind_param('sssssssssii', $service_slug, $title, $content, $section_type, $image_url, $video_url, $button_text, $button_link, $settingsJson, $sort_order, $sectionId);
    $stmt->execute();
    header('Location: service_section_form.php?slug=' . urlencode($service_slug) . '&section_id=' . (int)$sectionId);
    exit;
  } else {
    $stmt = $conn->prepare('INSERT INTO service_sections (service_slug, title, content, section_type, image_url, video_url, button_text, button_link, settings, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssssssi', $service_slug, $title, $content, $section_type, $image_url, $video_url, $button_text, $button_link, $settingsJson, $sort_order);
    $stmt->execute();
    $newId = $conn->insert_id;
    header('Location: service_section_form.php?slug=' . urlencode($service_slug) . '&section_id=' . (int)$newId);
    exit;
  }
}

$sections = $conn->query('SELECT id, title, section_type, sort_order FROM service_sections WHERE service_slug = \'' . $conn->real_escape_string($slug) . '\' ORDER BY sort_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Sections</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-shell">
  <div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-main">
      <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="fw-bold mb-1">Manage Service Sections</h3>
            <p class="text-muted mb-0">Add extra content blocks for the selected service.</p>
          </div>
          <a href="service_form.php?slug=<?php echo urlencode($slug); ?>" class="btn btn-outline-secondary">Back to Service</a>
        </div>
        <div class="card admin-card mb-4">
          <div class="card-body">
            <h3>Manage Sections for <?php echo htmlspecialchars($slug); ?></h3>
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="service_slug" value="<?php echo htmlspecialchars($slug); ?>">
              <div class="mb-3">
                <label class="form-label">Section Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($section['title'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Section Type</label>
                <select name="section_type" class="form-select">
                  <option value="content" <?php echo (($section['section_type'] ?? 'content') === 'content' ? 'selected' : ''); ?>>Content</option>
                  <option value="slider" <?php echo (($section['section_type'] ?? 'content') === 'slider' ? 'selected' : ''); ?>>Slider</option>
                  <option value="services" <?php echo (($section['section_type'] ?? 'content') === 'services' ? 'selected' : ''); ?>>Featured Services</option>
                  <option value="clients" <?php echo (($section['section_type'] ?? 'content') === 'clients' ? 'selected' : ''); ?>>Our Clients</option>
                </select>
              </div>
              <?php
                $allServices = $conn->query('SELECT id, title FROM services ORDER BY menu_order ASC, id ASC');
                $selectedServiceIds = [];
                $serviceCount = 3;
                if (!empty($section['settings'])) {
                    $settingsDecoded = json_decode($section['settings'], true);
                    if (is_array($settingsDecoded)) {
                        $selectedServiceIds = $settingsDecoded['service_ids'] ?? [];
                        $serviceCount = (int)($settingsDecoded['count'] ?? $serviceCount);
                    }
                }
              ?>
              <div class="mb-3">
                <label class="form-label">Select Services (for Featured Services section)</label>
                <div class="row">
                  <?php if ($allServices && $allServices->num_rows > 0): ?>
                    <?php while ($s = $allServices->fetch_assoc()): ?>
                      <div class="col-md-4 col-sm-6">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="selected_services[]" value="<?php echo (int)$s['id']; ?>" id="svc_<?php echo (int)$s['id']; ?>" <?php echo (in_array((int)$s['id'], $selectedServiceIds) ? 'checked' : ''); ?>>
                          <label class="form-check-label" for="svc_<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['title']); ?></label>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <div class="col-12 small text-muted">No services available. Add services first.</div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Number of services to display</label>
                <input type="number" name="service_count" class="form-control" value="<?php echo htmlspecialchars($serviceCount); ?>">
              </div>
              <?php
                $sliderSlides = [];
                if (!empty($section['settings'])) {
                    $settingsDecoded = json_decode($section['settings'], true);
                    if (is_array($settingsDecoded) && !empty($settingsDecoded['slides']) && is_array($settingsDecoded['slides'])) {
                        $sliderSlides = $settingsDecoded['slides'];
                    }
                }
              ?>
              <?php if (($section['section_type'] ?? '') === 'slider' || empty($section)): ?>
                <div class="mb-3">
                  <label class="form-label">Current Slider Images</label>
                  <?php if (!empty($sliderSlides)): ?>
                    <div class="row gy-3">
                      <?php foreach ($sliderSlides as $index => $slideData): ?>
                        <?php $slideUrl = is_array($slideData) ? ($slideData['url'] ?? '') : $slideData; ?>
                        <?php $slideLink = is_array($slideData) ? ($slideData['link'] ?? '') : ''; ?>
                        <div class="col-md-4">
                          <div class="card p-2">
                            <img src="<?php echo htmlspecialchars('/' . ltrim($slideUrl, '/\\')); ?>" alt="Slide <?php echo $index + 1; ?>" class="img-fluid rounded mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                              <span class="small">Slide <?php echo $index + 1; ?></span>
                              <a href="service_section_form.php?slug=<?php echo urlencode($slug); ?>&section_id=<?php echo (int)$sectionId; ?>&remove_slide=<?php echo $index; ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                            </div>
                            <input type="hidden" name="existing_slide_urls[]" value="<?php echo htmlspecialchars($slideUrl); ?>">
                            <label class="form-label mt-3">Slide redirect URL</label>
                            <input type="text" name="existing_slide_links[]" class="form-control" value="<?php echo htmlspecialchars($slideLink); ?>" placeholder="Optional redirect URL for this slide">
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="small text-muted">No slides added yet.</div>
                  <?php endif; ?>
                </div>
                <div class="mb-3">
                  <label class="form-label">Upload Slider Image</label>
                  <input type="file" name="slide_image_file" accept="image/*" class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Slider image redirect URL</label>
                  <input type="text" name="new_slide_link" class="form-control" placeholder="Optional redirect URL for new slide">
                </div>
              <?php endif; ?>
              <?php if (($section['section_type'] ?? '') === 'clients' || empty($section)): ?>
                <?php $clientLogos = []; if (!empty($section['settings'])) { $settingsDecoded = json_decode($section['settings'], true); if (is_array($settingsDecoded) && !empty($settingsDecoded['clients']) && is_array($settingsDecoded['clients'])) { $clientLogos = $settingsDecoded['clients']; } } ?>
                <div class="mb-3">
                  <label class="form-label">Current Client Logos</label>
                  <?php if (!empty($clientLogos)): ?>
                    <div class="row gy-3">
                      <?php foreach ($clientLogos as $index => $clientLogo): ?>
                        <?php $clientLogoUrl = is_array($clientLogo) ? ($clientLogo['url'] ?? '') : $clientLogo; ?>
                        <?php if ($clientLogoUrl === '') continue; ?>
                        <div class="col-md-4">
                          <div class="card p-2">
                            <img src="<?php echo htmlspecialchars('/' . ltrim($clientLogoUrl, '/\\')); ?>" alt="Client logo <?php echo $index + 1; ?>" class="img-fluid rounded mb-2" style="max-height: 140px; object-fit: contain;">
                            <div class="d-flex justify-content-between align-items-center">
                              <span class="small">Logo <?php echo $index + 1; ?></span>
                              <a href="service_section_form.php?slug=<?php echo urlencode($slug); ?>&section_id=<?php echo (int)$sectionId; ?>&remove_client=<?php echo $index; ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                            </div>
                            <input type="hidden" name="existing_client_logos[]" value="<?php echo htmlspecialchars($clientLogoUrl); ?>">
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="small text-muted">No client logos uploaded yet.</div>
                  <?php endif; ?>
                </div>
                <div class="mb-3">
                  <label class="form-label">Upload Client Logos</label>
                  <input type="file" name="client_logo_files[]" accept="image/*" class="form-control" multiple>
                  <div class="small text-muted mt-2">Select multiple logo files to display in the client carousel.</div>
                </div>
              <?php endif; ?>
              <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="4"><?php echo htmlspecialchars($section['content'] ?? ''); ?></textarea>
              </div>
              <div class="mb-3 file-upload-row" <?php echo (($section['section_type'] ?? '') === 'slider' ? 'style="display:none;"' : ''); ?>>
                <label class="form-label">File Upload (image or video)</label>
                <input type="file" name="file_upload" accept="image/*,video/*" class="form-control">
                <?php if (!empty($section['image_url'])): ?>
                  <div class="small text-muted mt-2">Current image: <a href="/<?php echo ltrim(htmlspecialchars($section['image_url']), '/'); ?>" target="_blank"><?php echo htmlspecialchars($section['image_url']); ?></a></div>
                <?php elseif (!empty($section['video_url'])): ?>
                  <div class="small text-muted mt-2">Current video: <a href="/<?php echo ltrim(htmlspecialchars($section['video_url']), '/'); ?>" target="_blank"><?php echo htmlspecialchars($section['video_url']); ?></a></div>
                <?php endif; ?>
              </div>
              <div class="mb-3">
                <label class="form-label">Button Text</label>
                <input type="text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($section['button_text'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Button Link</label>
                <input type="text" name="button_link" class="form-control" value="<?php echo htmlspecialchars($section['button_link'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($section['sort_order'] ?? 0); ?>">
              </div>
              <button type="submit" class="btn btn-primary">Save Section</button>
            </form>
          </div>
        </div>
        <div class="card admin-card">
          <div class="card-body">
            <h4>Existing Sections</h4>
            <ul class="list-group list-group-flush">
              <?php while ($item = $sections->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><?php echo htmlspecialchars($item['title'] ?: 'Untitled Section'); ?> (<?php echo htmlspecialchars($item['section_type']); ?>)</span>
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="service_section_form.php?slug=<?php echo urlencode($slug); ?>&section_id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="service_section_form.php?slug=<?php echo urlencode($slug); ?>&delete_section=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this section?');">Delete</a>
                  </div>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
<?php $conn->close(); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var sel = document.querySelector('select[name="section_type"]');
  var fileRow = document.querySelector('.file-upload-row');
  function toggle() {
    if (!sel || !fileRow) return;
    if (sel.value === 'slider' || sel.value === 'clients' || sel.value === 'services') {
      fileRow.style.display = 'none';
    } else {
      fileRow.style.display = '';
    }
  }
  sel.addEventListener('change', toggle);
  toggle();
});
</script>
