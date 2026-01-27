<?php
session_start();
require_once "includes/db_connect.php";
error_log("Session user_id: " . $_SESSION["user_id"]);
if (empty($_SESSION["user_id"])) {
    echo "<p>Please log in to view orders.</p>";
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$sql = "SELECT id, items, total_price, status, created_at 
        FROM orders WHERE customer_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No orders found.</p>";
    exit;
}

echo "<table class='orders-table'>
        <thead>
          <tr>
            <th>Order #</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Placed</th>
          </tr>
        </thead>
        <tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["id"] . "</td>";
    echo "<td>";
    $items = json_decode($row["items"], true);
    if (is_array($items)) {
        foreach ($items as $it) {
            echo htmlspecialchars($it["name"]) . " x " . (int)$it["qty"] . "<br>";
        }
    }
    echo "</td>";
    echo "<td>Rs. " . number_format((float)$row["total_price"], 2) . "</td>";
    echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
    echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
    echo "</tr>";
}
echo "</tbody></table>";
?>