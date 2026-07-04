<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;
if ($id) {
    $stmt = $conn->prepare('SELECT id, title, slug, summary, content, image_url, display_order, is_featured FROM services WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if (!empty($_FILES['image_file']['name'])) {
        $uploadedImage = uploadFile($_FILES['image_file']);
        if ($uploadedImage !== '') {
            $image_url = $uploadedImage;
        }
    }

    if ($id) {
        $update = $conn->prepare('UPDATE services SET title = ?, slug = ?, summary = ?, content = ?, image_url = ?, display_order = ?, is_featured = ? WHERE id = ?');
        $update->bind_param('sssssiii', $title, $slug, $summary, $content, $image_url, $display_order, $is_featured, $id);
        $update->execute();
    } else {
        $insert = $conn->prepare('INSERT INTO services (title, slug, summary, content, image_url, display_order, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insert->bind_param('sssssii', $title, $slug, $summary, $content, $image_url, $display_order, $is_featured);
        $insert->execute();
    }
    $success = 'Service saved successfully.';
    $service = ['id' => $id, 'title' => $title, 'slug' => $slug, 'summary' => $summary, 'content' => $content, 'image_url' => $image_url, 'display_order' => $display_order, 'is_featured' => $is_featured];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Service</title>
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
            <h3 class="fw-bold mb-1"><?php echo $service ? 'Edit Service' : 'Add Service'; ?></h3>
            <p class="text-muted mb-0">Manage services and featured content for the public website.</p>
          </div>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        <div class="card admin-card">
          <div class="card-body">
            <?php if (!empty($success)): ?>
              <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?php echo (int)($service['id'] ?? 0); ?>">
              <div class="mb-3">
                <label class="form-label">Service Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($service['title'] ?? ''); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($service['slug'] ?? ''); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Summary</label>
                <textarea name="summary" class="form-control" rows="3"><?php echo htmlspecialchars($service['summary'] ?? ''); ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="6"><?php echo htmlspecialchars($service['content'] ?? ''); ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Service Image</label>
                <input type="file" name="image_file" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Image URL / Path</label>
                <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($service['image_url'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Display Order</label>
                <input type="number" name="display_order" class="form-control" value="<?php echo (int)($service['display_order'] ?? 0); ?>">
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo (!empty($service['is_featured']) ? 'checked' : ''); ?>>
                <label class="form-check-label">Featured Service</label>
              </div>
              <button type="submit" class="btn btn-primary">Save Service</button>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
<?php $conn->close(); ?>
