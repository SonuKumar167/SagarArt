<?php
require 'includes/config.php';

$siteSettings = getSiteSettings($conn);
$pricingItems = getPricingItems($conn);
$groupedPricingItems = [];
foreach ($pricingItems as $item) {
    $groupedPricingItems[$item['category']][] = $item;
}
$initialCategory = array_key_first($groupedPricingItems) ?: '';
$initialItems = $initialCategory && isset($groupedPricingItems[$initialCategory]) ? $groupedPricingItems[$initialCategory] : [];
$initialItem = $initialItems[0] ?? null;
$pageTitle = 'Pricing Calculator - ' . ($siteSettings['site_name'] ?? 'Sagar Art');
$pageDescription = 'Estimate your print and signage cost instantly with our pricing calculator.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <?php if (!empty($siteSettings['favicon_url'])): ?><link rel="icon" href="<?php echo htmlspecialchars($siteSettings['favicon_url']); ?>"><?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <section class="section-shell">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-6">
          <h1 class="display-5 fw-bold">Pricing Calculator</h1>
          <p class="lead text-muted">Select a product, choose quantity, and get a fast estimate for your print or signage order.</p>
          <div class="card section-card mt-4">
            <div class="card-body">
              <form id="pricing-calculator">
                <div class="mb-3">
                  <label for="pricing-category" class="form-label">Product Category</label>
                  <select id="pricing-category" class="form-select" required>
                    <?php foreach ($groupedPricingItems as $category => $items): ?>
                      <option value="<?php echo htmlspecialchars($category); ?>"<?php echo $category === $initialCategory ? ' selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="pricing-item" class="form-label">Product Item</label>
                  <select id="pricing-item" class="form-select" required>
                    <?php foreach ($initialItems as $item): ?>
                      <option value="<?php echo htmlspecialchars($item['slug']); ?>"
                        data-price="<?php echo htmlspecialchars($item['price']); ?>"
                        data-threshold-quantity="<?php echo (int)$item['threshold_quantity']; ?>"
                        data-threshold-price="<?php echo htmlspecialchars($item['threshold_price']); ?>"
                        data-unit="<?php echo htmlspecialchars($item['unit_label']); ?>">
                        <?php echo htmlspecialchars($item['item_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-12">
                    <label class="form-label">Quantity / Size</label>
                    <div id="unit-controls">
                      <input id="pricing-quantity" type="number" min="1" value="1" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-6 d-flex align-items-end">
                    <button id="add-item-button" type="button" class="btn btn-primary w-100">Add Item</button>
                  </div>
                </div>

                <div class="table-responsive mb-4">
                  <table class="table table-hover align-middle">
                    <thead>
                      <tr>
                        <th>Item</th>
                        <th>Qty/Sqft</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody id="pricing-cart-body">
                      <tr class="text-center text-muted">
                        <td colspan="5">No items added yet.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Grand Total</span>
                    <strong id="pricing-total">₹ 0.00</strong>
                  </div>
                </div>

                <div class="alert alert-info small mb-0">Use this calculator as an estimate. Final pricing may vary based on custom requirements.</div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card section-card h-100">
            <div class="card-body">
              <h2 class="h4 mb-3">Available Price Items</h2>
              <div class="mb-3">
                <input id="pricing-list-search" type="text" class="form-control" placeholder="Search available items">
              </div>
              <?php if (!empty($groupedPricingItems)): ?>
                <div class="available-pricing-scroll">
                  <?php foreach ($groupedPricingItems as $category => $items): ?>
                    <div class="mb-4 category-block">
                      <h3 class="h6 mb-2 text-secondary"><?php echo htmlspecialchars($category); ?></h3>
                      <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                          <thead>
                            <tr>
                              <th>Item</th>
                              <th class="text-end">Price</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($items as $item): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?><?php if (!empty($item['unit_label'])): ?> <span class="text-muted">(<?php echo htmlspecialchars($item['unit_label']); ?>)</span><?php endif; ?></td>
                                <td class="text-end">₹ <?php echo number_format($item['price'], 2); ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="text-muted">No pricing items are available yet. Add them through the admin pricing manager.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const pricingGroups = <?php echo json_encode($groupedPricingItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const categorySelect = document.getElementById('pricing-category');
    const itemSelect = document.getElementById('pricing-item');
    const quantityInput = document.getElementById('pricing-quantity');
    const totalEl = document.getElementById('pricing-total');

    const addItemButton = document.getElementById('add-item-button');
    const cartBody = document.getElementById('pricing-cart-body');

    // Clear persisted cart on page load so refresh does not preserve added items
    localStorage.removeItem('pricingCalculatorCart');

    function getSelectedItem() {
      const itemSlug = itemSelect.value;
      const category = categorySelect.value;
      const items = pricingGroups[category] || [];
      return items.find(item => item.slug === itemSlug) || items[0] || null;
    }

    function renderItems() {
      const selectedCategory = categorySelect.value;
      const items = pricingGroups[selectedCategory] || [];
      itemSelect.innerHTML = '';
      items.forEach((item, index) => {
        const option = document.createElement('option');
        option.value = item.slug;
        option.textContent = item.item_name;
        option.dataset.price = item.price;
        option.dataset.thresholdQuantity = item.threshold_quantity || 0;
        option.dataset.thresholdPrice = item.threshold_price || 0;
        option.dataset.unit = item.unit_label;
        if (index === 0) option.selected = true;
        itemSelect.appendChild(option);
      });
      updateUnitControls();
    }

    function isAreaUnit(unit) {
      if (!unit) return false;
      const u = unit.toString().toLowerCase();
      return u.includes('square') || u.includes('sq') || u.includes('sqft') || u.includes('ft') || u.includes('feet');
    }

    function updateUnitControls() {
      const selected = getSelectedItem();
      const unit = selected ? (selected.unit_label || '') : '';
      const container = document.getElementById('unit-controls');
      container.innerHTML = '';

      if (isAreaUnit(unit)) {
        container.innerHTML = `
          <div class="row g-2">
            <div class="col-6">
              <input id="dimension-length" type="number" min="0" step="0.01" class="form-control" placeholder="Length (ft)">
            </div>
            <div class="col-6">
              <input id="dimension-breadth" type="number" min="0" step="0.01" class="form-control" placeholder="Breadth (ft)">
            </div>
            <div class="col-12 mt-2">
              <div class="form-text">Computed area will be used as quantity (Length × Breadth).</div>
            </div>
          </div>
        `;
      } else {
        container.innerHTML = `<input id="pricing-quantity" type="number" min="1" value="1" class="form-control" required>`;
      }
    }

    function getCartItems() {
      const cartData = localStorage.getItem('pricingCalculatorCart');
      return cartData ? JSON.parse(cartData) : [];
    }

    function saveCartItems(items) {
      localStorage.setItem('pricingCalculatorCart', JSON.stringify(items));
    }

    function formatCurrency(value) {
      return '₹ ' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getItemUnitPrice(item, quantity) {
      const basePrice = Number(item.price || 0);
      let bestPrice = basePrice;
      const thresholds = Array.isArray(item.thresholds) ? item.thresholds : [];
      thresholds.forEach(threshold => {
        const minQuantity = Number(threshold.min_quantity || 0);
        const thresholdPrice = Number(threshold.price || 0);
        if (minQuantity > 0 && quantity >= minQuantity && thresholdPrice > 0) {
          bestPrice = thresholdPrice;
        }
      });
      if (bestPrice === basePrice && item.threshold_quantity > 0 && item.threshold_price > 0 && quantity >= item.threshold_quantity) {
        bestPrice = Number(item.threshold_price);
      }
      return bestPrice;
    }

    function renderCart() {
      const items = getCartItems();
      cartBody.innerHTML = '';
      if (!items.length) {
        cartBody.innerHTML = '<tr class="text-center text-muted"><td colspan="5">No items added yet.</td></tr>';
        updateTotal();
        return;
      }

      items.forEach((cartItem, index) => {
        const liveUnitPrice = getItemUnitPrice(cartItem, cartItem.quantity);
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${cartItem.item_name}</td>
          <td><input type="number" min="0.01" step="any" value="${cartItem.quantity}" class="form-control form-control-sm cart-quantity" data-index="${index}"></td>
          <td class="text-end">${formatCurrency(liveUnitPrice)}</td>
          <td class="text-end">${formatCurrency(liveUnitPrice * cartItem.quantity)}</td>
          <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-cart-item" data-index="${index}">Remove</button></td>
        `;
        cartBody.appendChild(row);
      });
      updateTotal();
    }

    function updateTotal() {
      const items = getCartItems();
      const total = items.reduce((sum, item) => {
        const unit = getItemUnitPrice(item, item.quantity);
        return sum + unit * item.quantity;
      }, 0);
      totalEl.textContent = formatCurrency(total);
    }

    function addItemToCart() {
      const item = getSelectedItem();
      if (!item) return;
      let quantity = 1;
      const unit = item.unit_label || '';
      if (isAreaUnit(unit)) {
        const lengthEl = document.getElementById('dimension-length');
        const breadthEl = document.getElementById('dimension-breadth');
        const length = Math.max(0, Number(lengthEl ? lengthEl.value : 0) || 0);
        const breadth = Math.max(0, Number(breadthEl ? breadthEl.value : 0) || 0);
        quantity = parseFloat((length * breadth).toFixed(4)) || 0;
        if (quantity <= 0) {
          alert('Please enter valid length and breadth to compute area.');
          return;
        }
      } else {
        const qEl = document.getElementById('pricing-quantity');
        quantity = Math.max(1, Number(qEl ? qEl.value : 1) || 1);
      }
      const items = getCartItems();
      const currentUnitPrice = getItemUnitPrice(item, quantity);
      const cartItemData = {
        slug: item.slug,
        category: item.category,
        item_name: item.item_name,
        price: Number(item.price),
        unit_price: Number(currentUnitPrice),
        thresholds: Array.isArray(item.thresholds) ? item.thresholds : [],
        threshold_quantity: Number(item.threshold_quantity || 0),
        threshold_price: Number(item.threshold_price || 0),
        unit_label: item.unit_label,
        quantity: quantity
      };

      // Merge only when the same slug AND same unit price (so multiple tier variants can be added separately)
      const existingIndex = items.findIndex(cartItem => cartItem.slug === item.slug && Number(cartItem.unit_price || cartItem.price) === cartItemData.unit_price);
      if (existingIndex >= 0) {
        items[existingIndex].quantity = Number(items[existingIndex].quantity || 0) + Number(quantity);
      } else {
        items.push(cartItemData);
      }

      saveCartItems(items);
      renderCart();
    }

    function removeItemFromCart(index) {
      const items = getCartItems();
      items.splice(index, 1);
      saveCartItems(items);
      renderCart();
    }

    categorySelect.addEventListener('change', () => {
      renderItems();
    });

    itemSelect.addEventListener('change', () => {
      updateUnitControls();
    });

    addItemButton.addEventListener('click', () => {
      addItemToCart();
    });

    cartBody.addEventListener('change', (event) => {
      if (!event.target.classList.contains('cart-quantity')) return;
      const index = Number(event.target.dataset.index);
      const quantity = Math.max(0.01, Number(event.target.value) || 0.01);
      const items = getCartItems();
      if (items[index]) {
        items[index].quantity = quantity;
        items[index].unit_price = getItemUnitPrice(items[index], quantity);
        saveCartItems(items);
        renderCart();
      }
    });

    cartBody.addEventListener('click', (event) => {
      if (!event.target.classList.contains('remove-cart-item')) return;
      const index = Number(event.target.dataset.index);
      removeItemFromCart(index);
    });

    const pricingListSearch = document.getElementById('pricing-list-search');
    if (pricingListSearch) {
      pricingListSearch.addEventListener('input', () => {
        const query = pricingListSearch.value.trim().toLowerCase();
        document.querySelectorAll('.available-pricing-scroll .category-block').forEach(categoryBlock => {
          let rowMatches = false;
          categoryBlock.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.trim().toLowerCase();
            const matches = text.includes(query);
            row.style.display = matches ? '' : 'none';
            if (matches) {
              rowMatches = true;
            }
          });
          categoryBlock.style.display = rowMatches ? '' : 'none';
        });
      });
    }

    renderItems();
    renderCart();
  </script>
</body>
</html>
<?php $conn->close(); ?>
