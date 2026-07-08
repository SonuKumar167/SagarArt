<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$deleteSlug = isset($_GET['delete']) ? trim($_GET['delete']) : '';

if ($deleteSlug !== '') {
    $stmt = $conn->prepare('DELETE FROM services WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $deleteSlug);
    $stmt->execute();
    header('Location: service_form.php');
    exit;
}

$service = null;
$showForm = isset($_GET['new']) || $slug !== '';
if ($slug !== '') {
    $stmt = $conn->prepare('SELECT id, slug, title, hero_title, hero_text, image_url, hero_media_type, hero_video_url, hero_bg_color, hero_text_color, show_in_menu, menu_order FROM services WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
}
$service = $service ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $hero_title = trim($_POST['hero_title'] ?? '');
    $hero_text = trim($_POST['hero_text'] ?? '');
    $hero_video_url = trim($_POST['hero_video_url'] ?? $service['hero_video_url'] ?? '');
    $image_url = trim($_POST['image_url'] ?? $service['image_url'] ?? '');
    $hero_media_type = trim($_POST['hero_media_type'] ?? $service['hero_media_type'] ?? 'image');
    $hero_bg_color = trim($_POST['hero_bg_color'] ?? $service['hero_bg_color'] ?? '#4f46e5');
    $hero_text_color = trim($_POST['hero_text_color'] ?? $service['hero_text_color'] ?? '#ffffff');
    $show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0;
    $menu_order = (int)($_POST['menu_order'] ?? 0);
    $existingSlug = trim($_POST['existing_slug'] ?? '');
    $service_slug = trim($_POST['slug'] ?? '');

    if ($service_slug === '') {
        $service_slug = slugify($title);
    }

    if (!empty($_FILES['hero_media_file']['name'])) {
        $uploadedMedia = uploadFile($_FILES['hero_media_file']);
        if ($uploadedMedia !== '') {
            if (detectMediaType($uploadedMedia) === 'video') {
                $hero_video_url = $uploadedMedia;
                $hero_media_type = 'video';
                $image_url = '';
            } else {
                $image_url = $uploadedMedia;
                $hero_media_type = 'image';
                $hero_video_url = '';
            }
        }
    }

    if (!empty($hero_video_url) && detectMediaType($hero_video_url) === 'video') {
        $hero_media_type = 'video';
    } elseif (!empty($image_url)) {
        $hero_media_type = detectMediaType($image_url);
    }

    if ($existingSlug !== '') {
        $stmt = $conn->prepare('UPDATE services SET slug = ?, title = ?, hero_title = ?, hero_text = ?, image_url = ?, hero_media_type = ?, hero_video_url = ?, hero_bg_color = ?, hero_text_color = ?, show_in_menu = ?, menu_order = ? WHERE slug = ?');
        $types = str_repeat('s', 9) . 'iis';
        $stmt->bind_param($types, $service_slug, $title, $hero_title, $hero_text, $image_url, $hero_media_type, $hero_video_url, $hero_bg_color, $hero_text_color, $show_in_menu, $menu_order, $existingSlug);
        $stmt->execute();
        $success = 'Service updated successfully.';
    } else {
        $stmt = $conn->prepare('INSERT INTO services (slug, title, hero_title, hero_text, image_url, hero_media_type, hero_video_url, hero_bg_color, hero_text_color, show_in_menu, menu_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $types = str_repeat('s', 9) . 'ii';
        $stmt->bind_param($types, $service_slug, $title, $hero_title, $hero_text, $image_url, $hero_media_type, $hero_video_url, $hero_bg_color, $hero_text_color, $show_in_menu, $menu_order);
        $stmt->execute();
        $success = 'Service created successfully.';
    }

    $slug = $service_slug;
    $service = ['slug' => $service_slug, 'title' => $title, 'hero_title' => $hero_title, 'hero_text' => $hero_text, 'image_url' => $image_url, 'hero_media_type' => $hero_media_type, 'hero_video_url' => $hero_video_url, 'hero_bg_color' => $hero_bg_color, 'hero_text_color' => $hero_text_color, 'show_in_menu' => $show_in_menu, 'menu_order' => $menu_order];
}

$services = $conn->query('SELECT id, slug, title, show_in_menu FROM services ORDER BY menu_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Services</title>
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
            <h3 class="fw-bold mb-1">Service Manager</h3>
            <p class="text-muted mb-0">Create, update and manage your services like pages.</p>
          </div>
          <div class="d-flex gap-2">
            <?php if ($showForm): ?>
              <a href="service_form.php" class="btn btn-outline-secondary">Back to list</a>
            <?php else: ?>
              <a href="service_form.php?new=1" class="btn btn-primary">Add New Service</a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
          </div>
        </div>
        <?php if ($showForm): ?>
        <div class="row g-4">
          <div class="col-12">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3"><?php echo $slug ? 'Edit Service' : 'Create New Service'; ?></h4>
                <?php if (!empty($success)): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                  <input type="hidden" name="existing_slug" value="<?php echo htmlspecialchars($slug); ?>">
                  <div class="mb-3">
                    <label class="form-label">Service Title</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($service['title'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Service Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($service['slug'] ?? $slug); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Hero Title</label>
                    <input type="text" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($service['hero_title'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Hero Text</label>
                    <textarea name="hero_text" class="form-control" rows="3"><?php echo htmlspecialchars($service['hero_text'] ?? ''); ?></textarea>
                  </div>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Hero Background Color</label>
                        <input type="color" name="hero_bg_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($service['hero_bg_color'] ?? '#4f46e5'); ?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Hero Text Color</label>
                        <input type="color" name="hero_text_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($service['hero_text_color'] ?? '#ffffff'); ?>">
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Hero Background Media</label>
                    <input type="file" name="hero_media_file" accept="image/*,video/*" class="form-control">
                    <?php if (!empty($service['image_url'])): ?>
                      <small class="text-muted d-block mt-2">Current background image: <a href="../<?php echo htmlspecialchars($service['image_url']); ?>" target="_blank">View file</a></small>
                    <?php endif; ?>
                    <?php if (!empty($service['hero_video_url'])): ?>
                      <small class="text-muted d-block">Current hero video: <a href="../<?php echo htmlspecialchars($service['hero_video_url']); ?>" target="_blank">View file</a></small>
                    <?php endif; ?>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Background Image URL / Path</label>
                    <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($service['image_url'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Hero Video URL / Path</label>
                    <input type="text" name="hero_video_url" class="form-control" value="<?php echo htmlspecialchars($service['hero_video_url'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Show in Header Menu</label>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="show_in_menu" value="1" <?php echo (!empty($service['show_in_menu']) ? 'checked' : ''); ?>>
                      <label class="form-check-label">Visible</label>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Menu Order</label>
                    <input type="number" name="menu_order" class="form-control" value="<?php echo (int)($service['menu_order'] ?? 0); ?>">
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><?php echo $slug ? 'Save Changes' : 'Create Service'; ?></button>
                    <?php if ($slug): ?>
                      <a href="service_section_form.php?slug=<?php echo urlencode($slug); ?>" class="btn btn-outline-secondary">Manage Sections</a>
                    <?php endif; ?>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
          <div class="col-12">
            <div class="card admin-card">
              <div class="card-body">
                <?php if ($services && $services->num_rows > 0): ?>
                  <div class="table-responsive">
                    <table class="table align-middle">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th class="text-muted">Slug</th>
                          <th>Status</th>
                          <th class="text-end">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $services->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($row['slug']); ?></td>
                            <td><?php echo !empty($row['show_in_menu']) ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Hidden</span>'; ?></td>
                            <td class="text-end">
                              <div class="btn-group btn-group-sm" role="group">
                                <a href="../service.php?slug=<?php echo urlencode($row['slug']); ?>" class="btn btn-outline-info" target="_blank" rel="noopener noreferrer">View</a>
                                <a href="service_form.php?slug=<?php echo urlencode($row['slug']); ?>" class="btn btn-outline-primary">Edit</a>
                                <a href="service_form.php?delete=<?php echo urlencode($row['slug']); ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this service?');">Delete</a>
                              </div>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="text-muted">No services found. Create a new service using the button above.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
