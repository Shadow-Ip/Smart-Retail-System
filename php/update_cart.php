<?php
session_start();
require_once '../database/dbConnection.php';

$response = ['success' => false, 'cart_count' => 0];

// --- If product info provided (Add to cart) ---
if (isset($_POST['product_id'], $_POST['product_name'], $_POST['price'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];

    // Logged-in user
    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];

        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE customer_id = ? AND product_id = ?");
        $stmt->execute([$customer_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = $existing['quantity'] + 1;
            $update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE customer_id = ? AND product_id = ?");
            $update->execute([$newQty, $customer_id, $product_id]);
        } else {
            $insert = $pdo->prepare("INSERT INTO cart_items (customer_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->execute([$customer_id, $product_id, 1]);
        }
    } 
    // Guest user
// ----------------- IF GUEST USER -----------------
 else {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $quantity = 1; // default

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            // ADD quantity properly, don't duplicate
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

    $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
    $response['cart_count'] = $_SESSION['cart_count'];
    $response['success'] = true;
}

}

// --- Always recalculate latest count (even for GET calls) ---
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart_items WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $_SESSION['cart_count'] = (int)$stmt->fetchColumn();
} else {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    $_SESSION['cart_count'] = $count;
}

$response['cart_count'] = $_SESSION['cart_count'];
$response['success'] = true;

header('Content-Type: application/json');
echo json_encode($response);
exit;
