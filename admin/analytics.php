<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageCount = (int)$conn->query('SELECT COUNT(*) AS total FROM pages')->fetch_assoc()['total'];
$serviceCount = (int)$conn->query('SELECT COUNT(*) AS total FROM services')->fetch_assoc()['total'];
$menuCount = (int)$conn->query('SELECT COUNT(*) AS total FROM menu_items')->fetch_assoc()['total'];
$messageCount = (int)$conn->query('SELECT COUNT(*) AS total FROM contact_submissions')->fetch_assoc()['total'];
$recentMessages = $conn->query('SELECT name, email, submitted_at FROM contact_submissions ORDER BY submitted_at DESC LIMIT 5');
$pages = $conn->query('SELECT title, slug FROM pages ORDER BY id ASC');
$services = $conn->query('SELECT title, slug FROM services ORDER BY display_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Analytics</title>
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
            <h3 class="fw-bold mb-1">Analytics Overview</h3>
            <p class="text-muted mb-0">Track content health and audience engagement at a glance.</p>
          </div>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card admin-card h-100">
              <div class="card-body">
                <h6 class="text-muted">Pages</h6>
                <h2 class="fw-bold"><?php echo $pageCount; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card admin-card h-100">
              <div class="card-body">
                <h6 class="text-muted">Services</h6>
                <h2 class="fw-bold"><?php echo $serviceCount; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card admin-card h-100">
              <div class="card-body">
                <h6 class="text-muted">Menu Items</h6>
                <h2 class="fw-bold"><?php echo $menuCount; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card admin-card h-100">
              <div class="card-body">
                <h6 class="text-muted">Messages</h6>
                <h2 class="fw-bold"><?php echo $messageCount; ?></h2>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="card admin-card">
              <div class="card-body">
                <h5 class="fw-semibold mb-3">Content Snapshot</h5>
                <div class="analytics-chart mb-4">
                  <div class="analytics-bar">
                    <span style="height: <?php echo max(20, min(100, $pageCount * 20)); ?>%"></span>
                    <small>Pages</small>
                  </div>
                  <div class="analytics-bar">
                    <span style="height: <?php echo max(20, min(100, $serviceCount * 18)); ?>%"></span>
                    <small>Services</small>
                  </div>
                  <div class="analytics-bar">
                    <span style="height: <?php echo max(20, min(100, $menuCount * 18)); ?>%"></span>
                    <small>Menus</small>
                  </div>
                  <div class="analytics-bar">
                    <span style="height: <?php echo max(20, min(100, $messageCount * 12)); ?>%"></span>
                    <small>Messages</small>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Link</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($page = $pages->fetch_assoc()): ?>
                        <tr>
                          <td>Page</td>
                          <td><?php echo htmlspecialchars($page['title']); ?></td>
                          <td><a href="../<?php echo htmlspecialchars($page['slug']); ?>.php">View</a></td>
                        </tr>
                      <?php endwhile; ?>
                      <?php while ($service = $services->fetch_assoc()): ?>
                        <tr>
                          <td>Service</td>
                          <td><?php echo htmlspecialchars($service['title']); ?></td>
                          <td><a href="../service.php?slug=<?php echo urlencode($service['slug']); ?>">View</a></td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card admin-card">
              <div class="card-body">
                <h5 class="fw-semibold mb-3">Recent Messages</h5>
                <ul class="list-group list-group-flush">
                  <?php while ($message = $recentMessages->fetch_assoc()): ?>
                    <li class="list-group-item px-0">
                      <div class="fw-semibold"><?php echo htmlspecialchars($message['name']); ?></div>
                      <div class="small text-muted"><?php echo htmlspecialchars($message['email']); ?></div>
                      <div class="small text-muted"><?php echo htmlspecialchars($message['submitted_at']); ?></div>
                    </li>
                  <?php endwhile; ?>
                </ul>
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
