<?php

// <------ Start the session ------------ >
session_start();

require_once '../database/dbConnection.php';


$successMsg = '';

// ----------------- ADD TO CART -----------------
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    if (!isset($_SESSION['customer_id'])) {
        // Guest user — use session only
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'price' => $price,
                'quantity' => $quantity
            ];
        }
    } else {
// < ----------- Logged-in user — store cart in DB ------------------ >
        $customer_id = $_SESSION['customer_id'];

// < ---------- Check if product already exists in their cart ------------------ >
        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE customer_id = ? AND product_id = ?");
        $stmt->execute([$customer_id, $product_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart_items (customer_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$customer_id, $product_id, $quantity]);
        }
    }
    
 // < ---------- Update session cart count instantly --------------- >
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $_SESSION['cart_count'] = $stmt->fetchColumn();
} else {
    $_SESSION['cart_count'] = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

}


// < ----------------- FETCH PRODUCTS ----------------- >
$stmt = $pdo->query("SELECT * FROM products ORDER BY date_added DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// < ----------------- IMAGE HANDLER ----------------- >
function getProductImage($name) {
  $name = strtolower($name);
  if (strpos($name, 'headphone') !== false) return '../Pictures/Headphones.jpg';
  if (strpos($name, 'smartwatch') !== false) return '../Pictures/Smartwatch.jpeg';
  if (strpos($name, 'speaker') !== false) return '../Pictures/speaker.jpg';
  if (strpos($name, 'coffee') !== false) return '../Pictures/Coffee Maker.webp';
  if (strpos($name, 'graphics') !== false) return '../Pictures/Graphics Card.png';
  if (strpos($name, 'monitor') !== false) return '../Pictures/Gaming Monitor.png';
  if (strpos($name, 'laptop') !== false) return '../Pictures/laptop.png';
  if (strpos($name, 'tree') !== false) return '../Pictures/Christmas Tree1.png';
  if (strpos($name, 'converter') !== false) return '../Pictures/DVI Converter.png';
  if (strpos($name, 'grinder') !== false) return '../Pictures/Grinder.png';
  if (strpos($name, 'frye') !== false) return '../Pictures/Air Frye.png';
  if (strpos($name, 'fridge') !== false) return '../Pictures/Bottom Freezer Fridge.png';
  if (strpos($name, 'motherboard') !== false) return '../Pictures/MotherBoard.png';
  if (strpos($name, 'whiskey') !== false) return '../Pictures/Whiskey1.png';
  if (strpos($name, 'whisky') !== false) return '../Pictures/Whisky.png';
  return '../Pictures/default.jpg';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smart Retail System - Shop</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  <!-- Header -->
  <header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
      <ul class="nav-links">
        <li>Welcome <strong><?= htmlspecialchars($_SESSION['customer_name'] ?? 'Guest'); ?></strong></li>
        <li><a href="Cart.php">🛒 Cart (<?= ($_SESSION['cart_count'] ?? 0) ?> items)</a></li>
        <li><a href="javascript:void(0);" onclick="document.getElementById('home').scrollIntoView({behavior:'smooth'});">Home</a></li>
        <li><a href="javascript:void(0);" onclick="document.getElementById('products').scrollIntoView({behavior:'smooth'});">Products</a></li>
        <!-- Change Button depending on the user -->
        <?php if (isset($_SESSION['customer_name'])): ?>
          <li><a href="Orders.php">Orders</a>
          <li><a href="Customer_Logout.php" class="login-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
        <?php else: ?>
          <li><a href="Customer_Login.php" class="login-btn">Login</a></li>
          <li><a href="../Dashboard.php">Back to Main Menu</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <!-- Main -->
  <main id="home">
          <!-- Alert Box -->
    <?php if (!empty($successMsg)): ?>
    <div class="alert-box show" id="alertBox">
      <div class="alert success">
        <i class='bx bxs-check-circle'></i>
        <span><?= htmlspecialchars($successMsg); ?></span>
      </div>
    </div>

    <script>
      // Fade out 
      (function () {
        const alertBox = document.getElementById('alertBox');

  //< --------- after 5s start fading out (use my CSS .fade-out class) ---------- >
        setTimeout(() => {
          if (alertBox) alertBox.classList.add('fade-out');
        }, 5000);
      })();
    </script>
    <?php endif; ?>


    <!-- Introduction -->
    <section class="intro">
      <h1>Welcome to Smart Retail</h1>
      <p>Shop smarter with exclusive deals and real-time stock updates.</p>
      <button class="primary-btn" onclick="document.getElementById('products').scrollIntoView({behavior:'smooth'});">Start Shopping</button>
    </section>

    <!-- Product Section -->
    <section id="products" class="product-section container">
      <h2>Product Catalogue</h2>

      <!-- Filters -->
      <div class="filter-bar">
        <div class="filter-item">
          <label for="search">Search</label>
          <input id="search" type="text" placeholder="Search products by name..." />
        </div>

        <div class="filter-item">
          <label for="sort">Sort by</label>
          <select id="sort">
            <option value="newest">Newest Arrivals</option>
            <option value="oldest">Oldest First</option>
            <option value="price_low">Price: Low to High</option>
            <option value="price_high">Price: High to Low</option>
          </select>
        </div>
      </div>

      <!-- Product Grid -->
      <div class="product-grid" id="productGrid">
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product): ?>
            <div class="product-card"
                data-name="<?= strtolower(htmlspecialchars($product['product_name'])); ?>"
                data-price="<?= htmlspecialchars($product['price']); ?>"
                data-date="<?= htmlspecialchars($product['date_added']); ?>">
              <img src="<?= getProductImage($product['product_name']); ?>" 
                  alt="<?= htmlspecialchars($product['product_name']); ?>" />
              <h3><?= htmlspecialchars($product['product_name']); ?></h3>
              <p class="desc"><?= htmlspecialchars($product['description']); ?></p>
              <strong>R<?= number_format($product['price'], 2); ?></strong>
              <div class="actions">
              <a href="Product_selection.php?product_id=<?= $product['product_id']; ?>">
                <button class="view-btn">View</button>
              </a>
              <!-- Change button depending on Stock -->
                <?php if ($product['stock_quantity'] > 0): ?>
                <form class="add-to-cart-form">
                  <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                  <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']); ?>">
                  <input type="hidden" name="price" value="<?= $product['price']; ?>">
                <button class="register-btn" type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            <?php else: ?>
                <button class="register-btn" disabled>Out of Stock</button>
            <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          <?php else: ?>
          <p style="text-align:center;">No products available at the moment.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <div class="go-top">
    <button class="top-btn" onclick="document.getElementById('home').scrollIntoView({behavior:'smooth'});" title="Go to top">Top</button>
  </div>

  
  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
  </footer>
 <script src="../js/script.js"></script>
</body>
</html>
