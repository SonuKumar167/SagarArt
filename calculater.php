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
        <div class="col-lg-8">
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
                        <th>SR NO</th>
                        <th>Item Name</th>
                        <th>SIZE / DIMENSION</th>
                        <th>UNIT</th>
                        <th>MATERIAL / PAPER</th>
                        <th>QTY</th>
                        <th class="text-end">RATE (₹)</th>
                        <th class="text-end">AMOUNT (₹)</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody id="pricing-cart-body">
                      <tr class="text-center text-muted">
                        <td colspan="9">No items added yet.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Sub Total</span>
                    <strong id="pricing-subtotal">₹ 0.00</strong>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">GST (18%)</span>
                    <strong id="pricing-gst-amount">₹ 0.00</strong>
                  </div>
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

        <div class="col-lg-4">
          <div class="card section-card h-100">
            <div class="card-body">
              <h2 class="h4 mb-3">Available Items</h2>
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
    const subtotalEl = document.getElementById('pricing-subtotal');
    const gstAmountEl = document.getElementById('pricing-gst-amount');
    const GST_PERCENT = 18; // fixed GST percent (display-only)

    const addItemButton = document.getElementById('add-item-button');
    const cartBody = document.getElementById('pricing-cart-body');

    // GST is fixed/display-only; totals update automatically via updateTotal()

    function getStorageCartKey() {
      return 'pricingCalculatorCart';
    }

    function getSelectedItems() {
      const selectedCategory = categorySelect.value;
      const items = pricingGroups[selectedCategory] || [];
      const selectedOptions = Array.from(itemSelect.selectedOptions || []);

      if (!selectedOptions.length && items.length) {
        const fallbackOption = itemSelect.options[0];
        if (fallbackOption) {
          fallbackOption.selected = true;
          selectedOptions.push(fallbackOption);
        }
      }

      const selectedValues = selectedOptions.map(option => option.value).filter(Boolean);
      const normalizedValues = new Set(selectedValues);

      return items.filter(item => {
        const itemKey = item.slug || item.id || '';
        return normalizedValues.has(itemKey);
      });
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
      return u === 'sq ft' || u === 'sqft' || u === 'per sqft' || u === 'square feet' || u === 'square foot' || u === 'square ft';
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
      return parseFloat((lengthFt * breadthFt).toFixed(2));
    }

    function formatDimension(value, mode) {
      if (mode === 'in') {
        return `${value.toFixed(2)} in`; 
      }
      return `${value.toFixed(2)} ft`;
    }

    function getSuggestionLabel(item, quantity) {
      if (isAreaUnit(item?.unit_label)) {
        return `${quantity.toFixed(2)} sq ft`;
      }
      const label = item?.unit_label ? item.unit_label.toString().trim() : '';
      return label ? `${quantity.toFixed(2)} ${label}` : `${quantity.toFixed(2)} unit`;
    }

    function buildSuggestionRows(item, quantities) {
      const uniqueQuantities = Array.from(new Set(quantities.filter(q => q > 0))).sort((a, b) => a - b).slice(0, 4);
      if (!uniqueQuantities.length) {
        return '<div class="text-muted">Enter a quantity to see pricing suggestions.</div>';
      }

      const rows = uniqueQuantities.map(quantity => {
        const unitPrice = getItemUnitPrice(item, quantity);
        const totalPrice = quantity * unitPrice;
        return `
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">${getSuggestionLabel(item, quantity)}</div>
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
      if (selectedItems.length !== 1) {
        suggestionContainer.innerHTML = '';
        return;
      }

      const item = selectedItems[0];
      const qtyEl = document.getElementById('pricing-quantity');
      const currentQuantity = qtyEl ? Number(qtyEl.value || 0) : 0;

      if (isAreaUnit(item.unit_label)) {
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
        suggestionContainer.innerHTML = buildSuggestionRows(item, sampleAreas);
        return;
      }

      if (currentQuantity <= 0) {
        suggestionContainer.innerHTML = '<div class="text-muted">Enter a quantity to see pricing suggestions.</div>';
        return;
      }

      const thresholdSizes = Array.isArray(item.thresholds) ? item.thresholds.map(t => Number(t.min_quantity || 0)).filter(q => q > 0) : [];
      const sampleQuantities = [currentQuantity, ...thresholdSizes.slice(0, 3)];
      suggestionContainer.innerHTML = buildSuggestionRows(item, sampleQuantities);
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
      } else {
        quantityControls.innerHTML = `<input id="pricing-quantity" type="number" min="1" value="1" class="form-control" placeholder="Quantity ${unitLabel ? `(${unitLabel})` : ''}" required>`;
        if (selectedItems.length > 1 && selectedItems.some(item => isAreaUnit(item.unit_label))) {
          quantityControls.insertAdjacentHTML('beforeend', '<div class="form-text text-warning">Multiple selection includes area-based items; use a shared quantity value only.</div>');
        }
      }

      const qtyEl = document.getElementById('pricing-quantity');
      if (qtyEl) {
        qtyEl.addEventListener('input', renderDimensionSuggestions);
        qtyEl.addEventListener('change', renderDimensionSuggestions);
      }
      renderDimensionSuggestions();
    }

    function getCartItems() {
      try {
        const cartData = localStorage.getItem(getStorageCartKey());
        return cartData ? JSON.parse(cartData) : [];
      } catch (error) {
        console.error('Unable to read cart from localStorage:', error);
        return [];
      }
    }

    function saveCartItems(items) {
      try {
        localStorage.setItem(getStorageCartKey(), JSON.stringify(items));
      } catch (error) {
        console.error('Unable to save cart to localStorage:', error);
      }
    }

    function formatCurrency(value) {
      return '₹ ' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function generatePrintableHtml(items) {
      const now = new Date();
      const quotationDate = now.toLocaleDateString('en-GB');
      const quotationNumber = String(Math.floor(100000 + Math.random() * 900000));
      const rows = items.map((it, i) => {
        const basisQuantity = it.area || it.quantity;
        const unit = getItemUnitPrice(it, basisQuantity);
        const subtotal = it.area ? unit * it.area * it.quantity : unit * it.quantity;
        const isArea = !!it.area || isAreaUnit(it.unit_label);
        const sizeDim = isArea ? (it.dimension || it.area_label || '') : (it.item_name || it.description || '');
        const material = isArea ? (it.item_name || '') : '';
        const dimensionUnit = it.dimension_unit || (isArea ? 'FEET' : 'Number');
        return `<tr><td>${escapeHtml(String(i + 1))}</td><td>${escapeHtml(it.description || it.item_name)}</td><td>${escapeHtml(sizeDim)}</td><td class="text-center">${escapeHtml(dimensionUnit)}</td><td>${escapeHtml(material)}</td><td class="text-end">${escapeHtml(String(it.quantity))}</td><td class="text-end">${formatCurrency(unit)}</td><td class="text-end">${formatCurrency(subtotal)}</td></tr>`;
      }).join('');
      const total = items.reduce((s, it) => {
        const basisQuantity = it.area || it.quantity;
        const unit = getItemUnitPrice(it, basisQuantity);
        return s + (it.area ? unit * it.area * it.quantity : unit * it.quantity);
      }, 0);
      const subtotal = total;
      const gstInputVal = Number(GST_PERCENT) || 0;
      const gstAmount = subtotal * (gstInputVal / 100);
      const computedComplete = subtotal + gstAmount;
      return `
        <!doctype html>
        <html>
        <head>
          <meta charset="utf-8" />
          <title>Quotation - Sagar Arts</title>
          <style>
            body{font-family:Arial,Helvetica,sans-serif;color:#111;margin:0;padding:20px}
            .header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px}
            .brand-block{max-width:55%}
            .brand-title{font-size:28px;margin:0;color:#0d2a56;letter-spacing:1px}
            .brand-subtitle{margin:6px 0 0;font-size:14px;color:#555}
            .brand-note{margin:6px 0 0;font-size:12px;color:#333}
            .contact-box{border:1px solid #0d2a56;padding:14px;max-width:320px}
            .contact-box h3{margin:0 0 8px;font-size:16px;color:#0d2a56}
            .contact-box div{font-size:12px;line-height:1.5;color:#333}
            .quote-info{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
            .quote-address,.quote-metadata{border:1px solid #ddd;padding:14px}
            .quote-address h4,.quote-metadata h4{margin-top:0;font-size:14px;color:#0d2a56}
            .quote-address div,.quote-metadata div{font-size:12px;line-height:1.6;color:#333;margin-bottom:8px}
            .quote-metadata .field{display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px}
            table{width:100%;border-collapse:collapse;margin-bottom:20px}
            th,td{padding:10px 8px;border:1px solid #ccc;font-size:12px}
            th{text-align:left;background:#f4f6fb;color:#0d2a56}
            td{text-align:left;vertical-align:top}
            .text-end{text-align:right}
            .text-center{text-align:center}
          </style>
        </head>
        <body>
          <div class="header">
            <div class="brand-block">
              <p class="brand-title">SAGAR ARTS</p>
              <p class="brand-subtitle">DESIGN | PRINT | INSTALL</p>
              <p class="brand-note">We Print Your Imagination</p>
            </div>
            <div class="contact-box">
              <h3>Contact</h3>
              <div><strong>Email:</strong> ${escapeHtml('Sagararts1@gmail.com')}</div>
              <div><strong>Phone:</strong> ${escapeHtml('+91 9199115271, +91 9122796271')}</div>
              <div><strong>Address:</strong> ${escapeHtml('S.S. Enclave, Near Mico, Old G.T. Road,   Sasaram-821115, Bihar')}</div>
            </div>
          </div>

          <div class="quote-info">
            <div class="quote-address">
              <h4>To</h4>
              <div>Customer Name: ____________________________</div>
              <div>Address: _________________________________</div>
              <div>Whatsapp Number: ___________________________________</div>
              <div>Email: ____________________________________</div>
            </div>
            <div class="quote-metadata">
              <h4>&nbsp;</h4>
              <div class="field"><span>Quotation No</span><span>${escapeHtml(quotationNumber)}</span></div>
              <div class="field"><span>Date</span><span>${escapeHtml(quotationDate)}</span></div>
              <div class="field"><span>Valid Upto</span><span>20 Days</span></div>
            </div>
          </div>

          <table>
            <thead>
              <tr>
                <th>SR NO</th>
                <th>Item Name</th>
                <th>SIZE / DIMENSION</th>
                <th>UNIT</th>
                <th>MATERIAL / PAPER</th>
                <th>QTY</th>
                <th>RATE (₹)</th>
                <th>AMOUNT (₹)</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
            </tbody>
          </table>

          <div style="display:flex;justify-content:flex-end;margin-top:16px">
            <table style="width:320px;border-collapse:collapse">
              <tbody>
                <tr><td style="border:1px solid #ccc;padding:8px 10px;font-weight:600">Sub Total</td><td style="border:1px solid #ccc;padding:8px 10px;text-align:right">${formatCurrency(subtotal)}</td></tr>
                <tr><td style="border:1px solid #ccc;padding:8px 10px;font-weight:600">GST (${gstInputVal}% )</td><td style="border:1px solid #ccc;padding:8px 10px;text-align:right">${formatCurrency(gstAmount)}</td></tr>
                <tr><td style="border:1px solid #ccc;padding:8px 10px;font-weight:700;background:#d43f3a;color:#fff">Grand Total</td><td style="border:1px solid #ccc;padding:8px 10px;text-align:right;font-weight:700;background:#d43f3a;color:#fff">${formatCurrency(computedComplete)}</td></tr>
              </tbody>
            </table>
          </div>
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
        cartBody.innerHTML = '<tr class="text-center text-muted"><td colspan="9">No items added yet.</td></tr>';
        updateTotal();
        return;
      }

      items.forEach((cartItem, index) => {
        const basisQuantity = cartItem.area || cartItem.quantity;
        const liveUnitPrice = getItemUnitPrice(cartItem, basisQuantity);
        const subtotal = cartItem.area ? liveUnitPrice * cartItem.area * cartItem.quantity : liveUnitPrice * cartItem.quantity;
        const row = document.createElement('tr');
        const isArea = !!cartItem.area || isAreaUnit(cartItem.unit_label);
        const sizeCell = isArea ? `<input type="text" class="form-control form-control-sm cart-dimension" style="min-width:120px;" value="${escapeHtml(cartItem.dimension || cartItem.area_label || '')}" data-index="${index}" placeholder="10*12">` : `<span class="text-muted">${escapeHtml(cartItem.item_name || cartItem.description || '')}</span>`;
        const materialCell = isArea ? `${escapeHtml(cartItem.item_name)}` : '';
        const dimensionUnit = cartItem.dimension_unit || (isArea ? 'FEET' : 'Number');
        row.innerHTML = `
          <td>${index + 1}</td>
          <td>${escapeHtml(cartItem.description || cartItem.item_name)}</td>
          <td>${sizeCell}</td>
          <td class="text-center">${escapeHtml(dimensionUnit)}</td>
          <td>${materialCell}</td>
          <td style="width:140px;">
            <input type="number" min="0.01" step="any" value="${cartItem.quantity}" class="form-control form-control-sm cart-quantity" data-index="${index}" style="min-width:120px;">
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
      const subtotal = items.reduce((sum, item) => {
        const basisQuantity = item.area || item.quantity;
        const unit = getItemUnitPrice(item, basisQuantity);
        return sum + (item.area ? unit * item.area * item.quantity : unit * item.quantity);
      }, 0);
      const gstAmount = subtotal * (GST_PERCENT / 100);
      const grandTotal = subtotal + gstAmount;
      if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
      if (gstAmountEl) gstAmountEl.textContent = formatCurrency(gstAmount);
      if (totalEl) totalEl.textContent = formatCurrency(grandTotal);
    }

    function computeNumericSubtotal() {
      const items = getCartItems();
      return items.reduce((sum, item) => {
        const basisQuantity = item.area || item.quantity;
        const unit = getItemUnitPrice(item, basisQuantity);
        return sum + (item.area ? unit * item.area * item.quantity : unit * item.quantity);
      }, 0);
    }


    function addItemToCart() {
      const selectedItems = getSelectedItems();
      if (!selectedItems.length) {
        alert('Please select at least one item before adding it to the cart.');
        return;
      }

      const areaItems = selectedItems.filter(item => isAreaUnit(item.unit_label));
      let quantity = 1;
      let area = null;
      let areaLabel = '';
      let length = 0;
      let breadth = 0;

      if (selectedItems.length === 1 && areaItems.length === 1) {
        const lengthEl = document.getElementById('dimension-length');
        const breadthEl = document.getElementById('dimension-breadth');
        length = Math.max(0, Number(lengthEl ? lengthEl.value : 0) || 0);
        breadth = Math.max(0, Number(breadthEl ? breadthEl.value : 0) || 0);
        const mode = getSelectedUnitMode();
        area = parseFloat(toSquareFeet(length, breadth, mode).toFixed(4)) || 0;
        if (area <= 0) {
          alert('Please enter valid length and breadth to compute area.');
          return;
        }
        const qEl = document.getElementById('pricing-quantity');
        quantity = Math.max(1, Number(qEl ? qEl.value : 1) || 1);
        const lengthLabel = `${length}${mode === 'in' ? 'in' : 'ft'}`;
        const breadthLabel = `${breadth}${mode === 'in' ? 'in' : 'ft'}`;
        areaLabel = `${lengthLabel}*${breadthLabel} (${area.toFixed(2)})`;
      } else {
        const qEl = document.getElementById('pricing-quantity');
        quantity = Math.max(1, Number(qEl ? qEl.value : 1) || 1);
      }

      const items = getCartItems();

      selectedItems.forEach(item => {
        const basisQuantity = area || quantity;
        const currentUnitPrice = getItemUnitPrice(item, basisQuantity);
        const cartItemData = {
          slug: item.slug || item.id || '',
          category: categorySelect.value,
          item_name: item.item_name,
          description: item.description || item.item_name || '',
          price: Number(item.price || 0),
          unit_price: Number(currentUnitPrice || 0),
          thresholds: Array.isArray(item.thresholds) ? item.thresholds : [],
          threshold_quantity: Number(item.threshold_quantity || 0),
          threshold_price: Number(item.threshold_price || 0),
          unit_label: item.unit_label,
          quantity: quantity,
          area: area || null,
          dimension: area ? `${length}*${breadth}` : '',
          area_label: area ? areaLabel : '',
          dimension_unit: area ? (getSelectedUnitMode() === 'in' ? 'INCH' : 'FEET') : 'Number'
        };

        const shouldMerge = !isAreaUnit(item.unit_label) && items.some(cartItem => (cartItem.slug || cartItem.id || '') === (item.slug || item.id || '') && Number(cartItem.unit_price || cartItem.price) === Number(cartItemData.unit_price));
        if (shouldMerge) {
          const existingIndex = items.findIndex(cartItem => (cartItem.slug || cartItem.id || '') === (item.slug || item.id || '') && Number(cartItem.unit_price || cartItem.price) === Number(cartItemData.unit_price));
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

    function parseDimensionValue(value) {
      const cleaned = String(value).trim().toLowerCase().replace(/x/g, '*').replace(/\s+/g, '');
      const parts = cleaned.split('*').filter(Boolean);
      if (parts.length !== 2) return null;
      const length = Number(parts[0]);
      const breadth = Number(parts[1]);
      if (Number.isFinite(length) && Number.isFinite(breadth) && length > 0 && breadth > 0) {
        return { length, breadth, area: parseFloat((length * breadth).toFixed(4)), dimension: `${length}*${breadth}` };
      }
      return null;
    }

    cartBody.addEventListener('change', (event) => {
      const target = event.target;
      const items = getCartItems();
      if (target.classList.contains('cart-quantity')) {
        const index = Number(target.dataset.index);
        const quantity = Math.max(0.01, Number(target.value) || 0.01);
        if (items[index]) {
          items[index].quantity = quantity;
          const basisQuantity = items[index].area || quantity;
          items[index].unit_price = getItemUnitPrice(items[index], basisQuantity);
          saveCartItems(items);
          renderCart();
        }
        return;
      }
      if (target.classList.contains('cart-dimension')) {
        const index = Number(target.dataset.index);
        const parsed = parseDimensionValue(target.value);
        if (items[index]) {
          if (parsed) {
            items[index].dimension = parsed.dimension;
            items[index].area = parsed.area;
            items[index].area_label = parsed.dimension;
            const basisQuantity = items[index].area || items[index].quantity;
            items[index].unit_price = getItemUnitPrice(items[index], basisQuantity);
            saveCartItems(items);
          }
          renderCart();
        }
        return;
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
        // ensure totals are up-to-date
        updateTotal();
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
