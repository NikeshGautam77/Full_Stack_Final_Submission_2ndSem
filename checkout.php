<?php
// checkout.php
session_start();
require_once "includes/db_connect.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.html");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $customer_id = $_SESSION["user_id"];
  $cart_json = $_POST["cart_json"] ?? '';

  if (!$cart_json) {
    $_SESSION["flash_error"] = "Cart is empty.";
    header("Location: index.php");
    exit;
  }

  $items = json_decode($cart_json, true);
  if (!is_array($items) || empty($items)) {
    $_SESSION["flash_error"] = "Invalid cart data.";
    header("Location: index.php");
    exit;
  }

  $total = 0.0;
  foreach ($items as $it) {
    $qty = (int)($it["qty"] ?? 0);
    $price = (float)($it["price"] ?? 0);
    $total += $qty * $price;
  }

  // ✅ Always set status and created_at
  $sql = "INSERT INTO orders (customer_id, items, total_price, status, created_at) 
          VALUES (?, ?, ?, 'pending', NOW())";
  $stmt = $conn->prepare($sql);

  if (!$stmt) {
    $_SESSION["flash_error"] = "Database error: " . $conn->error;
    header("Location: index.php");
    exit;
  }

  $json = json_encode($items, JSON_UNESCAPED_UNICODE);
  $stmt->bind_param("isd", $customer_id, $json, $total);

  if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["flash_success"] = "Order placed successfully!";
    header("Location: my_orders.php");
    exit;
  } else {
    $_SESSION["flash_error"] = "Failed to place order: " . $stmt->error;
    header("Location: index.php");
    exit;
  }
}

header("Location: index.php");
exit;
?>