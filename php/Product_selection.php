<?php
// <------ Start the session ------------ >
session_start();

require_once '../database/dbConnection.php';
$successMsg = '';

// < ----------- Fetch Product Details ------------ >
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    die("<script>alert('Invalid product selection.'); window.location.href='Customer_Shop.php';</script>");
}
$product_id = (int)$_GET['product_id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("<script>alert('Product not found.'); window.location.href='Customer_Shop.php';</script>");
}

// <----------- Image Mapping (Different thumbnails per product) -------------- >
function getProductImages($name) {
    $name = strtolower($name);
    if (strpos($name, 'headphone') !== false)
        return ['../Pictures/Headphones.jpg', '../Pictures/Headphones1.jpg','../Pictures/Headphones2.jpg','../Pictures/Headphones3.jpg'];
    if (strpos($name, 'smartwatch') !== false)
        return ['../Pictures/Smartwatch.jpeg', '../Pictures/Smartwatch1.jpg', '../Pictures/Smartwatch2.jpg', '../Pictures/Smartwatch3.jpg'];
    if (strpos($name, 'speaker') !== false)
        return ['../Pictures/speaker.jpg','../Pictures/speaker1.jpg', '../Pictures/speaker2.jpg', '../Pictures/speaker3.jpg'];
    if (strpos($name, 'coffee') !== false)
        return ['../Pictures/Coffee Maker.webp','../Pictures/Coffee Maker1.jpg', '../Pictures/Coffee Maker2.jpg', '../Pictures/Coffee Maker3.jpg'];
    if (strpos($name, 'graphics') !== false)
        return ['../Pictures/Graphics Card.png','../Pictures/Graphics Card1.jpg', '../Pictures/Graphics Card2.jpg', '../Pictures/Graphics Card3.jpg'];
    if (strpos($name, 'monitor') !== false)
        return ['../Pictures/Gaming Monitor.png','../Pictures/GamingMonitor1.png', '../Pictures/GamingMonitor2.png', '../Pictures/GamingMonitor3.png'];
    if (strpos($name, 'laptop') !== false)
        return ['../Pictures/laptop.png','../Pictures/laptop1.png', '../Pictures/laptop2.png', '../Pictures/laptop3.png'];
    if (strpos($name, 'tree') !== false)
        return ['../Pictures/Christmas Tree1.png', '../Pictures/Christmas1.jpeg', '../Pictures/Christmas2.png','../Pictures/Christmas3.png'];
    if (strpos($name, 'converter') !== false)
        return['../Pictures/DVI Converter.png', '../Pictures/DVI Converter1.png', '../Pictures/DVI Converter2.png', '../Pictures/DVI Converter3.png'];
    if (strpos($name, 'grinder') !== false) 
        return ['../Pictures/Grinder.png', '../Pictures/Grinder1.png','../Pictures/Grinder2.png','../Pictures/Grinder3.png' ];
    if (strpos($name, 'frye') !== false) 
        return ['../Pictures/Air Frye.png','../Pictures/Air Frye1.png','../Pictures/Air Frye2.png','../Pictures/Air Frye4.png','../Pictures/Air Frye5.png','../Pictures/Air Frye3.png'];
    if (strpos($name, 'fridge') !== false) 
        return ['../Pictures/Bottom Freezer Fridge.png','../Pictures/Bottom Freezer Fridge1.png','../Pictures/Bottom Freezer Fridge2.png','../Pictures/Bottom Freezer Fridge3.png','../Pictures/Bottom Freezer Fridge4.png',];
    if (strpos($name, 'motherboard') !== false) 
        return ['../Pictures/MotherBoard.png','../Pictures/MotherBoard1.png','../Pictures/MotherBoard2.png','../Pictures/MotherBoard3.png','../Pictures/MotherBoard4.png',];
    if (strpos($name, 'whiskey') !== false) 
        return ['../Pictures/Whiskey1.png'];
    if (strpos($name, 'whisky') !== false) 
        return ['../Pictures/Whisky.png','../Pictures/Whisky1.png', '../Pictures/Whisky3.png'];
    return ['../Pictures/default.jpg'];
}

$images = getProductImages($product['product_name']);


// < ----------------- ADD TO CART ----------------- >
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
        // Logged-in user — store cart in DB
        $customer_id = $_SESSION['customer_id'];

        // Check if product already exists in their cart
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
    
    // <------- Update session cart count instantly -------->
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $_SESSION['cart_count'] = $stmt->fetchColumn();
} else {
    $_SESSION['cart_count'] = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}
$successMsg = "Successfully added product to cart!";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($product['product_name']); ?> - Smart Retail System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
<header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
        <ul class="nav-links">
            <li><a href="Customer_Shop.php">Back to Shopping</a></li>
            <li><a href="Cart.php">🛒 Cart (<?= ($_SESSION['cart_count'] ?? 0) ?> items)</a></li>
            <li><a href="Orders.php">View Orders</a></li>
            <!-- Change Button depending on the user -->
            <?php if (isset($_SESSION['customer_name'])): ?>
                <li><a href="Customer_Logout.php" class="login-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
            <?php else: ?>
                <li><a href="Customer_Login.php" class="login-btn">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="mt">
    <!-- Alert Box-->
    <?php if (!empty($successMsg)): ?>
    <div class="alert-box show" id="alertBox">
        <div class="alert success">
            <i class='bx bxs-check-circle'></i>
            <span><?= htmlspecialchars($successMsg); ?></span>
        </div>
    </div>

<script>
(function() {
    const alertBox = document.getElementById('alertBox');
    // Fade the alert
    setTimeout(() => alertBox?.classList.add('fade-out'), 4500);
})();
</script>
    <?php endif; ?>

    <div class="product-detail-container">
        <div class="product-images">
            <img id="mainImage" src="<?= $images[0]; ?>" alt="<?= htmlspecialchars($product['product_name']); ?>" class="main-image" />
            <div class="thumbnail-row">
                <?php foreach ($images as $i => $img): ?>
                    <img src="<?= $img; ?>" alt="View <?= $i+1; ?>" class="thumb <?= $i === 0 ? 'active-thumb' : ''; ?>" />
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-info">
            <h2><?= htmlspecialchars($product['product_name']); ?></h2>
            <p class="desc"><?= htmlspecialchars($product['description']); ?></p>
            <strong>R<?= number_format($product['price'], 2); ?></strong>
            <p class="mt1">
                <?= ($product['stock_quantity'] > 0) ? "✅ In Stock ({$product['stock_quantity']} available)" : "❌ Out of Stock"; ?>
            </p>

            <?php if ($product['stock_quantity'] > 0): ?>
            <form class="input-form" method="post" action="">
                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>" />
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']); ?>" />
                <input type="hidden" name="price" value="<?= $product['price']; ?>" />

                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" max="<?= $product['stock_quantity']; ?>" required value="1" />
                <button class="register-btn" type="submit" name="add_to_cart">Add to Cart</button>
            </form>
            <?php else: ?>
                <button class="register-btn" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
</footer>

<!-- Thumbnail Switcher -->
<script>
const mainImage = document.getElementById('mainImage');
const thumbnails = document.querySelectorAll('.thumbnail-row img');
thumbnails.forEach(thumb => {
  thumb.addEventListener('click', () => {
    mainImage.classList.add('fade-out');
    setTimeout(() => {
      mainImage.src = thumb.src;
      mainImage.classList.remove('fade-out');
    }, 300);
    thumbnails.forEach(t => t.classList.remove('active-thumb'));
    thumb.classList.add('active-thumb');
  });
});
</script>
</body>
</html>
