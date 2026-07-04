<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$menuId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;
$childDeleteId = isset($_GET['child_delete']) ? (int)$_GET['child_delete'] : 0;
$item = null;
if ($menuId) {
    $stmt = $conn->prepare('SELECT id, label, link, menu_order, is_active, has_dropdown FROM menu_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $menuId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
}

if ($deleteId) {
    $stmt = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    header('Location: menu_form.php');
    exit;
}

if ($childDeleteId) {
    $stmt = $conn->prepare('DELETE FROM menu_children WHERE id = ?');
    $stmt->bind_param('i', $childDeleteId);
    $stmt->execute();
    header('Location: menu_form.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['child_submit'])) {
        $parent_id = (int)($_POST['child_parent_id'] ?? 0);
        $child_label = trim($_POST['child_label'] ?? '');
        $child_link = trim($_POST['child_link'] ?? '');
        $child_order = (int)($_POST['child_order'] ?? 0);

        if ($parent_id && $child_label && $child_link) {
            $stmt = $conn->prepare('INSERT INTO menu_children (parent_id, label, link, menu_order, is_active) VALUES (?, ?, ?, ?, 1)');
            $stmt->bind_param('issi', $parent_id, $child_label, $child_link, $child_order);
            $stmt->execute();
            $success = 'Submenu item added successfully.';
        }
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $menu_order = (int)($_POST['menu_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $has_dropdown = isset($_POST['has_dropdown']) ? 1 : 0;

        if ($id) {
            $stmt = $conn->prepare('UPDATE menu_items SET label = ?, link = ?, menu_order = ?, is_active = ?, has_dropdown = ? WHERE id = ?');
            $stmt->bind_param('ssiiii', $label, $link, $menu_order, $is_active, $has_dropdown, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('INSERT INTO menu_items (label, link, menu_order, is_active, has_dropdown) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssiii', $label, $link, $menu_order, $is_active, $has_dropdown);
            $stmt->execute();
        }

        $success = 'Menu item saved successfully.';
        $item = ['id' => $id, 'label' => $label, 'link' => $link, 'menu_order' => $menu_order, 'is_active' => $is_active, 'has_dropdown' => $has_dropdown];
    }
}

$items = $conn->query('SELECT id, label, link, menu_order, is_active, has_dropdown FROM menu_items ORDER BY menu_order ASC, id ASC');
$parentItems = $conn->query('SELECT id, label FROM menu_items WHERE has_dropdown = 1 AND is_active = 1 ORDER BY menu_order ASC, id ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Manager</title>
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
            <h3 class="fw-bold mb-1">Header Menu Manager</h3>
            <p class="text-muted mb-0">Control top-level menu items and dropdown content.</p>
          </div>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        <div class="row g-4">
          <div class="col-lg-7">
            <div class="card admin-card">
              <div class="card-body">
                <h3><?php echo $menuId ? 'Edit Menu Item' : 'Add Menu Item'; ?></h3>
                <?php if (!empty($success)): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="post">
                  <input type="hidden" name="id" value="<?php echo (int)($item['id'] ?? 0); ?>">
                  <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control" value="<?php echo htmlspecialchars($item['label'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($item['link'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Order</label>
                    <input type="number" name="menu_order" class="form-control" value="<?php echo (int)($item['menu_order'] ?? 0); ?>">
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo (!empty($item['is_active']) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Visible in header</label>
                  </div>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_dropdown" value="1" <?php echo (!empty($item['has_dropdown']) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Show dropdown on hover</label>
                  </div>
                  <button type="submit" class="btn btn-primary">Save</button>
                </form>
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="card admin-card">
              <div class="card-body">
                <h4>Current Header Menu</h4>
                <div class="card border-0 bg-light mb-3">
                  <div class="card-body">
                    <h6 class="card-title">Add Submenu Item</h6>
                    <form method="post">
                      <input type="hidden" name="child_submit" value="1">
                      <div class="mb-2">
                        <label class="form-label">Parent Menu</label>
                        <select name="child_parent_id" class="form-select form-select-sm" required>
                          <option value="">Select parent menu</option>
                          <?php while ($parentItem = $parentItems->fetch_assoc()): ?>
                            <option value="<?php echo (int)$parentItem['id']; ?>"><?php echo htmlspecialchars($parentItem['label']); ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="mb-2">
                        <label class="form-label">Label</label>
                        <input type="text" name="child_label" class="form-control form-control-sm" required>
                      </div>
                      <div class="mb-2">
                        <label class="form-label">Link</label>
                        <input type="text" name="child_link" class="form-control form-control-sm" required>
                      </div>
                      <div class="mb-2">
                        <label class="form-label">Order</label>
                        <input type="number" name="child_order" class="form-control form-control-sm" value="0">
                      </div>
                      <button type="submit" class="btn btn-sm btn-outline-primary">Add Submenu</button>
                    </form>
                  </div>
                </div>
                <ul class="list-group list-group-flush">
                  <?php while ($row = $items->fetch_assoc()): ?>
                    <li class="list-group-item">
                      <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                          <div class="fw-semibold"><?php echo htmlspecialchars($row['label']); ?></div>
                          <div class="small text-muted"><?php echo htmlspecialchars($row['link']); ?></div>
                          <?php if (!empty($row['has_dropdown'])): ?><span class="badge bg-info-subtle text-info-emphasis mt-2">Dropdown</span><?php endif; ?>
                        </div>
                        <div class="btn-group btn-group-sm">
                          <a href="menu_form.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-outline-primary">Edit</a>
                          <a href="menu_form.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this menu item?');">Delete</a>
                        </div>
                      </div>
                      <?php
                      $childRows = $conn->query('SELECT id, label, link FROM menu_children WHERE parent_id = ' . (int)$row['id'] . ' AND is_active = 1 ORDER BY menu_order ASC, id ASC');
                      if ($childRows && $childRows->num_rows > 0):
                      ?>
                        <ul class="list-group list-group-flush mt-2">
                          <?php while ($childRow = $childRows->fetch_assoc()): ?>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                              <span class="small">↳ <?php echo htmlspecialchars($childRow['label']); ?></span>
                              <a href="menu_form.php?child_delete=<?php echo (int)$childRow['id']; ?>" class="btn btn-link btn-sm p-0 text-danger" onclick="return confirm('Remove this submenu item?');">Remove</a>
                            </li>
                          <?php endwhile; ?>
                        </ul>
                      <?php endif; ?>
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
