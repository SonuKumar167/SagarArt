<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pages = $conn->query('SELECT id, slug, title FROM pages ORDER BY id ASC');
$services = $conn->query('SELECT id, title, slug FROM services ORDER BY id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </nav>
  <div class="container py-4">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4>Manage Pages</h4>
            <ul class="list-group list-group-flush">
              <?php while ($page = $pages->fetch_assoc()): ?>
                <li class="list-group-item"><a href="page_form.php?slug=<?php echo urlencode($page['slug']); ?>"><?php echo htmlspecialchars($page['title']); ?></a></li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4>Manage Services</h4>
            <ul class="list-group list-group-flush">
              <?php while ($service = $services->fetch_assoc()): ?>
                <li class="list-group-item"><a href="service_form.php?id=<?php echo (int)$service['id']; ?>"><?php echo htmlspecialchars($service['title']); ?></a></li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php $conn->close(); ?>
