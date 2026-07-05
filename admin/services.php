<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;
if ($deleteId) {
    $stmt = $conn->prepare('DELETE FROM services WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    header('Location: services.php');
    exit;
}

$services = $conn->query('SELECT id, title, slug, display_order, is_featured FROM services ORDER BY display_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services — Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="admin-shell">
  <div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-main">
      <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="fw-bold mb-1">Services</h3>
            <p class="text-muted mb-0">List of services with quick actions.</p>
          </div>
          <div>
            <a href="service_form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Service</a>
            <a href="dashboard.php" class="btn btn-outline-secondary ms-2">Back to Dashboard</a>
          </div>
        </div>

        <div class="card admin-card">
          <div class="card-body">
            <?php if ($services && $services->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr>
                      <th>Title</th>
                      <th class="text-muted">Slug</th>
                      <th style="width:120px">Order</th>
                      <th style="width:160px">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = $services->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?> <?php echo (!empty($row['is_featured']) ? '<span class="badge bg-warning text-dark ms-2">Featured</span>' : ''); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($row['slug']); ?></td>
                        <td><?php echo (int)$row['display_order']; ?></td>
                        <td>
                          <div class="btn-group btn-group-sm" role="group">
                            <a class="btn btn-outline-primary" href="service_form.php?id=<?php echo (int)$row['id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a class="btn btn-outline-danger" href="services.php?delete=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this service?');" title="Delete"><i class="bi bi-trash"></i></a>
                            <a class="btn btn-outline-secondary" href="../service.php?slug=<?php echo urlencode($row['slug']); ?>" target="_blank" title="View"><i class="bi bi-eye"></i></a>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-muted">No services found. Add a new service using the button above.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
<?php $conn->close(); ?>
