<?php
session_start();
require_once "includes/db_connect.php";

// Only allow admins
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit;
}

// Handle actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"];

    if ($action === "add") {
        $stmt = $conn->prepare("INSERT INTO menu_items (name, category, price, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $_POST["name"], $_POST["category"], $_POST["price"], $_POST["description"]);
        $stmt->execute();
    }

    if ($action === "delete") {
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
        $stmt->bind_param("i", $_POST["id"]);
        $stmt->execute();
    }

    if ($action === "update_status") {
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $_POST["status"], $_POST["order_id"]);
        $stmt->execute();
    }
}

// Fetch data
$menu_result  = $conn->query("SELECT * FROM menu_items");
$order_result = $conn->query("SELECT o.id, u.username, o.items, o.total_price, o.status, o.created_at
                              FROM orders o 
                              JOIN users u ON o.customer_id = u.id
                              ORDER BY o.id DESC"); // ✅ latest order first
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Cafe Ordering System</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <!-- Header -->
  <header class="admin-header">
    <h1>☕ Cafe Admin Dashboard</h1>
    <nav><a href="logout.php" class="logout-btn">Logout</a></nav>
  </header>

  <div class="dashboard-container">

    <!-- Orders Management (moved above menu) -->
    <section class="dashboard-section">
      <h2>🛒 Manage Orders</h2>
      <table class="orders-table">
        <thead>
          <tr>
            <th>ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Created</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($order = $order_result->fetch_assoc()): ?>
            <tr>
              <td><?= $order["id"] ?></td>
              <td><?= htmlspecialchars($order["username"]) ?></td>
              <td><?= htmlspecialchars($order["items"]) ?></td>
              <td><?= $order["total_price"] ?></td>
              <td><?= $order["status"] ?></td>
              <td><?= $order["created_at"] ?></td>
              <td>
                <!-- Update Status -->
                <form action="admin_dashboard.php" method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="order_id" value="<?= $order["id"] ?>">
                  <select name="status">
                    <option value="pending"   <?= $order["status"]=="pending"?"selected":"" ?>>Pending</option>
                    <option value="preparing" <?= $order["status"]=="preparing"?"selected":"" ?>>Preparing</option>
                    <option value="completed" <?= $order["status"]=="completed"?"selected":"" ?>>Completed</option>
                  </select>
                  <button type="submit" class="update-btn">Update</button>
                </form>

                
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <!-- Menu Management (now below orders) -->
    <section class="dashboard-section">
      <h2>🍴 Manage Menu Items</h2>
      <form action="admin_dashboard.php" method="POST" class="menu-form">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Item name" required>
        <select name="category" required>
          <option value="veg">Veg</option>
          <option value="nonveg">Non-Veg</option>
          <option value="drinks">Drinks</option>
          <option value="desserts">Desserts</option>
        </select>
        <input type="number" name="price" step="0.01" placeholder="Price" required>
        <textarea name="description" placeholder="Description"></textarea>
        <button type="submit">➕ Add Item</button>
      </form>

      <table class="menu-table">
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Description</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $menu_result->fetch_assoc()): ?>
            <tr>
              <td><?= $row["id"] ?></td>
              <td><?= htmlspecialchars($row["name"]) ?></td>
              <td><?= $row["category"] ?></td>
              <td><?= $row["price"] ?></td>
              <td><?= htmlspecialchars($row["description"]) ?></td>
              <td>
                <form action="admin_dashboard.php" method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $row["id"] ?>">
                  <button type="submit" class="delete-btn">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

  </div>
</body>
</html>