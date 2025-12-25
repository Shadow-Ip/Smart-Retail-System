<?php
require_once '../database/dbConnection.php';
header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_POST['product_id'], $_POST['quantity'])) {
  $product_id = (int)$_POST['product_id'];
  $quantity = (int)$_POST['quantity'];

  if ($product_id > 0 && $quantity > 0) {
    try {
      // Update stock
      $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
      $stmt->execute([$quantity, $product_id]);

      // Fetch new quantity
      $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
      $stmt->execute([$product_id]);
      $new_qty = $stmt->fetchColumn();

      $response = [
        'success' => true,
        'new_quantity' => (int)$new_qty
      ];
    } catch (PDOException $e) {
      $response['error'] = $e->getMessage();
    }
  }
}

echo json_encode($response);
exit;
?>
