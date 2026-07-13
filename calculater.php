<?php
require 'includes/config.php';

$siteSettings = getSiteSettings($conn);

// Fetch active categories and their pricing items (use category_id when present, fallback to category name)
$groupedPricingItems = [];
$categoryResult = $conn->query('SELECT id, name FROM pricing_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
if ($categoryResult) {
  while ($cat = $categoryResult->fetch_assoc()) {
    $groupedPricingItems[$cat['name']] = [];
    $stmt = $conn->prepare('SELECT id, catalog_item_id, item_name, slug, description, unit_label, price, threshold_quantity, threshold_price FROM pricing_items WHERE (category_id = ? OR category = ?) AND is_active = 1 ORDER BY sort_order ASC, id ASC');
    $stmt->bind_param('is', $cat['id'], $cat['name']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      // load thresholds
      $thresholds = [];
      $tstmt = $conn->prepare('SELECT min_quantity, price FROM pricing_item_thresholds WHERE pricing_item_id = ? ORDER BY min_quantity ASC');
      $tstmt->bind_param('i', $row['id']);
      $tstmt->execute();
      $tres = $tstmt->get_result();
      while ($t = $tres->fetch_assoc()) {
        $thresholds[] = [
          'min_quantity' => (float)$t['min_quantity'],
          'price' => (float)$t['price'],
        ];
      }
      $row['thresholds'] = $thresholds;
      $groupedPricingItems[$cat['name']][] = $row;
    }
  }
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
                  <label for="pricing-item" class="form-label">Product Item(s)</label>
                  <select id="pricing-item" class="form-select" multiple size="6" required>
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
                  <div class="form-text">Hold Ctrl/Cmd to select multiple items.</div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-12">
                    <label class="form-label">Quantity / Dimensions</label>
                    <div id="quantity-controls"></div>
                  </div>
                  <div class="col-md-12">
                    <div id="dimension-suggestions" class="mt-2"></div>
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

                <div class="mb-3 d-flex gap-2">
                  <button id="print-cart-button" type="button" class="btn btn-outline-secondary">Print Cart</button>
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
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($items as $item): ?>
                              <tr>
                                <td>
                                  <?php echo htmlspecialchars($item['item_name']); ?><?php if (!empty($item['unit_label'])): ?> <span class="text-muted">(<?php echo htmlspecialchars($item['unit_label']); ?>)</span><?php endif; ?>
                                </td>
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
    const quantityControls = document.getElementById('quantity-controls');
    const suggestionContainer = document.getElementById('dimension-suggestions');
    const totalEl = document.getElementById('pricing-total');

    const addItemButton = document.getElementById('add-item-button');
    const cartBody = document.getElementById('pricing-cart-body');

    // Clear persisted cart on page load so refresh does not preserve added items
    localStorage.removeItem('pricingCalculatorCart');

    function getSelectedItems() {
      const selectedCategory = categorySelect.value;
      const items = pricingGroups[selectedCategory] || [];
      const selectedSlugs = Array.from(itemSelect.selectedOptions).map(option => option.value);
      return items.filter(item => selectedSlugs.includes(item.slug));
    }

    function getSelectedItem() {
      return getSelectedItems()[0] || null;
    }

    function renderItems() {
      const selectedCategory = categorySelect.value;
      const items = pricingGroups[selectedCategory] || [];
      itemSelect.innerHTML = '';
      items.forEach(item => {
        const option = document.createElement('option');
        option.value = item.slug;
        option.textContent = item.item_name;
        option.dataset.price = item.price;
        option.dataset.thresholdQuantity = item.threshold_quantity || 0;
        option.dataset.thresholdPrice = item.threshold_price || 0;
        option.dataset.unit = item.unit_label;
        itemSelect.appendChild(option);
      });
      if (itemSelect.options.length > 0) {
        itemSelect.options[0].selected = true;
      }
      updateUnitControls();
    }

    function isAreaUnit(unit) {
      if (!unit) return false;
      const u = unit.toString().toLowerCase().replace(/\s+/g, ' ').trim();
      return u === 'sq ft' || u === 'sqft' || u === 'square feet' || u === 'square foot' || u === 'square ft';
    }

    function getSelectedUnitLabel() {
      const selectedItems = getSelectedItems();
      if (selectedItems.length === 0) {
        return '';
      }
      const labels = Array.from(new Set(selectedItems.map(item => item.unit_label || ''))).filter(Boolean);
      return labels.length === 1 ? labels[0] : labels.join(', ');
    }

    function getSelectedUnitMode() {
      const modeSelect = document.getElementById('dimension-unit-mode');
      return modeSelect ? modeSelect.value : 'ft';
    }

    function toSquareFeet(length, breadth, mode) {
      if (!length || !breadth) return 0;
      const lengthFt = mode === 'in' ? length / 12 : length;
      const breadthFt = mode === 'in' ? breadth / 12 : breadth;
      return parseFloat((lengthFt * breadthFt).toFixed(4));
    }

    function formatDimension(value, mode) {
      if (mode === 'in') {
        return `${value.toFixed(2)} in`; 
      }
      return `${value.toFixed(2)} ft`;
    }

    function buildSuggestionRows(item, quantities, mode) {
      const uniqueQuantities = Array.from(new Set(quantities.filter(q => q > 0))).sort((a, b) => a - b).slice(0, 4);
      if (!uniqueQuantities.length) {
        return '<div class="text-muted">Enter dimensions to see pricing suggestions.</div>';
      }

      const rows = uniqueQuantities.map(quantity => {
        const unitPrice = getItemUnitPrice(item, quantity);
        const totalPrice = quantity * unitPrice;
        return `
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">${quantity.toFixed(2)} sq ft</div>
              <div class="text-muted small">Unit price: ${formatCurrency(unitPrice)}</div>
            </div>
            <div class="text-end">
              <div class="fw-bold">${formatCurrency(totalPrice)}</div>
            </div>
          </div>
        `;
      }).join('');

      return `
        <div class="card card-body p-2 bg-light">
          <div class="fw-semibold mb-2">Suggested price examples</div>
          <div class="list-group list-group-flush">
            ${rows}
          </div>
        </div>
      `;
    }

    function renderDimensionSuggestions() {
      const selectedItems = getSelectedItems();
      if (selectedItems.length !== 1 || !isAreaUnit(selectedItems[0].unit_label)) {
        suggestionContainer.innerHTML = '';
        return;
      }
      const item = selectedItems[0];
      const mode = getSelectedUnitMode();
      const lengthEl = document.getElementById('dimension-length');
      const breadthEl = document.getElementById('dimension-breadth');
      const length = lengthEl ? Number(lengthEl.value || 0) : 0;
      const breadth = breadthEl ? Number(breadthEl.value || 0) : 0;
      if (length <= 0 || breadth <= 0) {
        suggestionContainer.innerHTML = '<div class="text-muted">Enter valid length and breadth to see pricing suggestions.</div>';
        return;
      }
      const area = toSquareFeet(length, breadth, mode);
      const thresholdSizes = Array.isArray(item.thresholds) ? item.thresholds.map(t => Number(t.min_quantity || 0)).filter(q => q > 0) : [];
      const sampleAreas = [area, ...thresholdSizes.slice(0, 3)];
      suggestionContainer.innerHTML = buildSuggestionRows(item, sampleAreas, mode);
    }

    function updateUnitControls() {
      const selectedItems = getSelectedItems();
      quantityControls.innerHTML = '';
      const hasAreaUnit = selectedItems.length === 1 && isAreaUnit(selectedItems[0].unit_label);
      const unitLabel = getSelectedUnitLabel();

      if (hasAreaUnit) {
        quantityControls.innerHTML = `
          <div class="row g-2">
            <div class="col-4">
              <select id="dimension-unit-mode" class="form-select">
                <option value="ft">Feet</option>
                <option value="in">Inches</option>
              </select>
            </div>
            <div class="col-4">
              <input id="dimension-length" type="number" min="0" step="0.01" class="form-control" placeholder="Length (${formatDimension(1, getSelectedUnitMode()).split(' ')[1]})">
            </div>
            <div class="col-4">
              <input id="dimension-breadth" type="number" min="0" step="0.01" class="form-control" placeholder="Breadth (${formatDimension(1, getSelectedUnitMode()).split(' ')[1]})">
            </div>
            <div class="col-12 mt-2">
              <input id="pricing-quantity" type="number" min="1" value="1" class="form-control" placeholder="Quantity ${unitLabel ? `(${unitLabel})` : ''}" required>
            </div>
            <div class="col-12 mt-2">
              <div class="form-text">Computed area will be used as quantity in square feet, then multiplied by quantity.</div>
            </div>
          </div>
        `;

        const lengthEl = document.getElementById('dimension-length');
        const breadthEl = document.getElementById('dimension-breadth');
        const modeSelect = document.getElementById('dimension-unit-mode');
        const qtyEl = document.getElementById('pricing-quantity');
        [lengthEl, breadthEl, modeSelect, qtyEl].forEach(el => {
          if (!el) return;
          el.addEventListener('input', renderDimensionSuggestions);
          el.addEventListener('change', renderDimensionSuggestions);
        });
        renderDimensionSuggestions();
      } else {
        quantityControls.innerHTML = `<input id="pricing-quantity" type="number" min="1" value="1" class="form-control" placeholder="Quantity ${unitLabel ? `(${unitLabel})` : ''}" required>`;
        if (selectedItems.length > 1 && selectedItems.some(item => isAreaUnit(item.unit_label))) {
          quantityControls.insertAdjacentHTML('beforeend', '<div class="form-text text-warning">Multiple selection includes area-based items; use a shared quantity value only.</div>');
        }
        suggestionContainer.innerHTML = '';
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

    function generatePrintableHtml(items) {
      const title = document.title || 'Pricing Calculator Cart';
      const now = new Date();
      const rows = items.map(it => {
        const basisQuantity = it.area || it.quantity;
        const unit = getItemUnitPrice(it, basisQuantity);
        const subtotal = it.area ? unit * it.area * it.quantity : unit * it.quantity;
        const qtyLabel = it.area ? `${it.quantity} × ${it.area.toFixed(4)} sq ft` : it.quantity;
        return `<tr><td>${escapeHtml(it.item_name)}</td><td class="text-end">${escapeHtml(qtyLabel)}</td><td class="text-end">${formatCurrency(unit)}</td><td class="text-end">${formatCurrency(subtotal)}</td></tr>`;
      }).join('');
      const total = items.reduce((s, it) => {
        const basisQuantity = it.area || it.quantity;
        const unit = getItemUnitPrice(it, basisQuantity);
        return s + (it.area ? unit * it.area * it.quantity : unit * it.quantity);
      }, 0);
      return `
        <!doctype html>
        <html>
        <head>
          <meta charset="utf-8" />
          <title>${title}</title>
          <style>
            body{font-family:Arial,Helvetica,sans-serif;padding:20px}
            table{width:100%;border-collapse:collapse}
            th,td{padding:8px;border-bottom:1px solid #ddd}
            th{text-align:left}
            .text-end{text-align:right}
            .total{font-weight:700;margin-top:12px}
          </style>
        </head>
        <body>
          <h2>${title}</h2>
          <div>Printed: ${now.toLocaleString()}</div>
          <table>
            <thead><tr><th>Item</th><th>Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
              ${rows}
            </tbody>
          </table>
          <div class="total text-end">Grand Total: ${formatCurrency(total)}</div>
        </body>
        </html>
      `;
    }

    function escapeHtml(str) {
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function getItemUnitPrice(item, basisQuantity) {
      const basePrice = Number(item.price || 0);
      let bestPrice = basePrice;
      const thresholds = Array.isArray(item.thresholds) ? item.thresholds : [];
      thresholds.forEach(threshold => {
        const minQuantity = Number(threshold.min_quantity || 0);
        const thresholdPrice = Number(threshold.price || 0);
        if (minQuantity > 0 && basisQuantity >= minQuantity && thresholdPrice > 0) {
          bestPrice = thresholdPrice;
        }
      });
      if (bestPrice === basePrice && item.threshold_quantity > 0 && item.threshold_price > 0 && basisQuantity >= item.threshold_quantity) {
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
        const basisQuantity = cartItem.area || cartItem.quantity;
        const liveUnitPrice = getItemUnitPrice(cartItem, basisQuantity);
        const subtotal = cartItem.area ? liveUnitPrice * cartItem.area * cartItem.quantity : liveUnitPrice * cartItem.quantity;
        const quantityLabel = cartItem.area ? `${cartItem.quantity} × ${cartItem.area.toFixed(4)} sq ft` : cartItem.quantity;
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${cartItem.item_name}</td>
          <td>
            <div class="small text-muted mb-1">${escapeHtml(quantityLabel)}</div>
            <input type="number" min="0.01" step="any" value="${cartItem.quantity}" class="form-control form-control-sm cart-quantity" data-index="${index}">
          </td>
          <td class="text-end">${formatCurrency(liveUnitPrice)}</td>
          <td class="text-end">${formatCurrency(subtotal)}</td>
          <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-cart-item" data-index="${index}">Remove</button></td>
        `;
        cartBody.appendChild(row);
      });
      updateTotal();
    }

    function updateTotal() {
      const items = getCartItems();
      const total = items.reduce((sum, item) => {
        const basisQuantity = item.area || item.quantity;
        const unit = getItemUnitPrice(item, basisQuantity);
        return sum + (item.area ? unit * item.area * item.quantity : unit * item.quantity);
      }, 0);
      totalEl.textContent = formatCurrency(total);
    }

    function addItemToCart() {
      const selectedItems = getSelectedItems();
      if (!selectedItems.length) return;
      const areaItems = selectedItems.filter(item => isAreaUnit(item.unit_label));
      let quantity = 1;

      let area = null;
      if (selectedItems.length === 1 && areaItems.length === 1) {
        const lengthEl = document.getElementById('dimension-length');
        const breadthEl = document.getElementById('dimension-breadth');
        const length = Math.max(0, Number(lengthEl ? lengthEl.value : 0) || 0);
        const breadth = Math.max(0, Number(breadthEl ? breadthEl.value : 0) || 0);
        const mode = getSelectedUnitMode();
        area = parseFloat(toSquareFeet(length, breadth, mode).toFixed(4)) || 0;
        if (area <= 0) {
          alert('Please enter valid length and breadth to compute area.');
          return;
        }
        const qEl = document.getElementById('pricing-quantity');
        quantity = Math.max(1, Number(qEl ? qEl.value : 1) || 1);
      } else {
        const qEl = document.getElementById('pricing-quantity');
        quantity = Math.max(1, Number(qEl ? qEl.value : 1) || 1);
      }

      const items = getCartItems();

      selectedItems.forEach(item => {
        const basisQuantity = item.area ? item.area : area || quantity;
        const currentUnitPrice = getItemUnitPrice(item, basisQuantity);
        const cartItemData = {
          slug: item.slug,
          category: categorySelect.value,
          item_name: item.item_name,
          price: Number(item.price),
          unit_price: Number(currentUnitPrice),
          thresholds: Array.isArray(item.thresholds) ? item.thresholds : [],
          threshold_quantity: Number(item.threshold_quantity || 0),
          threshold_price: Number(item.threshold_price || 0),
          unit_label: item.unit_label,
          quantity: quantity,
          area: area || null
        };

        const shouldMerge = !isAreaUnit(item.unit_label) && items.some(cartItem => cartItem.slug === item.slug && Number(cartItem.unit_price || cartItem.price) === cartItemData.unit_price);
        if (shouldMerge) {
          const existingIndex = items.findIndex(cartItem => cartItem.slug === item.slug && Number(cartItem.unit_price || cartItem.price) === cartItemData.unit_price);
          if (existingIndex >= 0) {
            items[existingIndex].quantity = Number(items[existingIndex].quantity || 0) + Number(quantity);
          } else {
            items.push(cartItemData);
          }
        } else {
          items.push(cartItemData);
        }
      });

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
        const basisQuantity = items[index].area || quantity;
        items[index].unit_price = getItemUnitPrice(items[index], basisQuantity);
        saveCartItems(items);
        renderCart();
      }
    });

    cartBody.addEventListener('click', (event) => {
      if (!event.target.classList.contains('remove-cart-item')) return;
      const index = Number(event.target.dataset.index);
      removeItemFromCart(index);
    });

    const printButton = document.getElementById('print-cart-button');
    if (printButton) {
      printButton.addEventListener('click', () => {
        const items = getCartItems();
        if (!items.length) {
          alert('No items in cart to print.');
          return;
        }
        const html = generatePrintableHtml(items);
        const w = window.open('', '_blank');
        if (!w) {
          alert('Pop-up blocked. Please allow pop-ups to print.');
          return;
        }
        w.document.open();
        w.document.write(html);
        w.document.close();
        w.focus();
        // Give the new window a moment to render before printing
        setTimeout(() => { w.print(); }, 250);
      });
    }

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
