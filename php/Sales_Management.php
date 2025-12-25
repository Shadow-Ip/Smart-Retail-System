<?php
session_start();
require_once '../database/dbConnection.php';

// --- Ensure only logged-in associate ---
if (!isset($_SESSION['associate_id'])) {
  header("Location: Sales_associate_Login.php");
  exit;
}

$associate_name = $_SESSION['associate_name'] ?? 'Sales Associate';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $desc = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['stock_quantity'];
    $cat = trim($_POST['category']);

    if ($name && $price >= 0 && $qty >= 0) {
        $stmt = $pdo->prepare("
            INSERT INTO products (product_name, description, price, stock_quantity, category)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $desc, $price, $qty, $cat]);
        echo "<script>
          alert('✅ Product added successfully!');
          window.location.href='Sales_Management.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('⚠️ Please fill all fields correctly.');</script>";
    }
}

// --- Function to safely escape output ---
function s($v) { return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8'); }

// --- Function to auto-detect image for each product ---
function getProductImage($name) {
  $n = strtolower($name);
  if (strpos($n, 'headphone') !== false) return '../Pictures/Headphones.jpg';
  if (strpos($n, 'smartwatch') !== false ) return '../Pictures/Smartwatch.jpeg';
  if (strpos($n, 'speaker') !== false) return '../Pictures/speaker.jpg';
  if (strpos($n, 'coffee') !== false) return '../Pictures/Coffee Maker.webp';
  if (strpos($n, 'graphics') !== false || strpos($n, 'gpu') !== false) return '../Pictures/Graphics Card.png';
  if (strpos($n, 'monitor') !== false) return '../Pictures/Gaming Monitor.png';
  if (strpos($n, 'laptop') !== false) return '../Pictures/laptop.png';
  if (strpos($n, 'tree') !== false) return '../Pictures/Christmas Tree1.png';
  if (strpos($n, 'converter') !== false) return '../Pictures/DVI Converter.png';
  if (strpos($n, 'grinder') !== false) return '../Pictures/Grinder.png';
  if (strpos($n, 'frye') !== false) return '../Pictures/Air Frye.png';
  if (strpos($n, 'fridge') !== false) return '../Pictures/Bottom Freezer Fridge.png';
  if (strpos($n, 'motherboard') !== false) return '../Pictures/MotherBoard.png';
  if (strpos($n, 'whiskey') !== false) return '../Pictures/Whiskey1.png';
  if (strpos($n, 'whisky') !== false) return '../Pictures/Whisky.png';
  return '../Pictures/default.jpg';
}

// --- Summary Statistics ---
$total_sales = 0;
$total_orders = 0;
$low_stock = 0;
$in_stock = 0;
$out_stock = 0;

try {
  // Total completed payments
  $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Completed'");
  $total_sales = $stmt->fetchColumn() ?: 0;

  // Total orders
  $stmt = $pdo->query("SELECT COUNT(order_id) FROM orders");
  $total_orders = $stmt->fetchColumn() ?: 0;

  // Stock summary
  $stmt = $pdo->query("
    SELECT 
      SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) AS out_stock,
      SUM(CASE WHEN stock_quantity BETWEEN 1 AND 5 THEN 1 ELSE 0 END) AS low_stock,
      SUM(CASE WHEN stock_quantity > 5 THEN 1 ELSE 0 END) AS in_stock
    FROM products
  ");
  $stock = $stmt->fetch(PDO::FETCH_ASSOC);
  $low_stock = $stock['low_stock'];
  $in_stock = $stock['in_stock'];
  $out_stock = $stock['out_stock'];
} catch (PDOException $e) {
  die("Dashboard Error: " . $e->getMessage());
}

// --- Inventory Data ---
$inventory = $pdo->query("SELECT * FROM products ORDER BY category ASC, product_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Orders Data ---
$orders = $pdo->query("
  SELECT o.order_id, c.full_name AS customer_name, o.order_date, o.total_amount, o.status
  FROM orders o
  JOIN customers c ON o.customer_id = c.customer_id
  ORDER BY o.order_date DESC
  LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sales Management - Smart Retail System</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <!-- Header -->
  <header class="navbar">
    <div class="logo">🛍️ Smart Retail System - Sales Dashboard</div>
    <nav>
      <ul class="nav-links">
        <li><a href="Customer_Shop.php">Customer View</a></li>
        <li><a onclick="document.getElementById('sales').scrollIntoView({behavior:'smooth'});">Dashboard</a></li>
        <li><a onclick="document.getElementById('inventory').scrollIntoView({behavior:'smooth'});">Inventory</a></li>
        <li><a onclick="document.getElementById('orders').scrollIntoView({behavior:'smooth'});">Orders</a></li>
        <li><a href="Sales_Logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
      </ul>
    </nav>
  </header>

  <?php if (isset($_GET['success'])): ?>
  <div class="alert-box show">
    <div class="alert success">
      <i class='bx bxs-check-circle'></i>
      <span><?= htmlspecialchars($_GET['success']); ?></span>
    </div>
  </div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="alert-box show">
    <div class="alert error">
      <i class='bx bxs-x-circle'></i>
      <span><?= htmlspecialchars($_GET['error']); ?></span>
    </div>
  </div>
<?php endif; ?>


  <main id="sales">
    <!-- === SALES OVERVIEW === -->
    <section class="sales-section">
      <h1>Welcome  <?= s($associate_name) ?> </h1>
      <div class="dashboard-summary">
        <div class="summary-card">
          <img src="../Pictures/total sale.jpg" alt="Sales" />
          <h3>Total Sales</h3>
          <p>R<?= number_format($total_sales, 2) ?></p>
        </div>
        <div class="summary-card">
          <img src="../Pictures/orders.png" alt="Orders" />
          <h3>Number of Orders</h3>
          <p><?= $total_orders ?></p>
        </div>
        <div class="summary-card">
          <img src="../Pictures/low stock1.jpg" alt="Low Stock" />
          <h3>Low Stock Items</h3>
          <p><?= $low_stock ?></p>
        </div>
        <div class="summary-card">
          <img src="../Pictures/in stock.jpg" alt="Stock Available" />
          <h3>Stock Available</h3>
          <p><?= $in_stock ?></p>
        </div>
        <div class="summary-card">
          <img src="../Pictures/out of stock.jpg" alt="Out of Stock" />
          <h3>Out of Stock</h3>
          <p><?= $out_stock ?></p>
        </div>
      </div>
    </section>

    <!-- === INVENTORY OVERVIEW === -->
    <section id="inventory" class="inventory-section">
      <div class="inventory-header">
  <h2>Inventory Overview</h2>
  <button id="addProductBtn" class="primary-btn">+ Add New Product</button>
</div>


      <!-- Search & Filter -->
      <div class="filter-bar">
        <div class="filter-item">
          <label for="productSearch">Search Product</label>
          <input id="productSearch" placeholder="Search by product name..." onkeyup="filterInventory()" />
        </div>
        <div class="filter-item">
          <label for="stockFilter">Filter by Stock Status</label>
          <select id="stockFilter" onchange="filterInventory()">
            <option value="All">All</option>
            <option value="Low Stock">Low Stock</option>
            <option value="Stock Available">Stock Available</option>
            <option value="Out of Stock">Out of Stock</option>
          </select>
        </div>
      </div>

      <!-- Inventory Table -->
      <table class="inventory-table" id="inventoryTable">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Stock Level</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inventory as $p): 
            $img = getProductImage($p['product_name']);
            $statusClass = $p['stock_quantity'] == 0 ? 'status-out' :
                          ($p['stock_quantity'] <= 5 ? 'status-low' : 'status-good');
            $statusText = $p['stock_quantity'] == 0 ? 'Out of Stock' :
                          ($p['stock_quantity'] <= 5 ? 'Low Stock' : 'Stock Available');
          ?>
            <tr 
              data-name="<?= strtolower(s($p['product_name'])) ?>" 
              data-status="<?= $statusText ?>"
              data-id="<?= (int)$p['product_id'] ?>"
            >
              <td>
              <a href="#" class="product-trigger" 
                data-name="<?= s($p['product_name']) ?>"
                data-desc="<?= s($p['description']) ?>"
                data-price="<?= number_format($p['price'], 2) ?>"
                data-stock="<?= (int)$p['stock_quantity'] ?>"
                data-category="<?= s($p['category']) ?>"
                data-img="<?= s($img) ?>">
              <img src="<?= s($img) ?>" alt="<?= s($p['product_name']) ?>" class="clickable-img" />
              </a>
              <br><?= s($p['product_name']) ?>
            </td>

              <td><?= s($p['category'] ?: 'Uncategorized') ?></td>
              <td class="stock-qty"><?= (int)$p['stock_quantity'] ?></td>
              <td class="<?= $statusClass ?> status-cell"><?= $statusText ?></td>
              <td>
                <button class="enter-btn" onclick="restockProduct(<?= (int)$p['product_id'] ?>)">Restock</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div id="addProductModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Add New Product</h3>
    <form method="POST" id="addProductForm">
      <label>Product Name</label>
      <input type="text" name="product_name" required>

      <label>Description</label>
      <textarea name="description" required></textarea>

      <label>Price (R)</label>
      <input type="number" name="price" step="0.01" required>

      <label>Stock Quantity</label>
      <input type="number" name="stock_quantity" required>

      <label>Category</label>
      <input type="text" name="category">

      <div style="margin-top:1rem; display:flex; justify-content:space-between;">
        <button type="submit" name="add_product" class="btn" style="background:#28a745;color:#fff;">Save Product</button>
        <button type="button" id="cancelAdd" class="primary-btn" style="background:#dc3545;color:#fff;">Cancel</button>
      </div>
    </form>
  </div>
</div>

    </section>


    <!-- === ORDERS MANAGEMENT === -->
    <section id="orders" class="orders-section">
      <h1>Orders Management</h1>
      <div class="filter-bar">
        <div class="filter-item">
          <label for="orderSearch">Search Orders</label>
          <input id="orderSearch" placeholder="Search by order ID or customer" onkeyup="searchOrders()" />
        </div>
        <div class="filter-item">
          <label for="statusFilter">Filter by Status</label>
          <select id="statusFilter" onchange="filterOrders()">
            <option value="All">All</option>
            <option value="Pending">Pending</option>
            <option value="Processing">Processing</option>
            <option value="Shipped">Shipped</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <table class="orders-table" id="ordersTable">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr data-status="<?= s($o['status']) ?>">
            <td>#<?= (int)$o['order_id'] ?></td>
            <td><?= s($o['customer_name']) ?></td>
            <td><?= s(date('Y-m-d', strtotime($o['order_date']))) ?></td>
            <td>R<?= number_format($o['total_amount'], 2) ?></td>
            <td><?= s($o['status']) ?></td>
            <td>
              <form method="post" action="update_order.php" class="status-form">
                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                <select name="status">
                  <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $status): ?>
                    <option <?= $o['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn" type="submit">Update</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Smart Retail System | Internal Dashboard</p>
  </footer>
    <!-- === PRODUCT DETAIL MODAL === -->
<div id="productModal" class="modal-overlay">
  <div class="modal-content" id="productModalContent">
    <button id="closeProductModal" class="close-btn">&times;</button>
    <img id="modalImage" src="" alt="Product" style="width:100%;border-radius:8px;">
    <h2 id="modalName"></h2>
    <p id="modalDesc"></p>
    <p><strong>Category:</strong> <span id="modalCategory"></span></p>
    <p><strong>Price:</strong> R<span id="modalPrice"></span></p>
    <p><strong>Stock:</strong> <span id="modalStock"></span></p>

    <div class="modal-actions">
      <button id="editProductBtn" class="enter-btn">Restock</button>
    </div>
  </div>
</div>

  <script>
    // --- Search + Filter for Orders ---
    function filterOrders() {
      const val = document.getElementById('statusFilter').value;
      document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
        tr.style.display = (val === 'All' || tr.dataset.status === val) ? '' : 'none';
      });
    }

    function searchOrders() {
      const query = document.getElementById('orderSearch').value.toLowerCase();
      document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
        const text = tr.textContent.toLowerCase();
        tr.style.display = text.includes(query) ? '' : 'none';
      });
    }

  // --- Filter + Search for Inventory ---
  function filterInventory() {
    const searchValue = document.getElementById('productSearch').value.toLowerCase();
    const filterValue = document.getElementById('stockFilter').value;

    document.querySelectorAll('#inventoryTable tbody tr').forEach(tr => {
      const name = tr.dataset.name;
      const status = tr.dataset.status;
      const matchesSearch = name.includes(searchValue);
      const matchesFilter = (filterValue === 'All' || status === filterValue);

      tr.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
  }

  // --- Restock Function ---
  async function restockProduct(productId) {
    const amount = prompt("Enter quantity to add to stock:", "");
    if (amount === null || isNaN(amount) || amount <= 0) return;

    try {
      const formData = new FormData();
      formData.append('product_id', productId);
      formData.append('quantity', amount);

      const response = await fetch('update_stock.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();
      if (result.success) {
        const row = document.querySelector(`#inventoryTable tr[data-id="${productId}"]`);
        if (row) {
          const newQty = result.new_quantity;
          const qtyCell = row.querySelector('.stock-qty');
          qtyCell.textContent = newQty;

          // update status & color dynamically
          const statusCell = row.querySelector('.status-cell');
          if (newQty == 0) {
            statusCell.textContent = "Out of Stock";
            statusCell.className = "status-cell status-out";
            row.dataset.status = "Out of Stock";
          } else if (newQty <= 5) {
            statusCell.textContent = "Low Stock";
            statusCell.className = "status-cell status-low";
            row.dataset.status = "Low Stock";
          } else {
            statusCell.textContent = "Stock Available";
            statusCell.className = "status-cell status-good";
            row.dataset.status = "Stock Available";
          }
        }
        alert("✅ Stock updated successfully!");
      } else {
        alert("❌ Failed to update stock.");
      }
    } catch (err) {
      console.error(err);
      alert("⚠️ Error communicating with server.");
    }
  }

  document.querySelectorAll('.status-form').forEach(form => {
  form.addEventListener('submit', e => {
    const confirmed = confirm("Are you sure you want to update this order status?");
    if (!confirmed) e.preventDefault();
  });
});

document.getElementById('addProductBtn').addEventListener('click', () => {
  document.getElementById('addProductModal').classList.add('show');
});
document.getElementById('cancelAdd').addEventListener('click', () => {
  document.getElementById('addProductModal').classList.remove('show');
});

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('productModal');
  const closeModalBtn = document.getElementById('closeProductModal');
  const editBtn = document.getElementById('editProductBtn');

  // Open modal when image is clicked
  document.querySelectorAll('.product-trigger').forEach(trigger => {
    trigger.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('modalImage').src = trigger.dataset.img;
      document.getElementById('modalName').textContent = trigger.dataset.name;
      document.getElementById('modalDesc').textContent = trigger.dataset.desc;
      document.getElementById('modalCategory').textContent = trigger.dataset.category || 'Uncategorized';
      document.getElementById('modalPrice').textContent = trigger.dataset.price;
      document.getElementById('modalStock').textContent = trigger.dataset.stock;
      modal.dataset.productId = trigger.closest('tr').dataset.id; // store product_id
      modal.classList.add('show');
    });
  });

  // Close modal on button click
  closeModalBtn.addEventListener('click', () => {
    modal.classList.remove('show');
  });

  // Close modal if clicked outside content
  modal.addEventListener('click', e => {
    if (e.target === modal) modal.classList.remove('show');
  });

  // Edit button click handler
  editBtn.addEventListener('click', () => {
    const productId = modal.dataset.productId;
    if (!productId) return;

    modal.classList.remove('show');
    // Smooth scroll to restock button
    const row = document.querySelector(`#inventoryTable tr[data-id="${productId}"]`);
    if (row) {
      row.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const btn = row.querySelector('.enter-btn');
      btn.classList.add('highlight');
      setTimeout(() => btn.classList.remove('highlight'), 2000);
    }
  });
});
</script>
<script src="../js/script.js"></script>
</body>
</html>
