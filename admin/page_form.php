<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$slug = $_GET['slug'] ?? '';
$stmt = $conn->prepare('SELECT id, slug, title, content, hero_title, hero_text, image_url FROM pages WHERE slug = ? LIMIT 1');
$stmt->bind_param('s', $slug);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $hero_title = trim($_POST['hero_title'] ?? '');
    $hero_text = trim($_POST['hero_text'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    $update = $conn->prepare('UPDATE pages SET title = ?, content = ?, hero_title = ?, hero_text = ?, image_url = ? WHERE slug = ?');
    $update->bind_param('ssssss', $title, $content, $hero_title, $hero_text, $image_url, $slug);
    $update->execute();
    $success = 'Page updated successfully.';
    $page = ['title' => $title, 'content' => $content, 'hero_title' => $hero_title, 'hero_text' => $hero_text, 'image_url' => $image_url];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <a href="dashboard.php" class="btn btn-outline-secondary mb-3">Back to Dashboard</a>
    <div class="card shadow-sm">
      <div class="card-body">
        <h3>Edit <?php echo htmlspecialchars($page['title'] ?? $slug); ?></h3>
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Page Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($page['title'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Hero Title</label>
            <input type="text" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($page['hero_title'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Hero Text</label>
            <textarea name="hero_text" class="form-control" rows="3"><?php echo htmlspecialchars($page['hero_text'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6"><?php echo htmlspecialchars($page['content'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Image URL</label>
            <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($page['image_url'] ?? ''); ?>">
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
<?php $conn->close(); ?>
