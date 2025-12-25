<?php
session_start();
require_once '../database/dbConnection.php';

$successMsg = '';

try {
    // Ensure cart exists
    if (
        (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0)
        && !isset($_SESSION['customer_id'])
    ) {
        throw new Exception("Your cart is empty. Please add items before checking out.");
    }

    // Collect POST data
    $delivery_type = $_POST['delivery_type'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvc = $_POST['card_cvc'] ?? '';

    if (!$fullname || !$email || !$phone || !$address || !$payment_method) {
        throw new Exception("Please fill in all required fields.");
    }

    // Load cart items (for logged-in or guest)
    $cartItems = [];
    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.product_name, p.price, c.quantity 
            FROM cart_items c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.customer_id = ?
        ");
        $stmt->execute([$customer_id]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $cartItems = $_SESSION['cart'] ?? [];
    }

    if (empty($cartItems)) {
        throw new Exception("No items found in your cart.");
    }

    // Calculate total
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $shipping = ($subtotal < 650 && $subtotal > 0) ? 75.00 : 0.00;
    $total_amount = $subtotal + $shipping;

    // Start transaction
    $pdo->beginTransaction();

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_id, total_amount, status)
        VALUES (?, ?, 'Pending')
    ");
    $stmt->execute([$_SESSION['customer_id'] ?? 0, $total_amount]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt_item = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($cartItems as $item) {
        $stmt_item->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // Insert payment record
    $stmt_pay = $pdo->prepare("
        INSERT INTO payments (order_id, amount, payment_method, payment_status, transaction_ref)
        VALUES (?, ?, ?, 'Completed', ?)
    ");
    $transaction_ref = strtoupper(uniqid("TXN"));
    $stmt_pay->execute([$order_id, $total_amount, $payment_method, $transaction_ref]);

    // Commit transaction
    $pdo->commit();

    // Clear cart
    if (isset($_SESSION['customer_id'])) {
        $del = $pdo->prepare("DELETE FROM cart_items WHERE customer_id = ?");
        $del->execute([$_SESSION['customer_id']]);
    }
    unset($_SESSION['cart']);

    // Success message
    $successMsg = "Order successfully placed! Thank you, " . htmlspecialchars($fullname) . ". Your total was R" . number_format($total_amount, 2);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $successMsg = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmation</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="backGround1">

<header class="navbar">
  <div class="logo">🛍️ Smart Retail System</div>
  <nav>
    <ul class="nav-links">
      <li><a href="Customer_Shop.php">Continue Shopping</a></li>
      <li><a href="Orders.php">View Order</a></li>
      <li><a href="Cart.php">View Cart</a></li>
      <li><a href="Customer_Logout.php" class="login-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
    </ul>
  </nav>
</header>

<main class="center-page">
  <!-- Alert Box -->
  <?php if (!empty($successMsg)): ?>
    <div class="alert-box show" id="alertBox">
      <div class="alert success">
        <i class='bx bxs-check-circle'></i>
        <span><?= htmlspecialchars($successMsg); ?></span>
      </div>
    </div>

    <script>
      // Auto fade after 5 seconds
      (function () {
        const alertBox = document.getElementById('alertBox');
        setTimeout(() => {
          if (alertBox) alertBox.classList.add('fade-out');
        }, 5000);
      })();
    </script>
  <?php endif; ?>

  <section class="form-container5">
    <h1>✅ Thank You for Your Purchase!</h1>
    <p>Your order has been placed successfully.</p>
    <p>You will receive a confirmation email shortly.</p>
    <p><a href="Customer_Shop.php" class="btn">Return to Shop</a></p>
  </section>
</main>

<footer>
  <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
</footer>
</body>
</html>
