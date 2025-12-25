<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Optionally clear guest cart but keep DB cart safe
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['cart']);

// Destroy session data on the server
session_destroy();

// Optionally clear session cookie (extra safety)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to login page (or homepage)
header("Location: Customer_Login.php");
exit;
?>
