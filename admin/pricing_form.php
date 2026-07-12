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
$pricingItemThresholds = [];
if ($id > 0) {
    $stmt = $conn->prepare('SELECT id, category, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price, sort_order, is_active FROM pricing_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $pricingItem = $stmt->get_result()->fetch_assoc();

    if ($pricingItem) {
        $thresholdStmt = $conn->prepare('SELECT id, min_quantity, price FROM pricing_item_thresholds WHERE pricing_item_id = ? ORDER BY min_quantity ASC');
        $thresholdStmt->bind_param('i', $id);
        $thresholdStmt->execute();
        $thresholdResult = $thresholdStmt->get_result();
        while ($thresholdRow = $thresholdResult->fetch_assoc()) {
            $pricingItemThresholds[] = $thresholdRow;
        }
    }
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

    $thresholdMinQuantities = $_POST['threshold_min_quantity'] ?? [];
    $thresholdPrices = $_POST['threshold_price'] ?? [];
    $thresholds = [];
    foreach ($thresholdMinQuantities as $index => $minQuantity) {
      $minQuantity = floatval(str_replace(',', '', trim($minQuantity ?? '0')));
      $tierPrice = floatval(str_replace(',', '', trim($thresholdPrices[$index] ?? '0')));
      if ($minQuantity > 0 && $tierPrice > 0) {
        $thresholds[] = [
          'min_quantity' => $minQuantity,
          'price' => $tierPrice,
        ];
      }
    }

    $legacyThresholdQuantity = 0;
    $legacyThresholdPrice = 0.00;
    if (!empty($thresholds)) {
        usort($thresholds, function ($a, $b) {
            return $a['min_quantity'] <=> $b['min_quantity'];
        });
        $legacyThresholdQuantity = $thresholds[0]['min_quantity'];
        $legacyThresholdPrice = $thresholds[0]['price'];
    }

    if ($existingId > 0) {
        $stmt = $conn->prepare('UPDATE pricing_items SET category = ?, item_name = ?, slug = ?, description = ?, unit_label = ?, price = ?, threshold_quantity = ?, threshold_price = ?, sort_order = ?, is_active = ? WHERE id = ?');
        $stmt->bind_param('sssssdidiii', $category, $item_name, $slug, $description, $unit_label, $price, $legacyThresholdQuantity, $legacyThresholdPrice, $sort_order, $is_active, $existingId);
        $stmt->execute();

        $deleteStmt = $conn->prepare('DELETE FROM pricing_item_thresholds WHERE pricing_item_id = ?');
        $deleteStmt->bind_param('i', $existingId);
        $deleteStmt->execute();

        if (!empty($thresholds)) {
            $insertThreshold = $conn->prepare('INSERT INTO pricing_item_thresholds (pricing_item_id, min_quantity, price, sort_order) VALUES (?, ?, ?, ?)');
            foreach ($thresholds as $index => $threshold) {
                $sortOrder = $index + 1;
                $insertThreshold->bind_param('iddi', $existingId, $threshold['min_quantity'], $threshold['price'], $sortOrder);
                $insertThreshold->execute();
            }
        }

        $success = 'Pricing item updated successfully.';
        $id = $existingId;
    } else {
        $stmt = $conn->prepare('INSERT INTO pricing_items (category, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssdidii', $category, $item_name, $slug, $description, $unit_label, $price, $legacyThresholdQuantity, $legacyThresholdPrice, $sort_order, $is_active);
        $stmt->execute();
        $id = $conn->insert_id;

        if (!empty($thresholds)) {
            $insertThreshold = $conn->prepare('INSERT INTO pricing_item_thresholds (pricing_item_id, min_quantity, price, sort_order) VALUES (?, ?, ?, ?)');
            foreach ($thresholds as $index => $threshold) {
                $sortOrder = $index + 1;
                $insertThreshold->bind_param('iddi', $id, $threshold['min_quantity'], $threshold['price'], $sortOrder);
                $insertThreshold->execute();
            }
        }

        $success = 'Pricing item created successfully.';
    }

    $pricingItem = [
        'id' => $id,
        'category' => $category,
        'item_name' => $item_name,
        'slug' => $slug,
        'description' => $description,
        'unit_label' => $unit_label,
        'price' => $price,
        'threshold_quantity' => $legacyThresholdQuantity,
        'threshold_price' => $legacyThresholdPrice,
        'sort_order' => $sort_order,
        'is_active' => $is_active,
    ];
    $pricingItemThresholds = $thresholds;
}

$pricingItemsResult = $conn->query('SELECT id, category, item_name, slug, price, threshold_quantity, threshold_price, unit_label, is_active FROM pricing_items ORDER BY category ASC, sort_order ASC, id ASC');

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
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($pricingItem['price'] ?? '0.00'); ?>" required>
                      </div>
                      <div class="col-md-8 mb-3">
                        <label class="form-label">Quantity Pricing Tiers</label>
                        <div id="thresholds-container">
                          <?php if (!empty($pricingItemThresholds)): ?>
                            <?php foreach ($pricingItemThresholds as $tier): ?>
                              <div class="row g-2 mb-2 threshold-row align-items-end">
                                <div class="col-5">
                                  <label class="form-label visually-hidden">Min Quantity</label>
                                  <input type="number" step="any" name="threshold_min_quantity[]" class="form-control" value="<?php echo htmlspecialchars($tier['min_quantity']); ?>" min="0.01" placeholder="Min qty">
                                </div>
                                <div class="col-5">
                                  <label class="form-label visually-hidden">Tier Price</label>
                                  <input type="number" step="0.01" name="threshold_price[]" class="form-control" value="<?php echo htmlspecialchars($tier['price']); ?>" min="0" placeholder="Tier price">
                                </div>
                                <div class="col-auto">
                                  <button type="button" class="btn btn-outline-danger btn-sm remove-threshold-row">Remove</button>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="row g-2 mb-2 threshold-row align-items-end">
                              <div class="col-5">
                                <label class="form-label visually-hidden">Min Quantity</label>
                                <input type="number" step="any" name="threshold_min_quantity[]" class="form-control" min="0.01" placeholder="Min qty">
                              </div>
                              <div class="col-5">
                                <label class="form-label visually-hidden">Tier Price</label>
                                <input type="number" step="0.01" name="threshold_price[]" class="form-control" min="0" placeholder="Tier price">
                              </div>
                              <div class="col-auto">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-threshold-row">Remove</button>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                        <button type="button" id="add-threshold-row" class="btn btn-sm btn-outline-primary">Add Tier</button>
                        <div class="form-text">Use multiple rows for tiered bulk pricing. Leave blank rows empty.</div>
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
                    <div class="row mb-3">
                      <div class="col-md-6 mb-3 mb-md-0">
                        <input id="pricing-search" type="text" class="form-control" placeholder="Search category or item">
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table class="table align-middle" id="pricing-table">
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
                              <td class="text-end"><?php echo $row['threshold_quantity'] > 0 ? 'Above '.$row['threshold_quantity'].' @ ₹'.number_format($row['threshold_price'],2) : 'Standard'; ?></td>
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
                    <div class="d-flex justify-content-between align-items-center mt-3">
                      <div id="pricing-page-info" class="text-muted"></div>
                      <nav>
                        <ul id="pricing-pagination" class="pagination pagination-sm mb-0"></ul>
                      </nav>
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('pricing-search');
      const tableBody = document.querySelector('#pricing-table tbody');
      const pagination = document.getElementById('pricing-pagination');
      const pageInfo = document.getElementById('pricing-page-info');
      const rowsPerPage = 10;
      let currentPage = 1;
      let tableRows = [];

      if (searchInput && tableBody && pagination && pageInfo) {
        function buildRowData() {
          tableRows = Array.from(tableBody.querySelectorAll('tr'));
        }

        function renderTable() {
          const query = searchInput.value.trim().toLowerCase();
          const filteredRows = tableRows.filter(row => {
            const category = row.cells[0]?.textContent.trim().toLowerCase() || '';
            const item = row.cells[1]?.textContent.trim().toLowerCase() || '';
            return category.includes(query) || item.includes(query);
          });

          const totalRows = filteredRows.length;
          const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));
          currentPage = Math.min(currentPage, totalPages);

          tableRows.forEach(row => row.style.display = 'none');
          const start = (currentPage - 1) * rowsPerPage;
          const end = start + rowsPerPage;
          filteredRows.slice(start, end).forEach(row => row.style.display = '');

          pageInfo.textContent = `Showing ${Math.min(totalRows, start + 1)} to ${Math.min(totalRows, end)} of ${totalRows} matching items`;
          renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
          pagination.innerHTML = '';

          if (totalPages <= 1) {
            return;
          }

          const createButton = (label, page, disabled = false, active = false) => {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;
            button.disabled = disabled;
            button.addEventListener('click', function () {
              currentPage = page;
              renderTable();
            });
            li.appendChild(button);
            return li;
          };

          pagination.appendChild(createButton('Prev', Math.max(1, currentPage - 1), currentPage === 1));

          const maxButtons = 5;
          let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
          let endPage = Math.min(totalPages, startPage + maxButtons - 1);
          if (endPage - startPage + 1 < maxButtons) {
            startPage = Math.max(1, endPage - maxButtons + 1);
          }

          for (let page = startPage; page <= endPage; page++) {
            pagination.appendChild(createButton(page, page, false, page === currentPage));
          }

          pagination.appendChild(createButton('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages));
        }

        searchInput.addEventListener('input', function () {
          currentPage = 1;
          renderTable();
        });

        buildRowData();
        renderTable();
      }

      function createThresholdRow(minQuantity = '', price = '') {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 threshold-row align-items-end';
        row.innerHTML = `
          <div class="col-5">
            <label class="form-label visually-hidden">Min Quantity</label>
            <input type="number" step="any" name="threshold_min_quantity[]" class="form-control" min="0.01" placeholder="Min qty" value="${minQuantity}">
          </div>
          <div class="col-5">
            <label class="form-label visually-hidden">Tier Price</label>
            <input type="number" step="0.01" name="threshold_price[]" class="form-control" min="0" placeholder="Tier price" value="${price}">
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-outline-danger btn-sm remove-threshold-row">Remove</button>
          </div>
        `;
        return row;
      }

      const thresholdsContainer = document.getElementById('thresholds-container');
      const addThresholdRowButton = document.getElementById('add-threshold-row');

      if (thresholdsContainer && addThresholdRowButton) {
        addThresholdRowButton.addEventListener('click', function () {
          thresholdsContainer.appendChild(createThresholdRow());
        });

        thresholdsContainer.addEventListener('click', function (event) {
          if (!event.target.classList.contains('remove-threshold-row')) return;
          const row = event.target.closest('.threshold-row');
          if (row) {
            thresholdsContainer.removeChild(row);
          }
        });
      }
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
