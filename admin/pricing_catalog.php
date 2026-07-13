<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$editCategoryId = isset($_GET['edit_category']) ? (int)$_GET['edit_category'] : 0;
$editItemId = isset($_GET['edit_item']) ? (int)$_GET['edit_item'] : 0;
$deleteCategoryId = isset($_GET['delete_category']) ? (int)$_GET['delete_category'] : 0;
$deleteItemId = isset($_GET['delete_item']) ? (int)$_GET['delete_item'] : 0;

if ($deleteCategoryId > 0) {
    $stmt = $conn->prepare('DELETE FROM pricing_categories WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $deleteCategoryId);
    $stmt->execute();
    $message = 'Category deleted successfully.';
}

if ($deleteItemId > 0) {
    $stmt = $conn->prepare('DELETE FROM pricing_catalog_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $deleteItemId);
    $stmt->execute();
    $message = 'Item deleted successfully.';
}

$editingCategory = null;
if ($editCategoryId > 0) {
    $stmt = $conn->prepare('SELECT id, name, slug, sort_order, is_active FROM pricing_categories WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editCategoryId);
    $stmt->execute();
    $editingCategory = $stmt->get_result()->fetch_assoc();
}

$editingItem = null;
if ($editItemId > 0) {
    $stmt = $conn->prepare('SELECT id, category_id, item_name, slug, description, unit_label, sort_order, is_active FROM pricing_catalog_items WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editItemId);
    $stmt->execute();
    $editingItem = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['category_form'])) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        if ($name === '') {
            $error = 'Category name is required.';
        } else {
            if ($categoryId > 0) {
                $stmt = $conn->prepare('UPDATE pricing_categories SET name = ?, slug = ?, sort_order = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('ssiii', $name, $slug, $sortOrder, $isActive, $categoryId);
                $stmt->execute();
                $message = 'Category updated successfully.';
            } else {
                $stmt = $conn->prepare('INSERT INTO pricing_categories (name, slug, sort_order, is_active) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssii', $name, $slug, $sortOrder, $isActive);
                $stmt->execute();
                $message = 'Category created successfully.';
            }
            $editCategoryId = 0;
            $editingCategory = null;
        }
    }

    if (isset($_POST['item_form'])) {
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $itemName = trim($_POST['item_name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($itemName);
        $description = trim($_POST['description'] ?? '');
        $unitLabel = trim($_POST['unit_label'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

        if ($categoryId <= 0 || $itemName === '') {
            $error = 'Please select a category and provide an item name.';
        } else {
            if ($itemId > 0) {
                $stmt = $conn->prepare('UPDATE pricing_catalog_items SET category_id = ?, item_name = ?, slug = ?, description = ?, unit_label = ?, sort_order = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('issssiii', $categoryId, $itemName, $slug, $description, $unitLabel, $sortOrder, $isActive, $itemId);
                $stmt->execute();
                $message = 'Item updated successfully.';
            } else {
                $stmt = $conn->prepare('INSERT INTO pricing_catalog_items (category_id, item_name, slug, description, unit_label, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('issssii', $categoryId, $itemName, $slug, $description, $unitLabel, $sortOrder, $isActive);
                $stmt->execute();
                $message = 'Item created successfully.';
            }
            $editItemId = 0;
            $editingItem = null;
        }
    }
}

$categoriesResult = $conn->query('SELECT id, name, slug, sort_order, is_active FROM pricing_categories ORDER BY sort_order ASC, id ASC');
$categories = $categoriesResult ? $categoriesResult->fetch_all(MYSQLI_ASSOC) : [];

$itemsResult = $conn->query('SELECT pci.id, pci.category_id, pci.item_name, pci.slug, pci.description, pci.unit_label, pci.sort_order, pci.is_active, pc.name AS category_name FROM pricing_catalog_items pci JOIN pricing_categories pc ON pc.id = pci.category_id ORDER BY pc.sort_order ASC, pc.id ASC, pci.sort_order ASC, pci.id ASC');
$items = $itemsResult ? $itemsResult->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Product Catalog</title>
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
            <h3 class="fw-bold mb-1">Product Catalog Manager</h3>
            <p class="text-muted mb-0">Create product categories and items that appear in the pricing form dropdowns.</p>
          </div>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row g-4">
          <div class="col-lg-6">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3"><?php echo $editingCategory ? 'Edit Category' : 'Add Category'; ?></h4>
                <form method="post">
                  <input type="hidden" name="category_form" value="1">
                  <input type="hidden" name="category_id" value="<?php echo (int)($editingCategory['id'] ?? 0); ?>">
                  <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="category-name" name="name" class="form-control" value="<?php echo htmlspecialchars($editingCategory['name'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" id="category-slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($editingCategory['slug'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($editingCategory['sort_order'] ?? 0); ?>">
                  </div>
                  <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="category-active" value="1" <?php echo !empty($editingCategory['is_active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="category-active">Active</label>
                  </div>
                  <button type="submit" class="btn btn-primary"><?php echo $editingCategory ? 'Save Category' : 'Create Category'; ?></button>
                  <?php if ($editingCategory): ?>
                    <a href="pricing_catalog.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3"><?php echo $editingItem ? 'Edit Item' : 'Add Item'; ?></h4>
                <form method="post">
                  <input type="hidden" name="item_form" value="1">
                  <input type="hidden" name="item_id" value="<?php echo (int)($editingItem['id'] ?? 0); ?>">
                  <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                      <option value="">Select a category</option>
                      <?php foreach ($categories as $category): ?>
                        <option value="<?php echo (int)$category['id']; ?>" <?php echo (!empty($editingItem['category_id']) && (int)$editingItem['category_id'] === (int)$category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" id="item-name" name="item_name" class="form-control" value="<?php echo htmlspecialchars($editingItem['item_name'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" id="item-slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($editingItem['slug'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Unit Label</label>
                    <input type="text" name="unit_label" class="form-control" value="<?php echo htmlspecialchars($editingItem['unit_label'] ?? ''); ?>" placeholder="e.g. per sqft">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editingItem['description'] ?? ''); ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($editingItem['sort_order'] ?? 0); ?>">
                  </div>
                  <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="item-active" value="1" <?php echo !empty($editingItem['is_active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="item-active">Active</label>
                  </div>
                  <button type="submit" class="btn btn-primary"><?php echo $editingItem ? 'Save Item' : 'Create Item'; ?></button>
                  <?php if ($editingItem): ?>
                    <a href="pricing_catalog.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4 mt-1">
          <div class="col-12">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3">Categories</h4>
                <?php if (!empty($categories)): ?>
                  <div class="table-responsive">
                    <table class="table align-middle">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Slug</th>
                          <th>Sort</th>
                          <th>Status</th>
                          <th class="text-end">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($categories as $category): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo (int)$category['sort_order']; ?></td>
                            <td><?php echo !empty($category['is_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                            <td class="text-end">
                              <div class="btn-group btn-group-sm" role="group">
                                <a href="pricing_catalog.php?edit_category=<?php echo (int)$category['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                <a href="pricing_catalog.php?delete_category=<?php echo (int)$category['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this category and its items?');">Delete</a>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <p class="text-muted mb-0">No categories created yet.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card admin-card">
              <div class="card-body">
                <h4 class="mb-3">Items</h4>
                <?php if (!empty($items)): ?>
                  <div class="table-responsive">
                    <table class="table align-middle">
                      <thead>
                        <tr>
                          <th>Category</th>
                          <th>Item</th>
                          <th>Slug</th>
                          <th>Unit</th>
                          <th>Status</th>
                          <th class="text-end">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($items as $item): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['slug']); ?></td>
                            <td><?php echo htmlspecialchars($item['unit_label'] ?? '-'); ?></td>
                            <td><?php echo !empty($item['is_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                            <td class="text-end">
                              <div class="btn-group btn-group-sm" role="group">
                                <a href="pricing_catalog.php?edit_item=<?php echo (int)$item['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                <a href="pricing_catalog.php?delete_item=<?php echo (int)$item['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this item?');">Delete</a>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <p class="text-muted mb-0">No catalog items created yet.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script>
    (function() {
      function slugify(value) {
        return value
          .toString()
          .trim()
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
      }

      function bindAutoSlug(sourceId, slugId) {
        var source = document.getElementById(sourceId);
        var slug = document.getElementById(slugId);
        if (!source || !slug) {
          return;
        }

        var lastAutoValue = slugify(source.value || '');
        if (slug.value && slug.value !== lastAutoValue) {
          lastAutoValue = '';
        }

        source.addEventListener('input', function() {
          var generated = slugify(source.value || '');
          if (slug.value === '' || slug.value === lastAutoValue) {
            slug.value = generated;
            lastAutoValue = generated;
          }
        });
      }

      bindAutoSlug('category-name', 'category-slug');
      bindAutoSlug('item-name', 'item-slug');
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
