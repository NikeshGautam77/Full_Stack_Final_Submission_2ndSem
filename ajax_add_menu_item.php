<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

header('Content-Type: application/json');

// Admin check
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// CSRF
if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
    echo json_encode(["success" => false, "message" => "Invalid CSRF token"]);
    exit;
}

// Inputs
$name        = sanitizeInput($_POST["name"] ?? "");
$category    = sanitizeInput($_POST["category"] ?? "");
$price       = (float)($_POST["price"] ?? 0);
$description = sanitizeInput($_POST["description"] ?? "");

// Validation
if (empty($name) || empty($category)) {
    echo json_encode(["success" => false, "message" => "Name & category required"]);
    exit;
}

if ($price < 0) {
    echo json_encode(["success" => false, "message" => "Invalid price"]);
    exit;
}

// Insert
$stmt = $conn->prepare(
    "INSERT INTO menu_items (name, category, price, description) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssds", $name, $category, $price, $description);
$stmt->execute();

$newId = $stmt->insert_id;

echo json_encode([
    "success" => true,
    "message" => "Item added successfully",
    "item" => [
        "id" => $newId,
        "name" => $name,
        "category" => $category,
        "price" => $price,
        "description" => $description
    ]
]);
