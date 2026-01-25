<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

// Only allow admins
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login_secure.php");
    exit;
}

// Verify CSRF token for POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
        $_SESSION["flash_error"] = "Security token invalid. Please try again.";
        header("Location: admin_dashboard.php");
        exit;
    }

    $action = sanitizeInput($_POST["action"]);

    if ($action === "add") {
        $name = sanitizeInput($_POST["name"]);
        $category = sanitizeInput($_POST["category"]);
        $price = (float)$_POST["price"];
        $description = sanitizeInput($_POST["description"]);

        // Validate inputs
        if (empty($name) || empty($category)) {
            $_SESSION["flash_error"] = "Item name and category required.";
            header("Location: admin_dashboard.php");
            exit;
        }

        if ($price < 0) {
            $_SESSION["flash_error"] = "Price must be positive.";
            header("Location: admin_dashboard.php");
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO menu_items (name, category, price, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $category, $price, $description);
        $stmt->execute();
        $_SESSION["flash_success"] = "Item added successfully.";
    }

    if ($action === "delete") {
        $id = (int)$_POST["id"];
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION["flash_success"] = "Item deleted successfully.";
    }

    if ($action === "update_status") {
        $order_id = (int)$_POST["order_id"];
        $status = sanitizeInput($_POST["status"]);
        
        // Validate status values
        $valid_statuses = ['pending', 'preparing', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            $_SESSION["flash_error"] = "Invalid status.";
            header("Location: admin_dashboard.php");
            exit;
        }

        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $_SESSION["flash_success"] = "Order status updated.";
    }

    header("Location: admin_dashboard.php");
    exit;
}

// Fetch data
$menu_result  = $conn->query("SELECT * FROM menu_items");
$order_result = $conn->query("SELECT o.id, u.username, o.items, o.total_price, o.status, o.created_at
                              FROM orders o 
                              JOIN users u ON o.customer_id = u.id
                              ORDER BY o.id DESC");

$csrf_token = generateCsrfToken();
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

  <?php if (!empty($_SESSION["flash_success"])): ?>
    <div class="flash success" style="margin: 20px; padding: 12px; background-color: #d4edda; color: #155724; border-radius: 4px;"><?= htmlspecialchars($_SESSION["flash_success"]); ?></div>
    <?php unset($_SESSION["flash_success"]); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION["flash_error"])): ?>
    <div class="flash error" style="margin: 20px; padding: 12px; background-color: #f8d7da; color: #721c24; border-radius: 4px;"><?= htmlspecialchars($_SESSION["flash_error"]); ?></div>
    <?php unset($_SESSION["flash_error"]); ?>
  <?php endif; %>

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
              <td><?= htmlspecialchars($order["id"]); ?></td>
              <td><?= htmlspecialchars($order["username"]) ?></td>
              <td><?= htmlspecialchars($order["items"]) ?></td>
              <td><?= htmlspecialchars($order["total_price"]) ?></td>
              <td><?= htmlspecialchars($order["status"]) ?></td>
              <td><?= htmlspecialchars($order["created_at"]) ?></td>
              <td>
                <!-- Update Status -->
                <form action="admin_dashboard.php" method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="order_id" value="<?= htmlspecialchars($order["id"]); ?>">
                  <select name="status">
                    <option value="pending"   <?= htmlspecialchars($order["status"])=="pending"?"selected":"" ?>>Pending</option>
                    <option value="preparing" <?= htmlspecialchars($order["status"])=="preparing"?"selected":"" ?>>Preparing</option>
                    <option value="completed" <?= htmlspecialchars($order["status"])=="completed"?"selected":"" ?>>Completed</option>
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
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Item name" required maxlength="100">
        <select name="category" required>
          <option value="veg">Veg</option>
          <option value="nonveg">Non-Veg</option>
          <option value="drinks">Drinks</option>
          <option value="desserts">Desserts</option>
        </select>
        <input type="number" name="price" step="0.01" placeholder="Price" required min="0">
        <textarea name="description" placeholder="Description" maxlength="500"></textarea>
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
              <td><?= htmlspecialchars($row["id"]); ?></td>
              <td><?= htmlspecialchars($row["name"]) ?></td>
              <td><?= htmlspecialchars($row["category"]) ?></td>
              <td><?= htmlspecialchars($row["price"]); ?></td>
              <td><?= htmlspecialchars($row["description"]) ?></td>
              <td>
                <form action="admin_dashboard.php" method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($row["id"]); ?>">
                  <button type="submit" class="delete-btn" onclick="return confirm('Delete this item?');">Delete</button>
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