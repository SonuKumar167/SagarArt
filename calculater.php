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
                      <option value="<?php echo htmlspecialchars($item['slug']); ?>" data-price="<?php echo htmlspecialchars($item['price']); ?>" data-unit="<?php echo htmlspecialchars($item['unit_label']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="pricing-quantity" class="form-label">Quantity</label>
                    <input id="pricing-quantity" type="number" min="1" value="1" class="form-control" required>
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
                        <th>Qty</th>
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
              <?php if (!empty($groupedPricingItems)): ?>
                <div class="available-pricing-scroll">
                  <?php foreach ($groupedPricingItems as $category => $items): ?>
                    <div class="mb-4">
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
    const unitPriceEl = document.getElementById('pricing-unit-price');
    const unitLabelEl = document.getElementById('pricing-unit-label');
    const totalEl = document.getElementById('pricing-total');

    function formatCurrency(value) {
      return '₹ ' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const addItemButton = document.getElementById('add-item-button');
    const cartBody = document.getElementById('pricing-cart-body');

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
        option.dataset.unit = item.unit_label;
        if (index === 0) option.selected = true;
        itemSelect.appendChild(option);
      });
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

    function renderCart() {
      const items = getCartItems();
      cartBody.innerHTML = '';
      if (!items.length) {
        cartBody.innerHTML = '<tr class="text-center text-muted"><td colspan="5">No items added yet.</td></tr>';
        updateTotal();
        return;
      }

      items.forEach((cartItem, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${cartItem.item_name}</td>
          <td><input type="number" min="1" value="${cartItem.quantity}" class="form-control form-control-sm cart-quantity" data-index="${index}"></td>
          <td class="text-end">${formatCurrency(cartItem.price)}</td>
          <td class="text-end">${formatCurrency(cartItem.price * cartItem.quantity)}</td>
          <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-cart-item" data-index="${index}">Remove</button></td>
        `;
        cartBody.appendChild(row);
      });
      updateTotal();
    }

    function updateTotal() {
      const items = getCartItems();
      const total = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
      totalEl.textContent = formatCurrency(total);
    }

    function addItemToCart() {
      const item = getSelectedItem();
      if (!item) return;
      const quantity = Math.max(1, Number(quantityInput.value) || 1);
      const items = getCartItems();
      const existingIndex = items.findIndex(cartItem => cartItem.slug === item.slug);

      if (existingIndex >= 0) {
        items[existingIndex].quantity += quantity;
      } else {
        items.push({
          slug: item.slug,
          category: item.category,
          item_name: item.item_name,
          price: Number(item.price),
          unit_label: item.unit_label,
          quantity: quantity
        });
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

    addItemButton.addEventListener('click', () => {
      addItemToCart();
    });

    cartBody.addEventListener('change', (event) => {
      if (!event.target.classList.contains('cart-quantity')) return;
      const index = Number(event.target.dataset.index);
      const quantity = Math.max(1, Number(event.target.value) || 1);
      const items = getCartItems();
      if (items[index]) {
        items[index].quantity = quantity;
        saveCartItems(items);
        renderCart();
      }
    });

    cartBody.addEventListener('click', (event) => {
      if (!event.target.classList.contains('remove-cart-item')) return;
      const index = Number(event.target.dataset.index);
      removeItemFromCart(index);
    });

    renderItems();
    renderCart();
  </script>
</body>
</html>
<?php $conn->close(); ?>
