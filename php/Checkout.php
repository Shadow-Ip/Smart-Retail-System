<?php
session_start();
require_once '../database/dbConnection.php';

// <---------------- Load Cart Data (same logic as Cart.php) ---------------->
$cartItems = [];
$subtotal = 0.0;
$shipping = 0.0;
$grandTotal = 0.0;

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
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $pid = (int)$item['product_id'];
            $q = (int)$item['quantity'];
            $stmt = $pdo->prepare("SELECT product_id, product_name, price FROM products WHERE product_id = ?");
            $stmt->execute([$pid]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($p) {
                $p['quantity'] = $q;
                $cartItems[] = $p;
            }
        }
    }
}



// Calculate totals
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = ($subtotal > 0 && $subtotal < 650) ? 75.00 : 0.00;
$grandTotal = $subtotal + $shipping;

// Helper: escape HTML
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout - Smart Retail System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body class="backGround1">
    <header class="navbar">
        <div class="logo">🛍️ Smart Retail System</div>
        <nav>
            <ul class="nav-links">
                <li><a href="Cart.php">🛒 Back to Cart</a></li>
                <li><a href="Customer_Shop.php" >Continue Shopping</a></li>
                <?php if (isset($_SESSION['customer_name'])): ?>
                    <li><a href="Customer_Logout.php" class="login-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
                <?php else: ?>
                    <li><a href="Customer_Login.php" class="login-btn">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>


    <main class="checkout-wrapper">
        <!-- LEFT SIDE: Form -->
        <section class="checkout-left">
            <h1>Shipping & Payment Information</h1>
            <form id="checkout-form" class="input-form2" method="post" action="Process_Order.php">
                <label>Delivery Option</label>
                <select name="delivery_type" required>
                    <option value="" disabled selected>Delivery / Pick-up?</option>
                    <option value="Delivery">Delivery</option>
                    <option value="Pickup">Pick-up</option>
                </select>

                <label for="fullname">Full Name</label>
                <input id="fullname" name="fullname" type="text" required minlength="3" maxlength="100"
                    placeholder="Enter full name" />

                <label for="email">Email Address </label>
                <input id="email" name="email" type="email" required minlength="6" maxlength="100"
                    placeholder="Enter email address" title="Customer@email.com"/>

                <label for="phone">Phone Number </label>
                <input id="phone" name="phone" type="tel" required pattern="[0-9]{9,15}" minlength="10" maxlength="10"
                    placeholder="Enter phone number" title="0720123456"/>

                <label for="address">Address </label>
                <textarea id="address" name="address" rows="3" minlength="6" maxlength="200"
                    placeholder="Street, City, Postal Code" required></textarea>

                <h3 class="mt2">Payment Method</h3>
                <select id="payment_method" name="payment_method" required>
                    <option value="" disabled selected>Select Payment Method</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="EFT">EFT (Online Transfer)</option>
                    <option value="Cash">Cash on Delivery</option>
                </select>

            
                <label for="card_number">Card Number</label>
                <input id="card_number" name="card_number" type="text" minlength="12" maxlength="19"
                    pattern="[0-9]{13,19}" placeholder="1111222233334444" />

                <label for="card_expiry">Expiry Date (MM/YY)</label>
                <input id="card_expiry" name="card_expiry" type="text" minlength="4" maxlength="5" pattern="[0-9]{2}/[0-9]{2}"
                    placeholder="08/27" title="(MM/YY)" />

                <label for="card_cvc">CVC</label>
                <input id="card_cvc" name="card_cvc" type="text" minlength="3" maxlength="3"
                    pattern="[0-9]{3,4}" placeholder="123" />

                <label class="mt3">
                    <input type="checkbox" required /> I have read and agree to the Terms and Conditions.
                </label>
           
            </form>
        </section>

        <!-- RIGHT SIDE: Cart Summary -->
        <aside class="checkout-right">
            <h1>Review Your Cart</h1>

            <?php if (empty($cartItems)): ?>
                <p>Your cart is empty. <a href="Customer_Shop.php">Continue shopping</a>.</p>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="<?=
                            (function($n){
                                $n = strtolower($n);
                                if (strpos($n,'headphone')!==false) return '../Pictures/Headphones.jpg';
                                if (strpos($n,'smartwatch')!==false) return '../Pictures/Smartwatch.jpeg';
                                if (strpos($n,'speaker')!==false) return '../Pictures/speaker.jpg';
                                if (strpos($n,'coffee')!==false) return '../Pictures/Coffee Maker.webp';
                                if (strpos($n,'graphics')!==false) return '../Pictures/Graphics Card.png';
                                if (strpos($n,'monitor')!==false) return '../Pictures/Gaming Monitor.png';
                                if (strpos($n,'laptop')!==false) return '../Pictures/laptop.png';
                                if (strpos($n,'tree')!==false) return '../Pictures/Christmas Tree1.png';
                                if (strpos($n, 'converter')!== false) return '../Pictures/DVI Converter.png';
                                if (strpos($n, 'grinder') !== false) return '../Pictures/Grinder.png';
                                if (strpos($n, 'frye') !== false) return '../Pictures/Air Frye.png';
                                if (strpos($n, 'fridge') !== false) return '../Pictures/Bottom Freezer Fridge.png';
                                if (strpos($n, 'motherboard') !== false) return '../Pictures/MotherBoard.png';
                                if (strpos($n, 'whiskey') !== false) return '../Pictures/Whiskey1.png';
                                if (strpos($n, 'whisky') !== false) return '../Pictures/Whisky.png';
                                return '../Pictures/default.jpg';
                            })($item['product_name']);
                        ?>" alt="<?= h($item['product_name']); ?>" />
                        <div class="cart-details">
                            <p><strong><?= h($item['product_name']); ?></strong></p>
                            <p><?= (int)$item['quantity']; ?> x R<?= number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <p><span>Subtotal:</span><span>R<?= number_format($subtotal, 2); ?></span></p>
                    <p><span>Shipping:</span><span>R<?= number_format($shipping, 2); ?></span></p>
                    <p class="total"><span>Total:</span><span>R<?= number_format($grandTotal, 2); ?></span></p>
                </div>

                <div>
                    <button class="pay-btn" type="submit" form="checkout-form">Pay Now</button>
                </div>
                <div class="secure-note">
                    Secure Checkout - SSL Encrypted <br>
                    Your financial and personal details are protected during every transaction.
                </div>
            <?php endif; ?>
        </aside>
    </main>

    <footer>
        <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
    </footer>
    

<script>
document.addEventListener("DOMContentLoaded", function () {
  const paymentSelect = document.getElementById("payment_method");
  const cardFields = [
    document.getElementById("card_number"),
    document.getElementById("card_expiry"),
    document.getElementById("card_cvc"),
  ];

  // Helper: hide associated <label> with the field
  function toggleField(field, show) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    if (label) label.style.display = show ? "block" : "none";
    field.style.display = show ? "block" : "none";
  }

  // Hide all card fields on load
  cardFields.forEach((field) => toggleField(field, false));

  // Listen for payment method change
  paymentSelect.addEventListener("change", function () {
    const showCard = this.value === "Credit Card";
    cardFields.forEach((field) => toggleField(field, showCard));
    if (!showCard) {
      cardFields.forEach((field) => (field.value = "")); // Clear when hidden
    }
  });
});
</script>

</body>
</html>
