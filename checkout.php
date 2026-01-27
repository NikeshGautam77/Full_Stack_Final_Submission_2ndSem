<?php
session_start();
require_once "includes/db_connect.php";
header("Content-Type: application/json");

if (empty($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "You must be logged in."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = (int)$_SESSION["user_id"];
    $cart_json   = $_POST["cart_json"] ?? '';

    if (empty($cart_json)) {
        echo json_encode(["success" => false, "message" => "Cart is empty."]);
        exit;
    }

    $items = json_decode($cart_json, true);
    if (!is_array($items) || empty($items)) {
        echo json_encode(["success" => false, "message" => "Invalid cart data."]);
        exit;
    }

    $total = 0.0;
    foreach ($items as $it) {
        $qty   = (int)($it["qty"] ?? 0);
        $price = (float)($it["price"] ?? 0);
        if ($qty < 1 || $price < 0) {
            echo json_encode(["success" => false, "message" => "Invalid order data."]);
            exit;
        }
        $total += $qty * $price;
    }

    $sql  = "INSERT INTO orders (customer_id, items, total_price, status, created_at) 
             VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => $conn->error]);
        exit;
    }

    $json = json_encode($items, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param("isd", $customer_id, $json, $total);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Order placed successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    exit;
}
echo json_encode(["success" => false, "message" => "Invalid request"]);
exit;
?>