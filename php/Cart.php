<?php
// Cart.php
// Shows the user's cart, allows update/remove/clear

session_start();
require_once '../database/dbConnection.php';

// Helper: sanitize simple scalar input
function s($v) {
    return htmlspecialchars(trim((string)($v ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Determine whether request expects a partial fragment 
$isPartial = (isset($_GET['partial']) && $_GET['partial'] == '1');

// POST handling for cart operations: update_quantity, remove_item, clear_cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    //< ---------- Update quantity (session or DB) ---------->
    if ($action === 'update_quantity' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = max(0, (int)($_POST['quantity'] ?? 1));

        if (isset($_SESSION['customer_id'])) {
            $customer_id = $_SESSION['customer_id'];
            if ($quantity > 0) {
                $upd = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE customer_id = ? AND product_id = ?");
                $upd->execute([$quantity, $customer_id, $product_id]);
            } else {
                $del = $pdo->prepare("DELETE FROM cart_items WHERE customer_id = ? AND product_id = ?");
                $del->execute([$customer_id, $product_id]);
            }
        } else {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            foreach ($_SESSION['cart'] as $i => $item) {
                if ($item['product_id'] == $product_id) {
                    if ($quantity > 0) {
                        $_SESSION['cart'][$i]['quantity'] = $quantity;
                    } else {
                        array_splice($_SESSION['cart'], $i, 1);
                    }
                    break;
                }
            }
        }
    }

    // <---------- Remove single item ---------->
    if ($action === 'remove_item' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        if (isset($_SESSION['customer_id'])) {
            $customer_id = $_SESSION['customer_id'];
            $del = $pdo->prepare("DELETE FROM cart_items WHERE customer_id = ? AND product_id = ?");
            $del->execute([$customer_id, $product_id]);
        } else {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            foreach ($_SESSION['cart'] as $i => $item) {
                if ($item['product_id'] == $product_id) {
                    array_splice($_SESSION['cart'], $i, 1);
                    break;
                }
            }
        }
    }

    // <---------- Clear entire cart ---------->
    if ($action === 'clear_cart') {
        if (isset($_SESSION['customer_id'])) {
            $customer_id = $_SESSION['customer_id'];
            $del = $pdo->prepare("DELETE FROM cart_items WHERE customer_id = ?");
            $del->execute([$customer_id]);
        } else {
            unset($_SESSION['cart']);
        }
    }

        // If partial requested, fall through to render partial; otherwise redirect (PRG)
    if (!$isPartial) {
        header("Location: Cart.php");
        exit;
    }

}

// <---------- Fetch cart items (unified representation) ---------->
$cartItems = []; // each item: ['product_id','product_name','price','quantity','image','stock_quantity']

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.product_name, p.price, p.stock_quantity, p.description, c.quantity
        FROM cart_items c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.customer_id = ?
    ");
    $stmt->execute([$customer_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $cartItems[] = [
            'product_id' => (int)$r['product_id'],
            'product_name' => $r['product_name'],
            'price' => (float)$r['price'],
            'quantity' => (int)$r['quantity'],
            'stock_quantity' => (int)$r['stock_quantity'],
            'image' => (function($n){
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
            })($r['product_name'])
        ];
    }
} else {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    foreach ($_SESSION['cart'] as $item) {
        $pid = (int)$item['product_id'];
        $stmt = $pdo->prepare("SELECT product_name, price, stock_quantity FROM products WHERE product_id = ?");
        $stmt->execute([$pid]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($p) {
            $cartItems[] = [
                'product_id' => $pid,
                'product_name' => $p['product_name'],
                'price' => (float)$p['price'],
                'quantity' => max(1, (int)$item['quantity']),
                'stock_quantity' => (int)$p['stock_quantity'],
                'image' => (function($n){
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
                })($p['product_name'])
            ];
        }
    }
}

//< ---------- Compute totals ---------->
$subtotal = 0.0;
foreach ($cartItems as $ci) {
    $subtotal += $ci['price'] * $ci['quantity'];
}

// <---------- Compute total item count for display ---------->
$totalQuantity = 0;
foreach ($cartItems as $ci) {
    $totalQuantity += $ci['quantity'];
}
$_SESSION['cart_count'] = $totalQuantity;

$shipping = ($subtotal > 0 && $subtotal < 650) ? 75.00 : 0.00; // example: R75 shipping under R650
$grandTotal = $subtotal + $shipping;

// <---------- Helper to render cart content (used for full page and for partial) ---------->
function render_cart_content($cartItems, $subtotal, $shipping, $grandTotal) {
    ob_start();
    ?>
    <div class="cart-content-inner">
      <?php if (empty($cartItems)): ?>
        <h1 style="text-align:center;">Your cart is empty.</h1>
      <?php else: ?>
        <div class="cart-items-list">
          <?php foreach ($cartItems as $item): ?>
            <div class="cart-item" data-product-id="<?= (int)$item['product_id']; ?>" style="display:flex; gap:1rem; align-items:center; margin-bottom:1rem;">
              <img src="<?= s($item['image']); ?>" alt="<?= s($item['product_name']); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
              <div style="flex:1;">
                <strong><?= s($item['product_name']); ?></strong><br>
                <small>Unit: R<?= number_format($item['price'],2); ?> &middot; Stock: <?= (int)$item['stock_quantity']; ?></small>
              </div>

              <div style="width:260px; text-align:right;">
                <!-- Visible modern qty controls (JS will handle) -->
                <div class="qty-controls" style="display:inline-flex;align-items:center;gap:6px; margin-bottom:6px;">
                  <button class="qty-btn minus" data-pid="<?= (int)$item['product_id']; ?>" aria-label="Decrease">−</button>
                  <input type="number" class="qty-input" data-pid="<?= (int)$item['product_id']; ?>"
                         min="1" max="<?= max(1,$item['stock_quantity']); ?>" value="<?= (int)$item['quantity']; ?>"
                         style="width:70px;padding:6px;border-radius:8px;border:1px solid #ccc;text-align:center;">
                  <button class="qty-btn plus" data-pid="<?= (int)$item['product_id']; ?>" aria-label="Increase">+</button>
                </div>

                <!-- Keep original update form as a non-JS fallback (visually hidden when JS active) -->
                <noscript>
                  <form method="post" style="display:inline-block;">
                    <input type="hidden" name="action" value="update_quantity">
                    <input type="hidden" name="product_id" value="<?= (int)$item['product_id']; ?>">
                    <input type="number" name="quantity" min="0" max="<?= max(1,$item['stock_quantity']); ?>" value="<?= (int)$item['quantity']; ?>" style="width:70px;padding:6px;border-radius:8px;border:1px solid #ccc;">
                    <button type="submit" class="btn" style="margin-left:6px;">Update</button>
                  </form>
                </noscript>

                <!-- Remove item form -->
                <form method="post" style="display:inline-block; margin-left:6px;">
                  <input type="hidden" name="action" value="remove_item">
                  <input type="hidden" name="product_id" value="<?= (int)$item['product_id']; ?>">
                  <button type="submit" class="btn" onclick="return confirm('Remove this item from your cart?');" style="background:#e74c3c;border: none;">Remove</button>
                </form>

                <div style="margin-top:6px; font-weight:bold;" class="line-total">R<?= number_format($item['price'] * $item['quantity'], 2); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <hr>

        <div class="cart-summary" style="display:flex; justify-content:space-between; align-items:center; gap:1rem;">
          <div>
            <!-- Clear Cart button  -->
            <button class=" primary-btn" onclick="if(confirm('Clear entire cart?')) { document.getElementById('clearCartForm').submit(); }" style="background:#ff6b6b;border:none;color:white;">Clear Cart</button>
          </div>
          <div style="text-align:right; min-width:260px;">
            <p style="margin:0;">Subtotal: <strong>R<span id="subtotal"><?= number_format($subtotal,2); ?></span></strong></p>
            <p style="margin:0;">Shipping: <strong>R<span id="shipping"><?= number_format($shipping,2); ?></span></strong></p>
            <p style="margin:0; font-size:1.15rem;">Total: <strong>R<span id="grandTotal"><?= number_format($grandTotal,2); ?></span></strong></p>
            <div style="margin-top:0.6rem; display:flex; gap:0.6rem; justify-content:flex-end;">
              <a href="Customer_Shop.php" class="btn">Continue Shopping</a>
              <?php if (isset($_SESSION['customer_name'])): ?>
                <a href="Checkout.php" class="btn" style="background:#046bf1;color:#fff;">Proceed to Checkout</a>
              <?php else: ?>
                <a href="Customer_Login.php" class="btn">Please Login</a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Hidden clear cart form (unchanged) -->
        <form id="clearCartForm" method="post" style="display:none;">
          <input type="hidden" name="action" value="clear_cart">
        </form>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// If partial requested, return fragment only and exit
if ($isPartial) {
    echo render_cart_content($cartItems, $subtotal, $shipping, $grandTotal);
    exit;
}

// ---------- Full page rendering ----------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cart - Smart Retail System</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>
  <header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
      <ul class="nav-links">
        <li><a href="Customer_Shop.php">Continue Shopping</a></li>
        <li><a href="Cart.php">🛒 Cart (<?= ($_SESSION['cart_count'] ?? 0) ?> items)</a></li>
        <?php if (isset($_SESSION['customer_name'])): ?>
          <li><a href="Customer_Logout.php" class="login-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
        <?php else: ?>
          <li><a href="Customer_Login.php" class="login-btn">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <main style="margin-top:80px; max-width:1100px; margin-left:auto; margin-right:auto; padding:1rem;">
    <section class="orders-section">
      <h1 style="color:#004aad;">Your Shopping Cart</h1>
      <?= render_cart_content($cartItems, $subtotal, $shipping, $grandTotal); ?>
    </section>
  </main>

  <footer>
    <p style="text-align:center;">&copy; 2025 Smart Retail System | All Rights Reserved</p>
  </footer>

  <script>
 // JS: wire up the + / - controls and live update via AJAX to same file (action=update_quantity)
(function () {
  // Utility to fetch and send update
  async function sendUpdate(productId, quantity) {
    const form = new FormData();
    form.append("action", "update_quantity");
    form.append("product_id", productId);
    form.append("quantity", quantity);
    // POST to same page (Cart.php); server will respond with redirect.
    // I rely on PRG for non-AJAX; here we just send the POST and update UI on success.
    const resp = await fetch("Cart.php?partial=1", {
      method: "POST",
      body: form,
    });
    if (!resp.ok) return false;
    // response contains updated partial HTML — but I made it to update DOM client-side instead of replacing whole fragment
    return true;
  }

  function recalcTotals() {
    let subtotal = 0;
    document.querySelectorAll(".cart-item").forEach((item) => {
      const lineTotalText =
        item.querySelector(".line-total").textContent.replace(/[^\d.]/g, "") ||
        "0";
      subtotal += parseFloat(lineTotalText);
    });
    document.getElementById("subtotal").textContent = subtotal.toFixed(2);

    //  Shipping fee (made it to Match PHP logic)
    const shipping = subtotal > 0 && subtotal < 650 ? 75.0 : 0.0;
    document.getElementById("shipping").textContent = shipping.toFixed(2);
    document.getElementById("grandTotal").textContent = (
      subtotal + shipping
    ).toFixed(2);
  }

  // Attach handlers
  document.querySelectorAll(".cart-item").forEach((item) => {
    const pid = item.dataset.productId || item.getAttribute("data-product-id");
    const minus = item.querySelector(".qty-btn.minus");
    const plus = item.querySelector(".qty-btn.plus");
    const input = item.querySelector(".qty-input");
    const lineTotalEl = item.querySelector(".line-total");
    const unitText = item.querySelector("small")
      ? item.querySelector("small").textContent
      : "";
    const unitPriceMatch = unitText.match(/R([0-9,]*\.?\d+)/);
    const unitPrice = unitPriceMatch
      ? parseFloat(unitPriceMatch[1].replace(/,/g, ""))
      : 0;
    const max = parseInt(input.getAttribute("max") || "9999", 10);

    // helper to set quantity and update UI + server
    async function setQty(newQty) {
      if (newQty < 1) newQty = 1;
      if (newQty > max) newQty = max;
      input.value = newQty;
      // update line total visually
      lineTotalEl.textContent = "R" + (unitPrice * newQty).toFixed(2);
      recalcTotals();
      // send to server
      item.classList.add("fade");
      const ok = await sendUpdate(pid, newQty);
      item.classList.remove("fade");
      // no further action; server state updated
    }

    minus &&
      minus.addEventListener("click", async (e) => {
        e.preventDefault();
        let q = parseInt(input.value || "1", 10);
        if (isNaN(q)) q = 1;
        if (q > 1) {
          await setQty(q - 1);
        } else {
          // If at 1 and user wants to remove, you may choose to remove — I kept minimum 1 here.
        }
      });

    plus &&
      plus.addEventListener("click", async (e) => {
        e.preventDefault();
        let q = parseInt(input.value || "1", 10);
        if (isNaN(q)) q = 1;
        if (q < max) {
          await setQty(q + 1);
        }
      });

    input &&
      input.addEventListener("change", async () => {
        let q = parseInt(input.value || "1", 10);
        if (isNaN(q) || q < 1) q = 1;
        if (q > max) q = max;
        await setQty(q);
      });
  });

  // Prevent double submit on any standard forms
  document.addEventListener("submit", function (e) {
    const btn = e.target.querySelector('button[type="submit"]');
    if (btn) {
      btn.disabled = true;
      setTimeout(() => {
        btn.disabled = false;
      }, 1000);
    }
  });
})();

document.getElementById('clearCartForm')?.addEventListener('submit', async () => {
  setTimeout(refreshCartCount, 1000); // refresh after clearing
});
</script>
</body>
</html>
