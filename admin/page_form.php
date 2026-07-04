<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$deleteSlug = isset($_GET['delete']) ? trim($_GET['delete']) : '';

if ($deleteSlug !== '') {
    $stmt = $conn->prepare('DELETE FROM pages WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $deleteSlug);
    $stmt->execute();
    header('Location: page_form.php');
    exit;
}

$page = null;
if ($slug !== '') {
    $stmt = $conn->prepare('SELECT id, slug, title, content, hero_title, hero_text, image_url, hero_video_url, show_in_menu, menu_order FROM pages WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $page = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $hero_title = trim($_POST['hero_title'] ?? '');
    $hero_text = trim($_POST['hero_text'] ?? '');
    $hero_video_url = trim($_POST['hero_video_url'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0;
    $menu_order = (int)($_POST['menu_order'] ?? 0);
    $existingSlug = trim($_POST['existing_slug'] ?? '');
    $page_slug = trim($_POST['slug'] ?? '');

    if ($page_slug === '') {
        $page_slug = slugify($title);
    }

    if (!empty($_FILES['image_file']['name'])) {
        $uploadedImage = uploadFile($_FILES['image_file']);
        if ($uploadedImage !== '') {
            $image_url = $uploadedImage;
        }
    }

    if ($existingSlug !== '') {
        $stmt = $conn->prepare('UPDATE pages SET slug = ?, title = ?, content = ?, hero_title = ?, hero_text = ?, image_url = ?, hero_video_url = ?, show_in_menu = ?, menu_order = ? WHERE slug = ?');
        $stmt->bind_param('sssssssiis', $page_slug, $title, $content, $hero_title, $hero_text, $image_url, $hero_video_url, $show_in_menu, $menu_order, $existingSlug);
        $stmt->execute();
        $success = 'Page updated successfully.';
    } else {
        $stmt = $conn->prepare('INSERT INTO pages (slug, title, content, hero_title, hero_text, image_url, hero_video_url, show_in_menu, menu_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssii', $page_slug, $title, $content, $hero_title, $hero_text, $image_url, $hero_video_url, $show_in_menu, $menu_order);
        $stmt->execute();
        $success = 'Page created successfully.';
    }

    $slug = $page_slug;
    $page = ['slug' => $page_slug, 'title' => $title, 'content' => $content, 'hero_title' => $hero_title, 'hero_text' => $hero_text, 'image_url' => $image_url, 'hero_video_url' => $hero_video_url, 'show_in_menu' => $show_in_menu, 'menu_order' => $menu_order];
}

$pages = $conn->query('SELECT id, slug, title, show_in_menu FROM pages ORDER BY menu_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Pages</title>
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
            <h3 class="fw-bold mb-1">Page Manager</h3>
            <p class="text-muted mb-0">Create, update and remove pages directly from the admin panel.</p>
          </div>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>

        <div class="row g-4">
          <div class="col-lg-7">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3"><?php echo $slug ? 'Edit Page' : 'Create New Page'; ?></h4>
                <?php if (!empty($success)): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                  <input type="hidden" name="existing_slug" value="<?php echo htmlspecialchars($slug); ?>">
                  <div class="mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($page['title'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Page Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($page['slug'] ?? $slug); ?>" required>
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
                    <label class="form-label">Hero Video URL</label>
                    <input type="text" name="hero_video_url" class="form-control" value="<?php echo htmlspecialchars($page['hero_video_url'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="6"><?php echo htmlspecialchars($page['content'] ?? ''); ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Hero Image</label>
                    <input type="file" name="image_file" class="form-control">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Image URL / Path</label>
                    <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($page['image_url'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Show in Header Menu</label>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="show_in_menu" value="1" <?php echo (!empty($page['show_in_menu']) ? 'checked' : ''); ?>>
                      <label class="form-check-label">Visible</label>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Menu Order</label>
                    <input type="number" name="menu_order" class="form-control" value="<?php echo (int)($page['menu_order'] ?? 0); ?>">
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><?php echo $slug ? 'Save Changes' : 'Create Page'; ?></button>
                    <?php if ($slug): ?>
                      <a href="page_section_form.php?slug=<?php echo urlencode($slug); ?>" class="btn btn-outline-secondary">Manage Sections</a>
                    <?php endif; ?>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3">Existing Pages</h4>
                <div class="list-group list-group-flush">
                  <?php while ($row = $pages->fetch_assoc()): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="small text-muted">/<?php echo htmlspecialchars($row['slug']); ?></div>
                      </div>
                      <div class="btn-group btn-group-sm">
                        <a href="page_form.php?slug=<?php echo urlencode($row['slug']); ?>" class="btn btn-outline-primary">Edit</a>
                        <a href="page_form.php?delete=<?php echo urlencode($row['slug']); ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this page?');">Delete</a>
                      </div>
                    </div>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
<?php $conn->close(); ?>
