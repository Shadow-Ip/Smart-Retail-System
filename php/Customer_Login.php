<?php
session_start();
require_once '../database/dbConnection.php';
date_default_timezone_set('Africa/Johannesburg');

//  < ---------- REGISTRATION HANDLING ---------- >
$register_errors = [];
$register_success = '';
$old = [
  'fullname' => '',
  'email'    => '',
  'phone'    => '',
  'address'  => ''
];

if (isset($_POST['action']) && $_POST['action'] === 'register') {
  $fullname = trim($_POST['fullname'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $phone    = trim($_POST['phone'] ?? '');
  $address  = trim($_POST['address'] ?? '');

  $old['fullname'] = $fullname;
  $old['email']    = $email;
  $old['phone']    = $phone;
  $old['address']  = $address;

  if ($fullname === '' || strlen($fullname) < 3 || strlen($fullname) > 100)
    $register_errors[] = "Full name must be between 3 and 100 characters.";
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $register_errors[] = "A valid email address is required.";
  if ($password === '' || strlen($password) < 5)
    $register_errors[] = "Password must be at least 5 characters.";
  if (!preg_match('/^[0-9]{10}$/', $phone))
    $register_errors[] = "Phone number must be exactly 10 digits.";
  if (strlen($address) < 5 || strlen($address) > 500)
    $register_errors[] = "Address must be between 5 and 500 characters.";

  if (empty($register_errors)) {
    $dupCheck = $pdo->prepare("SELECT customer_id FROM customers WHERE email = ? LIMIT 1");
    $dupCheck->execute([$email]);
    $duplicate = $dupCheck->fetch();

    if ($duplicate) {
      $register_errors[] = "This email address is already registered. Please login instead.";
    } else {
      $passwordHash = password_hash($password, PASSWORD_DEFAULT);
      $registrationTimestamp = date('Y-m-d H:i:s');

      $insert = $pdo->prepare("
        INSERT INTO customers (full_name, email, password, phone, address, registration_date)
        VALUES (?, ?, ?, ?, ?, ?)
      ");
      try {
        $insert->execute([$fullname, $email, $passwordHash, $phone, $address, $registrationTimestamp]);
        $register_success = "Registration successful. You can now log in.";
        $old = ['fullname'=>'','email'=>'','phone'=>'','address'=>''];
      } catch (PDOException $ex) {
        $register_errors[] = "Failed to register. Please try again later.";
      }
    }
  }
}

// < ---------- LOGIN HANDLING ---------- >
$login_error = '';
if (isset($_POST['action']) && $_POST['action'] === 'login') {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($email === '' || $password === '') {
    $login_error = "Please fill in all fields.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['customer_id'] = $user['customer_id'];
      $_SESSION['customer_name'] = $user['full_name'];
      header("Location: Customer_Shop.php");

      // Load saved cart from database into session
$stmt = $pdo->prepare("
    SELECT p.product_id, p.product_name, p.price, c.quantity 
    FROM cart_items c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.customer_id = ?
");
$stmt->execute([$_SESSION['customer_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_SESSION['cart'] = [];
$_SESSION['cart_count'] = count($_SESSION['cart']);
foreach ($cartItems as $item) {
    $_SESSION['cart'][] = [
        'product_id' => $item['product_id'],
        'product_name' => $item['product_name'],
        'price' => $item['price'],
        'quantity' => $item['quantity']
    ];
}

      exit;
    } else {
      $login_error = "Invalid email or password.";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Customer Login & Register - Smart Retail System</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body class="backGround">
  <header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
      <ul class="nav-links">
        <li><a href="../Dashboard.php">Exit to Menu</a></li>
        <li><a href="Customer_Shop.php">Back to Shop</a></li>
      </ul>
    </nav>
  </header>

  <main><br/>
    <div class="form-container slide-container">
      <!-- Alert Box -->
      <?php if (!empty($register_errors) || $register_success || $login_error): ?>
        <div class="alert-box show">
          <div class="alert <?= $register_success ? 'success' : ($login_error ? 'error' : 'error') ?>">
            <i class='bx <?= $register_success ? 'bxs-check-circle' : 'bxs-x-circle' ?>'></i>
            <span>
              <?php
                if (!empty($register_errors)) {
                  foreach ($register_errors as $err) echo htmlspecialchars($err) . " ";
                }
                if ($register_success) echo htmlspecialchars($register_success);
                if ($login_error) echo htmlspecialchars($login_error);
              ?>
            </span>
          </div>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <div id="loginForm" class="form-slide active slide-left">
        <h1>Customer Login</h1>
        <form class="input-form1" method="post" action="Customer_Login.php">
          <input type="hidden" name="action" value="login">
          <label for="email">Email</label>
          <input type="email" name="email" placeholder="Enter your email" maxlength="100" minlength="5" required>

          <label for="password">Password</label>
            <div class="password-wrapper">
            <input type="password" id="loginPassword" name="password" placeholder="Enter your password" minlength="5" required>
            <i class='bx bx-show toggle-password' data-target="loginPassword"></i>
            </div>


          <button type="submit" class="login-b">Login</button>
        </form>
        <p style="text-align:center; margin-top:1rem;">
          Don't have an account?
          <a href="#" onclick="switchForm('registerForm')" style="color:#004aad; font-weight:bold;">Register</a>
        </p>
      </div>

<!-- Register Form -->
<div id="registerForm" class="form-slide slide-right">
  <h1>Customer Registration</h1>
  <form class="input-form register-grid" method="post" action="Customer_Login.php">
    <input type="hidden" name="action" value="register">

    <div class="grid-pair">
      <div>
        <label for="fullname">Full Name</label>
        <input id="fullname" name="fullname" type="text" required maxlength="100" placeholder="Enter full names"
          minlength="3" pattern="[A-Za-z\s'.-]+" title="Letters and spaces only"
          value="<?php echo htmlspecialchars($old['fullname']); ?>" />
      </div>

      <div>
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" required maxlength="100" minlength="6"
          placeholder="customer@email.com"
          value="<?php echo htmlspecialchars($old['email']); ?>" />
      </div>
    </div>

    <div class="grid-pair">
      <div>
        <label for="password">Password</label>
          <div class="password-wrapper">
          <input id="registerPassword" name="password" type="password" required minlength="5" placeholder="Minimum 5 characters">
          <i class='bx bx-show toggle-password' data-target="registerPassword"></i>
          </div>
      </div>

      <div>
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" type="tel" pattern="[0-9]{10}" maxlength="10" required
          minlength="10" placeholder="0821234567"
          value="<?php echo htmlspecialchars($old['phone']); ?>" />
      </div>
    </div>

    <div>
      <label for="address">Address</label>
      <textarea id="address" name="address" rows="3" required maxlength="300" minlength="5" title="Street, City, Postal Code"
        placeholder="Street, City, Postal Code"><?php echo htmlspecialchars($old['address']); ?></textarea>
    </div>

    <div style="text-align:center;">
      <button class="register-btn" type="submit">Register</button>
    </div>
  </form>

  <p style="text-align:center; margin-top:1rem;">
    Already have an account?
    <a href="#" onclick="switchForm('loginForm')" style="color:#004aad; font-weight:bold;">Login</a>
  </p>
</div>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
  </footer>
  <script> 
    document.querySelectorAll(".toggle-password").forEach(icon => {
  icon.addEventListener("click", () => {
    let targetId = icon.getAttribute("data-target");
    let input = document.getElementById(targetId);

    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("bx-show");
      icon.classList.add("bx-hide");
    } else {
      input.type = "password";
      icon.classList.remove("bx-hide");
      icon.classList.add("bx-show");
    }
  });
});

  </script>
  <script src="../js/script.js"></script>
</body>
</html>
