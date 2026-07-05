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
$toggleId = isset($_GET['toggle']) ? (int)$_GET['toggle'] : 0;
$showForm = isset($_GET['new']) || $menuId > 0;
$showList = !$showForm;
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

if ($toggleId) {
  $stmt = $conn->prepare('UPDATE menu_items SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?');
  $stmt->bind_param('i', $toggleId);
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

$formValues = [
    'id' => 0,
    'label' => '',
    'link' => '',
    'menu_order' => 0,
    'is_active' => 1,
    'has_dropdown' => 0,
];

if ($item) {
    $formValues = array_merge($formValues, $item);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Manager</title>
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
            <h3 class="fw-bold mb-1">Header Menu Manager</h3>
            <p class="text-muted mb-0">Control top-level menu items and dropdown content.</p>
          </div>
          <div class="d-flex gap-2">
            <?php if (!$showForm): ?>
              <a href="menu_form.php?new=1" class="btn btn-primary">Add Menu Item</a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
          </div>
        </div>
        <div class="row g-4">
          <?php if ($showForm): ?>
          <div class="col-12">
            <div class="card admin-card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="mb-0"><?php echo $menuId ? 'Edit Menu Item' : 'Add Menu Item'; ?></h4>
                  <?php if ($showForm): ?>
                    <a href="menu_form.php" class="btn btn-outline-secondary btn-sm">Back to Menu List</a>
                  <?php endif; ?>
                </div>
                <?php if (!empty($success)): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="post">
                  <input type="hidden" name="id" value="<?php echo (int)($formValues['id'] ?? 0); ?>">
                  <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control" value="<?php echo htmlspecialchars($formValues['label'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($formValues['link'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Order</label>
                    <input type="number" name="menu_order" class="form-control" value="<?php echo (int)($formValues['menu_order'] ?? 0); ?>">
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo (!empty($formValues['is_active']) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Visible in header</label>
                  </div>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_dropdown" value="1" <?php echo (!empty($formValues['has_dropdown']) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Show dropdown on hover</label>
                  </div>
                  <button type="submit" class="btn btn-primary">Save</button>
                </form>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($showList): ?>
          <div class="col-12">
            <div class="card admin-card">
              <div class="card-body">
                <?php if ($items && $items->num_rows > 0): ?>
                  <?php while ($row = $items->fetch_assoc()): ?>
                    <div class="card mb-3 shadow-sm">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                          <div>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['label']); ?></h5>
                            <p class="small text-muted mb-2"><?php echo htmlspecialchars($row['link']); ?></p>
                            <div class="d-flex flex-wrap gap-2">
                              <?php echo !empty($row['is_active']) ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Hidden</span>'; ?>
                              <?php if (!empty($row['has_dropdown'])): ?><span class="badge bg-info text-dark">Dropdown</span><?php endif; ?>
                            </div>
                          </div>
                          <div class="btn-group btn-group-sm" role="group">
                            <a href="menu_form.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <?php if (!empty($row['is_active'])): ?>
                              <a href="menu_form.php?toggle=<?php echo (int)$row['id']; ?>" class="btn btn-outline-secondary" title="Hide"><i class="bi bi-eye"></i></a>
                            <?php else: ?>
                              <a href="menu_form.php?toggle=<?php echo (int)$row['id']; ?>" class="btn btn-outline-secondary" title="Show"><i class="bi bi-eye-slash"></i></a>
                            <?php endif; ?>
                            <a href="menu_form.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this menu item?');"><i class="bi bi-trash"></i></a>
                          </div>
                        </div>
                        <?php
                        $childRows = $conn->query('SELECT id, label, link FROM menu_children WHERE parent_id = ' . (int)$row['id'] . ' ORDER BY menu_order ASC, id ASC');
                        if ($childRows && $childRows->num_rows > 0):
                        ?>
                          <div class="mt-3">
                            <h6 class="mb-3">Added Sub-Menu</h6>
                            <div class="row g-2">
                              <?php 
                              $childRows->data_seek(0);
                              while ($childRow = $childRows->fetch_assoc()): 
                              ?>
                                <div class="col-md-4 col-sm-6">
                                  <div class="border rounded p-3 bg-light submenu-item-box position-relative">
                                    <div class="small fw-semibold mb-1"><?php echo htmlspecialchars($childRow['label']); ?></div>
                                    <div class="small text-muted text-break mb-2"><?php echo htmlspecialchars($childRow['link']); ?></div>
                                    <a href="menu_form.php?child_delete=<?php echo (int)$childRow['id']; ?>" class="btn btn-link btn-sm text-danger p-0 position-absolute top-0 end-0 m-2" onclick="return confirm('Remove this submenu item?');" title="Delete"><i class="bi bi-trash"></i></a>
                                  </div>
                                </div>
                              <?php endwhile; ?>
                            </div>
                          </div>
                        <?php endif; ?>
                        <div class="mt-3 border rounded p-3 bg-light submenu-dropzone-wrapper">
                          <button class="btn btn-sm btn-outline-secondary w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#submenuPanel<?php echo (int)$row['id']; ?>" aria-expanded="false">
                            <span>Drag pages/services to set as sub-menu</span>
                            <i class="bi bi-chevron-down"></i>
                          </button>
                          <div class="collapse mt-3" id="submenuPanel<?php echo (int)$row['id']; ?>">
                            <p class="small text-muted mb-3">Pick a page or service from the lists below and drop it onto this parent menu to create a submenu item.</p>
                            <div class="row g-2">
                              <?php
                              // Get existing submenu links for this parent
                              $existingLinks = [];
                              $existingRows = $conn->query('SELECT link FROM menu_children WHERE parent_id = ' . (int)$row['id']);
                              if ($existingRows && $existingRows->num_rows > 0) {
                                  while ($existingRow = $existingRows->fetch_assoc()) {
                                      $existingLinks[] = $existingRow['link'];
                                  }
                              }

                              $pageOptions = $conn->query('SELECT slug, title FROM pages ORDER BY menu_order ASC, id ASC');
                              if ($pageOptions && $pageOptions->num_rows > 0):
                                  while ($pageOption = $pageOptions->fetch_assoc()):
                                      $pageLink = 'page.php?slug=' . $pageOption['slug'];
                                      // Skip if already in submenu
                                      if (in_array($pageLink, $existingLinks)) {
                                          continue;
                                      }
                              ?>
                                <div class="col-md-6">
                                  <div class="border rounded p-2 bg-white page-draggable" draggable="true" data-label="<?php echo htmlspecialchars($pageOption['title']); ?>" data-link="<?php echo htmlspecialchars($pageLink); ?>" style="cursor: grab;">
                                    <div class="small fw-semibold"><?php echo htmlspecialchars($pageOption['title']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($pageLink); ?></div>
                                  </div>
                                </div>
                              <?php
                                  endwhile;
                              endif;
                              $serviceOptions = $conn->query('SELECT slug, title FROM services ORDER BY display_order ASC, id ASC');
                              if ($serviceOptions && $serviceOptions->num_rows > 0):
                                  while ($serviceOption = $serviceOptions->fetch_assoc()):
                                      $serviceLink = 'service.php?slug=' . $serviceOption['slug'];
                                      // Skip if already in submenu
                                      if (in_array($serviceLink, $existingLinks)) {
                                          continue;
                                      }
                              ?>
                                <div class="col-md-6">
                                  <div class="border rounded p-2 bg-white page-draggable" draggable="true" data-label="<?php echo htmlspecialchars($serviceOption['title']); ?>" data-link="<?php echo htmlspecialchars($serviceLink); ?>" style="cursor: grab;">
                                    <div class="small fw-semibold"><?php echo htmlspecialchars($serviceOption['title']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($serviceLink); ?></div>
                                  </div>
                                </div>
                              <?php
                                  endwhile;
                              endif;
                              if ((!$pageOptions || $pageOptions->num_rows === 0) && (!$serviceOptions || $serviceOptions->num_rows === 0)):
                              ?>
                                <div class="col-12">
                                  <div class="small text-muted">No pages or services available yet. Create one first.</div>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="submenu-dropzone border border-dashed rounded p-3 mt-3 text-center text-muted" tabindex="0" role="button">
                              <i class="bi bi-arrow-down-circle"></i>
                              <div class="small fw-semibold mt-1">Drop page or service here to assign as sub-menu</div>
                            </div>
                            <form method="post" class="mt-3">
                              <input type="hidden" name="child_submit" value="1">
                              <input type="hidden" name="child_parent_id" value="<?php echo (int)$row['id']; ?>">
                              <input type="hidden" name="child_label" value="">
                              <input type="hidden" name="child_link" value="">
                              <input type="hidden" name="child_order" value="0">
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <div class="text-muted">No menu items found. Add a new top-level link to get started.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
    </main>
  </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (button) {
    const targetSelector = button.getAttribute('data-bs-target');
    if (!targetSelector) {
      return;
    }

    const target = document.querySelector(targetSelector);
    if (!target) {
      return;
    }

    button.addEventListener('click', function (event) {
      event.preventDefault();
      const isExpanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!isExpanded));
      target.classList.toggle('show', !isExpanded);

      const icon = button.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-chevron-down', isExpanded);
        icon.classList.toggle('bi-chevron-up', !isExpanded);
      }
    });
  });

  document.querySelectorAll('.page-draggable').forEach(function (card) {
    card.addEventListener('dragstart', function (event) {
      event.dataTransfer.setData('text/plain', JSON.stringify({
        label: this.dataset.label,
        link: this.dataset.link
      }));
      event.dataTransfer.effectAllowed = 'copy';
    });
  });

  document.querySelectorAll('.submenu-dropzone').forEach(function (zone) {
    const wrapper = zone.closest('.submenu-dropzone-wrapper');
    const form = wrapper ? wrapper.querySelector('form') : null;

    if (!form) {
      return;
    }

    const applyDrop = function (data) {
      form.querySelector('[name="child_label"]').value = data.label || '';
      form.querySelector('[name="child_link"]').value = data.link || '';
      form.submit();
    };

    zone.addEventListener('dragover', function (event) {
      event.preventDefault();
      this.classList.add('border-primary', 'bg-light');
    });

    zone.addEventListener('dragleave', function () {
      this.classList.remove('border-primary', 'bg-light');
    });

    zone.addEventListener('drop', function (event) {
      event.preventDefault();
      this.classList.remove('border-primary', 'bg-light');
      try {
        const data = JSON.parse(event.dataTransfer.getData('text/plain') || '{}');
        if (data && data.label) {
          applyDrop(data);
        }
      } catch (error) {
        console.error(error);
      }
    });
  });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
