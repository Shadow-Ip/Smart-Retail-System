<?php
session_start();
require_once '../database/dbConnection.php';

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: Customer_Login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$orders = [];

// Fetch all orders with products
$stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.status,
        p.product_id,
        p.product_name,
        p.price,
        oi.quantity
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$customer_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by order
foreach ($rows as $row) {
    $oid = $row['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'order_id' => $row['order_id'],
            'order_date' => $row['order_date'],
            'status' => $row['status'],
            'total_amount' => $row['total_amount'],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = [
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity']
    ];
}

function productImage($name) {
    $n = strtolower($name);
    if (strpos($n, 'headphone') !== false) return '../Pictures/Headphones.jpg';
    if (strpos($n, 'smartwatch') !== false) return '../Pictures/Smartwatch.jpeg';
    if (strpos($n, 'speaker') !== false) return '../Pictures/speaker.jpg';
    if (strpos($n, 'coffee') !== false) return '../Pictures/Coffee Maker.webp';
    if (strpos($n, 'graphics') !== false) return '../Pictures/Graphics Card.png';
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

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Smart Retail System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="backGround1">
<header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
        <ul class="nav-links">
            <li><a href="Customer_Shop.php">Continue Shopping</a></li>
            <li><a href="Cart.php">🛒 Cart (<?= ($_SESSION['cart_count'] ?? 0) ?> items)</a></li>
            <li><a href="Customer_Logout.php" class="login-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
        </ul>
    </nav>
</header>

<main class="orders-wrapper">
    <h1>My Orders</h1>

    <?php if (empty($orders)): ?>
        <p>You have no orders yet. <a href="Customer_Shop.php">Start shopping</a> 🛍️</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <p><strong>Order #<?= h($order['order_id']); ?></strong></p>
                    <p>Date: <?= date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
                    <p>Status: <span class="status <?= strtolower($order['status']); ?>"><?= h($order['status']); ?></span></p>
                </div>

                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="cart-item">
                            <a href="product_selection.php?product_id=<?= h($item['product_id']); ?>">
                                <img src="<?= productImage($item['product_name']); ?>" 
                                     alt="<?= h($item['product_name']); ?>" 
                                     class="clickable-img">
                            </a>
                            <div class="cart-details">
                                <p><strong><?= h($item['product_name']); ?></strong></p>
                                <p><?= (int)$item['quantity']; ?> × R<?= number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <p><strong>Total:</strong> R<?= number_format($order['total_amount'], 2); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
</footer>
</body>
</html>
