<?php
// checkout.php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

// ✅ Ensure user is logged in
if (empty($_SESSION["user_id"])) {
    $_SESSION["flash_error"] = "You must be logged in as a customer to place an order.";
    header("Location: login_secure.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ Verify CSRF token
    if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
        $_SESSION["flash_error"] = "Security token invalid. Please try again.";
        header("Location: index.php");
        exit;
    }

    $customer_id = (int)$_SESSION["user_id"];
    $cart_json   = $_POST["cart_json"] ?? '';

    if (empty($cart_json)) {
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

    // ✅ Calculate total
    $total = 0.0;
    foreach ($items as $it) {
        $qty   = (int)($it["qty"] ?? 0);
        $price = (float)($it["price"] ?? 0);

        if ($qty < 1 || $price < 0) {
            $_SESSION["flash_error"] = "Invalid order data.";
            header("Location: index.php");
            exit;
        }
        $total += $qty * $price;
    }

    // ✅ Insert order
    $sql  = "INSERT INTO orders (customer_id, items, total_price, status, created_at) 
             VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $json = json_encode($items, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param("isd", $customer_id, $json, $total);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION["flash_success"] = "Order placed successfully!";
        header("Location: my_orders.php");
        exit;
    } else {
        die("SQL Execute Error: " . $stmt->error);
    }
}

// Default redirect if not POST
header("Location: index.php");
exit;
?>