<?php
session_start();
require_once '../database/dbConnection.php';

// Only allow logged-in associates
if (!isset($_SESSION['associate_id'])) {
    header("Location: Associate_Login.php");
    exit;
}

// Check for required POST fields
if (empty($_POST['order_id']) || empty($_POST['status'])) {
    header("Location: Sales_Management.php?error=Missing+parameters");
    exit;
}

$order_id = (int)$_POST['order_id'];
$status = trim($_POST['status']);
$allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

// Validate
if (!in_array($status, $allowed_statuses)) {
    header("Location: Sales_Management.php?error=Invalid+status");
    exit;
}

try {
    // Confirm order exists
    $check = $pdo->prepare("SELECT order_id FROM orders WHERE order_id = ?");
    $check->execute([$order_id]);
    if (!$check->fetch()) {
        header("Location: Sales_Management.php?error=Order+not+found");
        exit;
    }

    //  Update order status
    $update = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $update->execute([$status, $order_id]);

    // Optional: Record who changed it (if you added a log table)
     $log = $pdo->prepare("INSERT INTO order_activity_log (order_id, associate_id, action, action_date)
     VALUES (?, ?, ?, NOW())");
     $log->execute([$order_id, $_SESSION['associate_id'], 'Status changed to ' . $status]);

    header("Location: Sales_Management.php?success=Order+updated+successfully");
    exit;

} catch (PDOException $e) {
    header("Location: Sales_Management.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
