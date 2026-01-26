<?php
session_start();
require_once "includes/db_connect.php";

$name     = $_POST["name"] ?? "";
$category = $_POST["category"] ?? "";
$minPrice = $_POST["minPrice"] ?? "";
$maxPrice = $_POST["maxPrice"] ?? "";

$sql = "SELECT * FROM menu_items WHERE available=1";
$params = [];
$types  = "";

// Name filter
if (!empty($name)) {
  $sql .= " AND name LIKE ?";
  $params[] = "%$name%";
  $types   .= "s";
}

// Category filter
if (!empty($category)) {
  $sql .= " AND category = ?";
  $params[] = $category;
  $types   .= "s";
}

// Min price filter
if ($minPrice !== "" && is_numeric($minPrice)) {
  $sql .= " AND price >= ?";
  $params[] = (float)$minPrice;
  $types   .= "d";
}

// Max price filter
if ($maxPrice !== "" && is_numeric($maxPrice)) {
  $sql .= " AND price <= ?";
  $params[] = (float)$maxPrice;
  $types   .= "d";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<p>No items found.</p>";
}

while ($row = $result->fetch_assoc()) {
  echo "<div class='menu-card'>";
  echo "<h4>" . htmlspecialchars($row['name']) . "</h4>";
  echo "<p>Rs. " . htmlspecialchars($row['price']) . "</p>";
  echo "<button onclick=\"addToCart('" . addslashes($row['name']) . "', " . $row['price'] . ")\">Add to Cart</button>";
  echo "</div>";
}
?>