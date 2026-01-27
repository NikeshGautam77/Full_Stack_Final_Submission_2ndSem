<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

header('Content-Type: application/json');

// Only admin allowed
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// CSRF check
if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
    echo json_encode(["success" => false, "message" => "Invalid CSRF token"]);
    exit;
}

$order_id = (int)($_POST["order_id"] ?? 0);
$status   = sanitizeInput($_POST["status"] ?? "");

$valid_statuses = ['pending', 'preparing', 'completed'];
if (!$order_id || !in_array($status, $valid_statuses)) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Order status updated"]);
