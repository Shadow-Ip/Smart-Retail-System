<?php
// <------ Start the session ------------ >
session_start();


// <---------- Handle role selection -------------- >
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['role'])) {
        $role = $_POST['role'];

        if ($role === 'customer') {
            $_SESSION['role'] = 'customer';
            header("Location: php/Customer_Shop.php");
            exit;
        } elseif ($role === 'sales_associate') {
            $_SESSION['role'] = 'sales_associate';
            header("Location: php/Sales_associate_Login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smart Retail System - Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <!-- Fixed Navbar -->
  <header class="navbar">
    <div class="logo">🛍️ Smart Retail System</div>
    <nav>
      <ul class="nav-links">
        <li><a href="php/Customer_Shop.php">Continue as Customer</a></li>
        <li><a href="php/Sales_associate_Login.php">Sales Associate</a></li>
      </ul>
    </nav>
  </header>

  <!-- Main Dashboard Section -->
  <main id="home">
    <section class="intro2">
      <h1>Welcome to Smart Retail System</h1>
      <p>Select your access mode below to continue</p>
    </section>

    <section class="dashboard-section">
      <div class="dashboard-grid">
        <!-- Customer Card -->
        <div class="dashboard-card">
          <img src="Pictures/Customer.webp" alt="Customer" />
          <h2>Customer</h2>
          <p>Browse products, manage your cart, and complete purchases easily.</p>
          <form method="POST" action="Dashboard.php">
            <input type="hidden" name="role" value="customer" />
            <button type="submit" class="enter-btn">Enter</button>
          </form>
        </div>

        <!-- Sales Associate Card -->
        <div class="dashboard-card">
          <img src="Pictures/sales associate.jpg" alt="Sales Associate" />
          <h2>Sales Associate</h2>
          <p>Monitor inventory, track sales, and manage customer orders efficiently.</p>
          <form method="POST" action="Dashboard.php">
            <input type="hidden" name="role" value="sales_associate" />
            <button type="submit" class="enter-btn">Enter</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Smart Retail System | All Rights Reserved</p>
  </footer>
</body>
</html>
