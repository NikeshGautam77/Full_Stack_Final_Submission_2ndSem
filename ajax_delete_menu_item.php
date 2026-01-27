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

// CSRF check
if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
    echo json_encode(["success" => false, "message" => "Invalid CSRF token"]);
    exit;
}

$id = (int)($_POST["id"] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid ID"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode([
    "success" => true,
    "message" => "Item deleted"
]);
