<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$slug = $_GET['slug'] ?? '';
$sectionId = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;
$deleteSectionId = isset($_GET['delete_section']) ? (int)$_GET['delete_section'] : 0;
$section = null;
if ($deleteSectionId) {
    $stmt = $conn->prepare('DELETE FROM page_sections WHERE id = ? AND page_slug = ? LIMIT 1');
    $stmt->bind_param('is', $deleteSectionId, $slug);
    $stmt->execute();
    header('Location: page_section_form.php?slug=' . urlencode($slug));
    exit;
}
if ($sectionId) {
  $stmt = $conn->prepare('SELECT id, page_slug, title, content, section_type, image_url, video_url, button_text, button_link, sort_order, settings FROM page_sections WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
    $section = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_slug = trim($_POST['page_slug'] ?? $slug);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $section_type = trim($_POST['section_type'] ?? 'content');
    $image_url = trim($_POST['image_url'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $button_text = trim($_POST['button_text'] ?? '');
    $button_link = trim($_POST['button_link'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $settingsJson = '';
    // handle services selection when section_type is services
    if ($section_type === 'services') {
      $selected = $_POST['selected_services'] ?? [];
      if (!is_array($selected)) {
        $selected = [$selected];
      }
      $serviceIds = array_map('intval', $selected);
      $displayCount = (int)($_POST['service_count'] ?? count($serviceIds));
      $settings = ['service_ids' => $serviceIds, 'count' => $displayCount];
      $settingsJson = json_encode($settings);
    }

    if (!empty($_FILES['image_file']['name'])) {
        $uploadedImage = uploadFile($_FILES['image_file']);
        if ($uploadedImage !== '') {
            $image_url = $uploadedImage;
        }
    }

    if ($sectionId) {
      $stmt = $conn->prepare('UPDATE page_sections SET page_slug = ?, title = ?, content = ?, section_type = ?, image_url = ?, video_url = ?, button_text = ?, button_link = ?, settings = ?, sort_order = ? WHERE id = ?');
      $stmt->bind_param('sssssssssii', $page_slug, $title, $content, $section_type, $image_url, $video_url, $button_text, $button_link, $settingsJson, $sort_order, $sectionId);
      $stmt->execute();
      // Redirect to the edit view to ensure we reload saved settings from the database
      header('Location: page_section_form.php?slug=' . urlencode($page_slug) . '&section_id=' . (int)$sectionId);
      exit;
    } else {
      $stmt = $conn->prepare('INSERT INTO page_sections (page_slug, title, content, section_type, image_url, video_url, button_text, button_link, settings, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $stmt->bind_param('sssssssssi', $page_slug, $title, $content, $section_type, $image_url, $video_url, $button_text, $button_link, $settingsJson, $sort_order);
      $stmt->execute();
      $newId = $conn->insert_id;
      header('Location: page_section_form.php?slug=' . urlencode($page_slug) . '&section_id=' . (int)$newId);
      exit;
    }
}

$sections = $conn->query('SELECT id, title, section_type, sort_order FROM page_sections WHERE page_slug = \'' . $conn->real_escape_string($slug) . '\' ORDER BY sort_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page Sections</title>
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
            <h3 class="fw-bold mb-1">Manage Sections</h3>
            <p class="text-muted mb-0">Add extra content blocks for the selected page.</p>
          </div>
          <a href="page_form.php?slug=<?php echo urlencode($slug); ?>" class="btn btn-outline-secondary">Back to Page</a>
        </div>
        <div class="card admin-card mb-4">
          <div class="card-body">
            <h3>Manage Sections for <?php echo htmlspecialchars($slug); ?></h3>
            <?php if (!empty($success)): ?>
              <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="page_slug" value="<?php echo htmlspecialchars($slug); ?>">
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
                </select>
              </div>
              <?php
                $allServices = $conn->query('SELECT id, title FROM services ORDER BY display_order ASC, id ASC');
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
              <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="4"><?php echo htmlspecialchars($section['content'] ?? ''); ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image_file" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Image URL / Path</label>
                <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($section['image_url'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Video URL</label>
                <input type="text" name="video_url" class="form-control" value="<?php echo htmlspecialchars($section['video_url'] ?? ''); ?>">
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
                  <a href="page_section_form.php?slug=<?php echo urlencode($slug); ?>&section_id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <a href="page_section_form.php?slug=<?php echo urlencode($slug); ?>&delete_section=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this section?');">Delete</a>
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
