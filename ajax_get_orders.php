<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

header('Content-Type: application/json');

// Only allow admins
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$order_result = $conn->query("SELECT o.id, u.username, o.items, o.total_price, o.status, o.created_at
                              FROM orders o
                              JOIN users u ON o.customer_id = u.id
                              ORDER BY o.id DESC");

$orders = [];
while($order = $order_result->fetch_assoc()) {
    $orders[] = $order;
}

echo json_encode([
    "success"=>true,
    "orders"=>$orders,
    "csrf_token"=>generateCsrfToken()
]);