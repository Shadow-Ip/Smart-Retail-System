<?php
session_start();
require_once '../database/dbConnection.php';
date_default_timezone_set('Africa/Johannesburg');

// ---------- REGISTRATION ----------
$register_errors = [];
$register_success = '';
$old = ['fullname' => '', 'email' => ''];

if (isset($_POST['action']) && $_POST['action'] === 'register') {
  $fullname = trim($_POST['fullname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  $old['fullname'] = $fullname;
  $old['email'] = $email;

  if ($fullname === '' || strlen($fullname) < 3)
    $register_errors[] = "Full name must be at least 3 characters.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $register_errors[] = "Valid email required.";
  if (strlen($password) < 5)
    $register_errors[] = "Password must be at least 5 characters.";

  if (empty($register_errors)) {
    $check = $pdo->prepare("SELECT associate_id FROM sales_associate WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
      $register_errors[] = "This email is already registered. Please log in.";
    } else {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $insert = $pdo->prepare("INSERT INTO sales_associate (associate_name, email, password) VALUES (?, ?, ?)");
      try {
        $insert->execute([$fullname, $email, $hashed]);
        $register_success = "Registration successful. You can now log in.";
        $old = ['fullname' => '', 'email' => ''];
      } catch (PDOException $e) {
        $register_errors[] = "Database error while registering.";
      }
    }
  }
}

// ---------- LOGIN ----------
$login_error = '';
if (isset($_POST['action']) && $_POST['action'] === 'login') {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($email === '' || $password === '') {
    $login_error = "Please fill in all fields.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM sales_associate WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['associate_id'] = $user['associate_id'];
      $_SESSION['associate_name'] = $user['associate_name'];
      header("Location: Sales_Management.php");
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
  <title>Sales Associate Login & Register - Smart Retail System</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body class="backGround">
  <header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
      <ul class="nav-links">
        <li><a href="../Dashboard.php">Back to Main Menu</a></li>
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
        <h1>Sales Associate Login</h1>
        <form class="input-form1" method="post" action="Sales_associate_Login.php">
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
          Don’t have an account?
          <a href="#" onclick="switchForm('registerForm')" style="color:#004aad; font-weight:bold;">Register</a>
        </p>
      </div>

      <!-- Register Form -->
      <div id="registerForm" class="form-slide slide-right">
        <h1>Sales Associate Registration</h1>
        <form class="input-form" method="post" action="Sales_associate_Login.php">
          <input type="hidden" name="action" value="register">

          <label for="fullname">Full Name</label>
          <input id="fullname" name="fullname" type="text" required maxlength="100" placeholder="Enter full name"
            minlength="3" pattern="[A-Za-z\s'.-]+" title="Letters and spaces only"
            value="<?= htmlspecialchars($old['fullname']); ?>" />

          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" required maxlength="100"
            minlength="5" placeholder="associate@email.com"
            value="<?= htmlspecialchars($old['email']); ?>" />

          <label for="password">Password</label>
            <div class="password-wrapper">
              <input id="registerPassword" name="password" type="password" required minlength="5" placeholder="Minimum 5 characters">
              <i class='bx bx-show toggle-password' data-target="registerPassword"></i>
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

  <script src="../js/script.js"></script>
  <script>
  document.querySelectorAll(".toggle-password").forEach(icon => {
  icon.addEventListener("click", () => {
    let target = icon.getAttribute("data-target");
    let input = document.getElementById(target);

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
</body>
</html>
