<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;
if ($id) {
    $stmt = $conn->prepare('SELECT id, title, slug, summary, content FROM services WHERE id = ? LIMIT 1');
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

    if ($id) {
        $update = $conn->prepare('UPDATE services SET title = ?, slug = ?, summary = ?, content = ? WHERE id = ?');
        $update->bind_param('ssssi', $title, $slug, $summary, $content, $id);
        $update->execute();
    } else {
        $insert = $conn->prepare('INSERT INTO services (title, slug, summary, content) VALUES (?, ?, ?, ?)');
        $insert->bind_param('ssss', $title, $slug, $summary, $content);
        $insert->execute();
    }
    $success = 'Service saved successfully.';
    $service = ['id' => $id, 'title' => $title, 'slug' => $slug, 'summary' => $summary, 'content' => $content];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Service</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <a href="dashboard.php" class="btn btn-outline-secondary mb-3">Back to Dashboard</a>
    <div class="card shadow-sm">
      <div class="card-body">
        <h3><?php echo $service ? 'Edit Service' : 'Add Service'; ?></h3>
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post">
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
          <button type="submit" class="btn btn-primary">Save Service</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
<?php $conn->close(); ?>
