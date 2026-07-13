<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;
$editCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($deleteId > 0) {
    $stmt = $conn->prepare('DELETE FROM pricing_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    header('Location: pricing_form.php');
    exit;
}

$pricingItem = null;
$showForm = isset($_GET['new']) || $id > 0 || $editCategoryId > 0;
$pricingItemThresholds = [];
$selectedItemIds = [];
$selectedPricingData = [];
$categoryId = 0;
if ($id > 0) {
    $stmt = $conn->prepare('SELECT id, catalog_item_id, category_id, category, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price, sort_order, is_active FROM pricing_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $pricingItem = $stmt->get_result()->fetch_assoc();

    if ($pricingItem) {
        $categoryId = (int)($pricingItem['category_id'] ?? 0);
        $siblingsStmt = $conn->prepare('SELECT id, catalog_item_id, category_id, category, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price, sort_order, is_active FROM pricing_items WHERE (category = ? OR category_id = ?) ORDER BY sort_order ASC, id ASC');
        $catName = $pricingItem['category'];
        $siblingsStmt->bind_param('si', $catName, $categoryId);
        $siblingsStmt->execute();
        $siblingsResult = $siblingsStmt->get_result();

        while ($row = $siblingsResult->fetch_assoc()) {
            $thresholdStmt = $conn->prepare('SELECT id, min_quantity, price FROM pricing_item_thresholds WHERE pricing_item_id = ? ORDER BY min_quantity ASC');
            $thresholdStmt->bind_param('i', $row['id']);
            $thresholdStmt->execute();
            $thresholdResult = $thresholdStmt->get_result();
            $itemThresholds = [];

            while ($thresholdRow = $thresholdResult->fetch_assoc()) {
                $itemThresholds[] = $thresholdRow;
            }

            if (!empty($row['catalog_item_id'])) {
                $selectedItemIds[] = (int)$row['catalog_item_id'];
                $selectedPricingData[$row['catalog_item_id']] = [
                    'unit_label' => $row['unit_label'] ?? '',
                    'slug' => $row['slug'] ?? '',
                    'price' => $row['price'] ?? '',
                    'thresholds' => array_map(function ($threshold) {
                        return [
                            'min_quantity' => $threshold['min_quantity'],
                            'price' => $threshold['price'],
                        ];
                    }, $itemThresholds),
                ];
            }

            if (empty($pricingItemThresholds) && $row['id'] === $pricingItem['id']) {
                $pricingItemThresholds = $itemThresholds;
            }
        }
    }
}
$pricingItem = $pricingItem ?: [];
$selectedItemIds = array_unique($selectedItemIds);
$selectedPricingData = $selectedPricingData;

$categoryOptions = [];
$categoryResult = $conn->query('SELECT id, name, slug FROM pricing_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
if ($categoryResult) {
    while ($categoryRow = $categoryResult->fetch_assoc()) {
        $categoryOptions[] = $categoryRow;
    }
}

$categoryNameById = [];
foreach ($categoryOptions as $categoryOption) {
    $categoryNameById[(int)$categoryOption['id']] = $categoryOption['name'];
}

// reverse lookup: category name => id
$categoryIdByName = array_flip($categoryNameById);

if ($id > 0 && !empty($pricingItem)) {
    if ($categoryId <= 0) {
        foreach ($categoryNameById as $cid => $name) {
            if ($name === $pricingItem['category']) {
                $categoryId = $cid;
                break;
            }
        }
    }
    $pricingItem['category_id'] = $categoryId;
}

// If a category edit was requested (open form with items for a category)
if ($editCategoryId > 0 && empty($pricingItem)) {
    $categoryId = $editCategoryId;
    $categoryName = trim($categoryNameById[$categoryId] ?? '');
    if ($categoryName !== '') {
        $rowsStmt = $conn->prepare('SELECT id, catalog_item_id, category_id, category, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price, sort_order, is_active FROM pricing_items WHERE (category = ? OR category_id = ?) ORDER BY sort_order ASC, id ASC');
        if ($rowsStmt) {
            $rowsStmt->bind_param('si', $categoryName, $categoryId);
            $rowsStmt->execute();
            $rowsResult = $rowsStmt->get_result();
            while ($row = $rowsResult->fetch_assoc()) {
        if (!empty($row['catalog_item_id'])) {
          $selectedItemIds[] = (int)$row['catalog_item_id'];
          $thresholdStmt = $conn->prepare('SELECT id, min_quantity, price FROM pricing_item_thresholds WHERE pricing_item_id = ? ORDER BY min_quantity ASC');
          $thresholdStmt->bind_param('i', $row['id']);
          $thresholdStmt->execute();
          $thresholdResult = $thresholdStmt->get_result();
          $itemThresholds = [];
          while ($thresholdRow = $thresholdResult->fetch_assoc()) {
            $itemThresholds[] = $thresholdRow;
          }
          $selectedPricingData[$row['catalog_item_id']] = [
            'unit_label' => $row['unit_label'] ?? '',
            'slug' => $row['slug'] ?? '',
            'price' => $row['price'] ?? '',
            'thresholds' => array_map(function ($threshold) {
              return [
                'min_quantity' => $threshold['min_quantity'],
                'price' => $threshold['price'],
              ];
            }, $itemThresholds),
          ];
        }
      }
    }
    $selectedItemIds = array_unique($selectedItemIds);

    // Load default form values for category-level edit if there is no single pricing item selected.
    if (empty($pricingItem) && $categoryId > 0) {
        $defaultFormStmt = $conn->prepare('SELECT description, sort_order, is_active FROM pricing_items WHERE category_id = ? LIMIT 1');
        if ($defaultFormStmt) {
            $defaultFormStmt->bind_param('i', $categoryId);
            $defaultFormStmt->execute();
            $defaultResult = $defaultFormStmt->get_result();
            if ($defaultRow = $defaultResult->fetch_assoc()) {
                $pricingItem['description'] = $defaultRow['description'];
                $pricingItem['sort_order'] = $defaultRow['sort_order'];
                $pricingItem['is_active'] = $defaultRow['is_active'];
                $pricingItem['category_id'] = $categoryId;
            }
        }
    }

    $selectedItemIds = array_unique($selectedItemIds);
  }
}

$catalogItems = [];
$catalogItemsResult = $conn->query('SELECT pci.id, pci.category_id, pci.item_name, pci.slug, pci.unit_label, pc.name AS category_name FROM pricing_catalog_items pci JOIN pricing_categories pc ON pc.id = pci.category_id WHERE pci.is_active = 1 ORDER BY pc.sort_order ASC, pc.id ASC, pci.sort_order ASC, pci.id ASC');
if ($catalogItemsResult) {
    while ($itemRow = $catalogItemsResult->fetch_assoc()) {
        $catalogItems[] = $itemRow;
    }
}

$itemNameById = [];
foreach ($catalogItems as $catalogItem) {
    $itemNameById[(int)$catalogItem['id']] = $catalogItem['item_name'];
}

function makeUniqueSlug($conn, $baseSlug, $excludeId = 0) {
    $slug = $baseSlug;
    $suffix = 1;
    while (true) {
        if ($excludeId > 0) {
            $stmt = $conn->prepare('SELECT id FROM pricing_items WHERE slug = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $slug, $excludeId);
        } else {
            $stmt = $conn->prepare('SELECT id FROM pricing_items WHERE slug = ? LIMIT 1');
            $stmt->bind_param('s', $slug);
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $selectedItemIds = array_unique(array_map('intval', (array)($_POST['item_ids'] ?? [])));
    $category = trim($categoryNameById[$categoryId] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $existingId = isset($_POST['existing_id']) ? (int)$_POST['existing_id'] : 0;
    $originalCategory = '';

    if ($existingId > 0) {
        $stmt = $conn->prepare('SELECT category FROM pricing_items WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $existingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            $originalCategory = trim($row['category']);
        } else {
            $existingId = 0;
        }
    }

    if (empty($category) || empty($selectedItemIds)) {
        $error = 'Please select a category and at least one item.';
    } else {
        // Build pricing data array from submitted inputs for each selected item
        $itemsPricingData = [];
        foreach ($selectedItemIds as $catalogItemId) {
          $itemsPricingData[$catalogItemId] = [
            'unit_label' => trim($_POST['unit_label'][$catalogItemId] ?? ''),
            'slug' => trim($_POST['slug'][$catalogItemId] ?? ''),
            'price' => floatval(str_replace(',', '', trim($_POST['price'][$catalogItemId] ?? '0'))),
            'thresholds' => [],
          ];
          $thresholdMinQuantities = $_POST['thresholds'][$catalogItemId]['min_quantity'] ?? [];
          $thresholdPrices = $_POST['thresholds'][$catalogItemId]['price'] ?? [];
          foreach ($thresholdMinQuantities as $index => $minQuantity) {
            $minQuantity = floatval(str_replace(',', '', trim($minQuantity ?? '0')));
            $tierPrice = floatval(str_replace(',', '', trim($thresholdPrices[$index] ?? '0')));
            if ($minQuantity > 0 && $tierPrice > 0) {
              $itemsPricingData[$catalogItemId]['thresholds'][] = [
                'min_quantity' => $minQuantity,
                'price' => $tierPrice,
              ];
            }
          }
        }

        // Load existing pricing rows for this category to determine updates vs inserts
        $existingRows = [];
        if ($categoryId > 0) {
          $rowsStmt = $conn->prepare('SELECT id, catalog_item_id FROM pricing_items WHERE category_id = ?');
          if ($rowsStmt) {
            $rowsStmt->bind_param('i', $categoryId);
            $rowsStmt->execute();
            $rowsResult = $rowsStmt->get_result();
            while ($row = $rowsResult->fetch_assoc()) {
              if (!empty($row['catalog_item_id'])) {
                $existingRows[(int)$row['catalog_item_id']] = (int)$row['id'];
              }
            }
          }
        } else {
          $rowsStmt = $conn->prepare('SELECT id, catalog_item_id FROM pricing_items WHERE category = ?');
          if ($rowsStmt) {
            $rowsStmt->bind_param('s', $category);
            $rowsStmt->execute();
            $rowsResult = $rowsStmt->get_result();
            while ($row = $rowsResult->fetch_assoc()) {
              if (!empty($row['catalog_item_id'])) {
                $existingRows[(int)$row['catalog_item_id']] = (int)$row['id'];
              }
            }
          }
        }

        // Remove any existing pricing rows for this category that are no longer selected
        if (!empty($selectedItemIds)) {
          $placeholders = implode(',', array_fill(0, count($selectedItemIds), '?'));
          if ($categoryId > 0) {
            $deleteSql = "DELETE FROM pricing_items WHERE category_id = ? AND catalog_item_id NOT IN ($placeholders)";
          } else {
            $deleteSql = "DELETE FROM pricing_items WHERE category = ? AND catalog_item_id NOT IN ($placeholders)";
          }
          $deleteStmt = $conn->prepare($deleteSql);
          if ($deleteStmt) {
            if ($categoryId > 0) {
              $types = 'i' . str_repeat('i', count($selectedItemIds));
              $bindParams = [];
              $bindParams[] = & $types;
              $bindParams[] = & $categoryId;
              foreach ($selectedItemIds as $key => $value) {
                $bindParams[] = & $selectedItemIds[$key];
              }
              call_user_func_array([$deleteStmt, 'bind_param'], $bindParams);
            } else {
              $types = 's' . str_repeat('i', count($selectedItemIds));
              $bindParams = [];
              $bindParams[] = & $types;
              $bindParams[] = & $category;
              foreach ($selectedItemIds as $key => $value) {
                $bindParams[] = & $selectedItemIds[$key];
              }
              call_user_func_array([$deleteStmt, 'bind_param'], $bindParams);
            }
            $deleteStmt->execute();
          }
        }

        $savedIds = [];
        foreach ($selectedItemIds as $catalogItemId) {
            $item_name = trim($itemNameById[$catalogItemId] ?? '');
            $unit_label = trim($_POST['unit_label'][$catalogItemId] ?? '');
            $price = floatval(str_replace(',', '', trim($_POST['price'][$catalogItemId] ?? '0')));
            $slugBase = slugify($category . ' ' . $item_name . ' ' . $unit_label);
            $submittedSlug = trim($_POST['slug'][$catalogItemId] ?? '');
            $slug = $submittedSlug !== '' ? slugify($submittedSlug) : $slugBase;

            $itemId = isset($existingRows[$catalogItemId]) ? $existingRows[$catalogItemId] : 0;
            if ($itemId > 0) {
                $slug = makeUniqueSlug($conn, $slug, $itemId);
            } else {
                $slug = makeUniqueSlug($conn, $slug);
            }

            $thresholds = [];
            $thresholdMinQuantities = $_POST['thresholds'][$catalogItemId]['min_quantity'] ?? [];
            $thresholdPrices = $_POST['thresholds'][$catalogItemId]['price'] ?? [];
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

            if ($itemId > 0) {
              $stmt = $conn->prepare('UPDATE pricing_items SET catalog_item_id = ?, category_id = ?, category = ?, item_name = ?, slug = ?, description = ?, unit_label = ?, price = ?, threshold_quantity = ?, threshold_price = ?, sort_order = ?, is_active = ? WHERE id = ?');
              $stmt->bind_param('iisssssdidiii', $catalogItemId, $categoryId, $category, $item_name, $slug, $description, $unit_label, $price, $legacyThresholdQuantity, $legacyThresholdPrice, $sort_order, $is_active, $itemId);
              $stmt->execute();
            } else {
              $stmt = $conn->prepare('INSERT INTO pricing_items (catalog_item_id, category_id, category, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
              $stmt->bind_param('iisssssdidii', $catalogItemId, $categoryId, $category, $item_name, $slug, $description, $unit_label, $price, $legacyThresholdQuantity, $legacyThresholdPrice, $sort_order, $is_active);
              $stmt->execute();
              $itemId = $conn->insert_id;
            }

            if ($itemId > 0) {
                $savedIds[] = $itemId;

                $deleteThresholdStmt = $conn->prepare('DELETE FROM pricing_item_thresholds WHERE pricing_item_id = ?');
                $deleteThresholdStmt->bind_param('i', $itemId);
                $deleteThresholdStmt->execute();

                if (!empty($thresholds)) {
                    $insertThreshold = $conn->prepare('INSERT INTO pricing_item_thresholds (pricing_item_id, min_quantity, price, sort_order) VALUES (?, ?, ?, ?)');
                    foreach ($thresholds as $index => $threshold) {
                        $sortOrder = $index + 1;
                        $insertThreshold->bind_param('iddi', $itemId, $threshold['min_quantity'], $threshold['price'], $sortOrder);
                        $insertThreshold->execute();
                    }
                }
            }
        }

        if (!empty($savedIds)) {
            $success = count($savedIds) > 1 ? 'Pricing items saved successfully.' : 'Pricing item saved successfully.';
            $id = end($savedIds);
        }

        // After saving, ensure the UI preloads the submitted values so the admin sees the entered data immediately
        $selectedPricingData = $itemsPricingData;

        $pricingItem = [
            'id' => $id,
            'category' => $category,
            'item_name' => trim($itemNameById[$selectedItemIds[0]] ?? ''),
            'slug' => trim($_POST['slug'][$selectedItemIds[0]] ?? ''),
            'description' => $description,
            'unit_label' => trim($_POST['unit_label'][$selectedItemIds[0]] ?? ''),
            'price' => floatval(str_replace(',', '', trim($_POST['price'][$selectedItemIds[0]] ?? '0'))),
            'threshold_quantity' => $legacyThresholdQuantity,
            'threshold_price' => $legacyThresholdPrice,
            'sort_order' => $sort_order,
            'is_active' => $is_active,
        ];
        $pricingItemThresholds = $thresholds;
    }
}

$categorySummaries = $conn->query("SELECT pc.id, pc.name, pc.sort_order, (
  SELECT COUNT(*) FROM pricing_items pi WHERE (pi.category_id = pc.id OR pi.category = pc.name)
) AS item_count FROM pricing_categories pc ORDER BY pc.sort_order ASC, pc.id ASC");

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
                    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
                    <div class="row g-3">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="pricing-category-select" class="form-select" required>
                          <option value="">Select a category</option>
                          <?php foreach ($categoryOptions as $categoryOption): ?>
                            <option value="<?php echo (int)$categoryOption['id']; ?>" <?php echo ((int)($categoryOption['id']) === (int)($categoryId ?? $pricingItem['category_id'] ?? 0)) ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoryOption['name']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Items</label>
                        <div id="pricing-items-list" class="border rounded p-2" style="max-height:240px;overflow:auto;">
                          <!-- Item checkboxes populated by JS -->
                        </div>
                        <div class="form-text">Select items to configure pricing (checkboxes).</div>
                      </div>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($description ?? $pricingItem['description'] ?? ''); ?></textarea>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($sort_order ?? $pricingItem['sort_order'] ?? 0); ?>">
                      </div>
                      <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="is_active" id="pricing-active" value="1" <?php echo (!empty($is_active ?? null) || !empty($pricingItem['is_active'] ?? null)) ? 'checked' : ''; ?>>
                          <label class="form-check-label" for="pricing-active">Active</label>
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Selected Items</label>
                      <div id="selected-items-container" class="mb-3"></div>
                      <div class="form-text">Set price, slug, unit label, and tiered pricing for each selected item.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
                  <?php if ($categorySummaries && $categorySummaries->num_rows > 0): ?>
                    <div class="row mb-3">
                      <div class="col-md-6 mb-3 mb-md-0">
                        <input id="pricing-search" type="text" class="form-control" placeholder="Search category">
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table class="table align-middle" id="pricing-table">
                        <thead>
                          <tr>
                            <th>Category</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while ($row = $categorySummaries->fetch_assoc()): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($row['name']); ?></td>
                              <td class="text-center"><?php echo (int)$row['item_count']; ?></td>
                              <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                  <a href="pricing_form.php?category_id=<?php echo (int)$row['id']; ?>" class="btn btn-outline-primary">Edit Category</a>
                                  <a href="pricing_form.php?new=1&category_id=<?php echo (int)$row['id']; ?>" class="btn btn-outline-secondary">Add/Update Items</a>
                                </div>
                              </td>
                            </tr>
                          <?php endwhile; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="text-muted">No pricing categories found. Create categories and catalog items first.</div>
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

      const categorySelect = document.getElementById('pricing-category-select');
      const itemsListContainer = document.getElementById('pricing-items-list');
      const selectedItemsContainer = document.getElementById('selected-items-container');
      const selectedPricingData = <?php echo json_encode($selectedPricingData); ?>;
      const catalogItems = <?php echo json_encode($catalogItems); ?>;

      function slugify(value) {
        return value.toString().trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
      }

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }

      function createThresholdRow(itemId, minQuantity = '', price = '') {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 threshold-row align-items-end';
        row.dataset.itemId = itemId;
        row.innerHTML = `
          <div class="col-5">
            <label class="form-label visually-hidden">Min Quantity</label>
            <input type="number" step="any" name="thresholds[${itemId}][min_quantity][]" class="form-control" min="0.01" placeholder="Min qty" value="${escapeHtml(minQuantity)}">
          </div>
          <div class="col-5">
            <label class="form-label visually-hidden">Tier Price</label>
            <input type="number" step="0.01" name="thresholds[${itemId}][price][]" class="form-control" min="0" placeholder="Tier price" value="${escapeHtml(price)}">
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-outline-danger btn-sm remove-threshold-row">Remove</button>
          </div>
        `;
        return row;
      }

      function createItemBlockFromData(item) {
        const itemId = String(item.id);
        const itemName = item.item_name || item.item_name || '';
        const categoryName = item.category_name || '';
        const defaultUnit = item.unit_label || '';
        const existingData = selectedPricingData[itemId] || {};
        const unitValue = existingData.unit_label || defaultUnit;
        const autoSlug = slugify(`${categoryName} ${itemName} ${unitValue}`.trim());
        const slugValue = existingData.slug || autoSlug;
        const priceValue = existingData.price || '';

        const card = document.createElement('div');
        card.className = 'card mb-3 selected-item-card';
        card.dataset.itemId = itemId;
        card.innerHTML = `
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="card-title mb-1">${escapeHtml(itemName)}</h5>
                <div class="text-muted">Category: ${escapeHtml(categoryName)}</div>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm remove-item-btn">Remove</button>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Unit Label</label>
                <input type="text" name="unit_label[${itemId}]" class="form-control item-unit-input" value="${escapeHtml(unitValue)}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Slug</label>
                <input type="text" name="slug[${itemId}]" class="form-control item-slug-input" value="${escapeHtml(slugValue)}" placeholder="${escapeHtml(autoSlug)}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price[${itemId}]" class="form-control" min="0" value="${escapeHtml(priceValue)}">
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label">Tiered Pricing</label>
              <div class="thresholds-list"></div>
              <button type="button" class="btn btn-sm btn-outline-primary add-threshold-row">Add Tier</button>
            </div>
          </div>
        `;

        const unitInput = card.querySelector('.item-unit-input');
        const slugInput = card.querySelector('.item-slug-input');
        const thresholdsList = card.querySelector('.thresholds-list');
        const addThresholdButton = card.querySelector('.add-threshold-row');

        slugInput.dataset.autoSlug = autoSlug;
        if (!existingData.slug) {
          slugInput.value = autoSlug;
        }

        unitInput.addEventListener('input', function () {
          const nextAutoSlug = slugify(`${categoryName} ${itemName} ${unitInput.value}`.trim());
          if (slugInput.value === slugInput.dataset.autoSlug) {
            slugInput.value = nextAutoSlug;
          }
          slugInput.dataset.autoSlug = nextAutoSlug;
        });

        addThresholdButton.addEventListener('click', function () {
          thresholdsList.appendChild(createThresholdRow(itemId));
        });

        thresholdsList.addEventListener('click', function (event) {
          if (!event.target.classList.contains('remove-threshold-row')) return;
          const row = event.target.closest('.threshold-row');
          if (row) {
            row.remove();
          }
        });

        const thresholds = Array.isArray(existingData.thresholds) ? existingData.thresholds : [];
        if (thresholds.length) {
          thresholds.forEach(function (threshold) {
            thresholdsList.appendChild(createThresholdRow(itemId, threshold.min_quantity || '', threshold.price || ''));
          });
        } else {
          thresholdsList.appendChild(createThresholdRow(itemId));
        }

        card.querySelector('.remove-item-btn').addEventListener('click', function () {
          const checkbox = itemsListContainer.querySelector(`input[type=checkbox][value="${itemId}"]`);
          if (checkbox) checkbox.checked = false;
          renderSelectedItems();
        });

        return card;
      }

      function renderSelectedItems() {
        if (!selectedItemsContainer || !itemsListContainer) return;
        const checkedBoxes = Array.from(itemsListContainer.querySelectorAll('input[type=checkbox]:checked'));
        const selectedIds = checkedBoxes.map(cb => cb.value);
        const existingBlocks = Array.from(selectedItemsContainer.querySelectorAll('.selected-item-card'));

        existingBlocks.forEach(function (block) {
          if (!selectedIds.includes(block.dataset.itemId)) {
            block.remove();
          }
        });

        selectedIds.forEach(function (itemId) {
          const exists = selectedItemsContainer.querySelector(`.selected-item-card[data-item-id="${itemId}"]`);
          if (!exists) {
            const item = catalogItems.find(ci => String(ci.id) === String(itemId));
            if (item) {
              selectedItemsContainer.appendChild(createItemBlockFromData(item));
            }
          }
        });

        if (selectedIds.length === 0) {
          selectedItemsContainer.innerHTML = '<div class="text-muted">Choose one or more items to configure pricing.</div>';
        }
      }

      function renderItemsForCategory() {
        if (!categorySelect || !itemsListContainer) return;
        const selectedCategoryId = parseInt(categorySelect.value || '0', 10);
        itemsListContainer.innerHTML = '';
        const filtered = catalogItems.filter(ci => !selectedCategoryId || parseInt(ci.category_id, 10) === selectedCategoryId);
        if (filtered.length === 0) {
          itemsListContainer.innerHTML = '<div class="text-muted p-2">No items for selected category.</div>';
          return;
        }
        filtered.forEach(function (item) {
          const id = String(item.id);
          const checked = <?php echo json_encode($selectedItemIds); ?>.includes(parseInt(item.id,10)) ? 'checked' : '';
          const div = document.createElement('div');
          div.className = 'form-check';
          div.innerHTML = `<input class="form-check-input" type="checkbox" value="${escapeHtml(id)}" id="pricing_item_${escapeHtml(id)}" ${checked} name="item_ids[]">
                           <label class="form-check-label" for="pricing_item_${escapeHtml(id)}">${escapeHtml(item.item_name)} <small class="text-muted">(${escapeHtml(item.unit_label || '')})</small></label>`;
          itemsListContainer.appendChild(div);
        });

        // attach change handler
        Array.from(itemsListContainer.querySelectorAll('input[type=checkbox]')).forEach(function (cb) {
          cb.addEventListener('change', function () {
            renderSelectedItems();
          });
        });
      }

      if (categorySelect && itemsListContainer) {
        categorySelect.addEventListener('change', function () {
          renderItemsForCategory();
          renderSelectedItems();
        });

        // initial render
        renderItemsForCategory();
        renderSelectedItems();
      }
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
