<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;

if ($deleteId > 0) {
    $stmt = $conn->prepare('DELETE FROM pricing_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    header('Location: pricing_form.php');
    exit;
}

$pricingItem = null;
$showForm = isset($_GET['new']) || $id > 0;
if ($id > 0) {
    $stmt = $conn->prepare('SELECT id, category, item_name, slug, description, unit_label, price, sort_order, is_active FROM pricing_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $pricingItem = $stmt->get_result()->fetch_assoc();
}
$pricingItem = $pricingItem ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($item_name);
    $description = trim($_POST['description'] ?? '');
    $unit_label = trim($_POST['unit_label'] ?? '');
    $price = floatval(str_replace(',', '', trim($_POST['price'] ?? '0')));
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $existingId = isset($_POST['existing_id']) ? (int)$_POST['existing_id'] : 0;

    if ($existingId > 0) {
        $stmt = $conn->prepare('UPDATE pricing_items SET category = ?, item_name = ?, slug = ?, description = ?, unit_label = ?, price = ?, sort_order = ?, is_active = ? WHERE id = ?');
        $stmt->bind_param('sssssdiii', $category, $item_name, $slug, $description, $unit_label, $price, $sort_order, $is_active, $existingId);
        $stmt->execute();
        $success = 'Pricing item updated successfully.';
        $id = $existingId;
    } else {
        $stmt = $conn->prepare('INSERT INTO pricing_items (category, item_name, slug, description, unit_label, price, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssdii', $category, $item_name, $slug, $description, $unit_label, $price, $sort_order, $is_active);
        $stmt->execute();
        $success = 'Pricing item created successfully.';
        $id = $conn->insert_id;
    }

    $pricingItem = [
        'id' => $id,
        'category' => $category,
        'item_name' => $item_name,
        'slug' => $slug,
        'description' => $description,
        'unit_label' => $unit_label,
        'price' => $price,
        'sort_order' => $sort_order,
        'is_active' => $is_active,
    ];
}

$pricingItemsResult = $conn->query('SELECT id, category, item_name, slug, price, unit_label, is_active FROM pricing_items ORDER BY category ASC, sort_order ASC, id ASC');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Pricing</title>
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
            <h3 class="fw-bold mb-1">Pricing Manager</h3>
            <p class="text-muted mb-0">Add, update, and remove pricing products that power the calculator page.</p>
          </div>
          <div class="d-flex gap-2">
            <?php if ($showForm): ?>
              <a href="pricing_form.php" class="btn btn-outline-secondary">Back to list</a>
            <?php else: ?>
              <a href="pricing_form.php?new=1" class="btn btn-primary">Add Product</a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
          </div>
        </div>

        <?php if ($showForm): ?>
          <div class="row g-4">
            <div class="col-12">
              <div class="card admin-card">
                <div class="card-body">
                  <h4 class="mb-3"><?php echo $id ? 'Edit Pricing Item' : 'Create Pricing Item'; ?></h4>
                  <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                  <?php endif; ?>
                  <form method="post">
                    <input type="hidden" name="existing_id" value="<?php echo (int)($pricingItem['id'] ?? 0); ?>">
                    <div class="row g-3">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($pricingItem['category'] ?? ''); ?>" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="item_name" class="form-control" value="<?php echo htmlspecialchars($pricingItem['item_name'] ?? ''); ?>" required>
                      </div>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($pricingItem['slug'] ?? ''); ?>" required>
                        <div class="form-text">Unique identifier used for the calculator item.</div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Label</label>
                        <input type="text" name="unit_label" class="form-control" value="<?php echo htmlspecialchars($pricingItem['unit_label'] ?? ''); ?>" placeholder="e.g. per piece, per set, per sheet">
                      </div>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($pricingItem['price'] ?? '0.00'); ?>" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($pricingItem['sort_order'] ?? 0); ?>">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Description</label>
                      <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($pricingItem['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3 form-check">
                      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo !empty($pricingItem['is_active']) ? 'checked' : ''; ?>>
                      <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $id ? 'Save Changes' : 'Create Item'; ?></button>
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
                  <?php if ($pricingItemsResult && $pricingItemsResult->num_rows > 0): ?>
                    <div class="table-responsive">
                      <table class="table align-middle">
                        <thead>
                          <tr>
                            <th>Category</th>
                            <th>Item</th>
                            <th class="text-end">Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while ($row = $pricingItemsResult->fetch_assoc()): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($row['category']); ?></td>
                              <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                              <td class="text-end">₹ <?php echo number_format($row['price'], 2); ?></td>
                              <td><?php echo !empty($row['is_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                              <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                  <a href="pricing_form.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                  <a href="pricing_form.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this pricing item?');">Delete</a>
                                </div>
                              </td>
                            </tr>
                          <?php endwhile; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="text-muted">No pricing items found. Add a new pricing product to enable the calculator.</div>
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
