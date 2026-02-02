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
  <link rel="stylesheet" href="CSS/admin.css">
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
  <?php endif; ?>

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
                <form class="status-form" data-order-id="<?= htmlspecialchars($order["id"]); ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                    
                    <select name="status">
                      <option value="pending"   <?= $order["status"]=="pending"?"selected":"" ?>>Pending</option>
                      <option value="preparing" <?= $order["status"]=="preparing"?"selected":"" ?>>Preparing</option>
                      <option value="completed" <?= $order["status"]=="completed"?"selected":"" ?>>Completed</option>
                    </select>

                    <button type="submit" class="update-btn">Update</button>
                    <span class="status-msg" style="margin-left:8px;"></span>
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
      <form id="add-item-form" class="menu-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

        <input type="text" name="name" placeholder="Item name" required>
        
        <select name="category" required>
          <option value="veg">Veg</option>
          <option value="nonveg">Non-Veg</option>
          <option value="drinks">Drinks</option>
          <option value="desserts">Desserts</option>
        </select>

        <input type="number" name="price" step="0.01" min="0" placeholder="Price" required>
        
        <textarea name="description" placeholder="Description"></textarea>

        <button type="submit">➕ Add Item</button>
        <span id="add-item-msg" style="margin-left:10px;"></span>
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
                <button class="delete-btn"
                        data-id="<?= htmlspecialchars($row["id"]); ?>"
                        data-csrf="<?= htmlspecialchars($csrf_token); ?>">
                  Delete
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

  </div>

  <script>
document.querySelectorAll('.status-form').forEach(form => {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const orderId = this.dataset.orderId;
    const status  = this.querySelector('select[name="status"]').value;
    const csrf    = this.querySelector('input[name="csrf_token"]').value;
    const msgBox  = this.querySelector('.status-msg');

    msgBox.textContent = 'Updating...';

    fetch('ajax_update_order_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        order_id: orderId,
        status: status,
        csrf_token: csrf
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        msgBox.textContent = '✅ Updated';
        msgBox.style.color = 'green';
      } else {
        msgBox.textContent = '❌ ' + data.message;
        msgBox.style.color = 'red';
      }
    })
    .catch(() => {
      msgBox.textContent = '❌ Error';
      msgBox.style.color = 'red';
    });
  });
});



// ================= ADD ITEM =================
document.getElementById('add-item-form').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = this;
  const msg  = document.getElementById('add-item-msg');
  const tbody = document.querySelector('.menu-table tbody');

  msg.textContent = 'Adding...';

  fetch('ajax_add_menu_item.php', {
    method: 'POST',
    body: new FormData(form)
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) {
      msg.textContent = '❌ ' + data.message;
      msg.style.color = 'red';
      return;
    }

    msg.textContent = '✅ Added';
    msg.style.color = 'green';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${data.item.id}</td>
      <td>${data.item.name}</td>
      <td>${data.item.category}</td>
      <td>${data.item.price}</td>
      <td>${data.item.description}</td>
      <td>
        <button class="delete-btn"
                data-id="${data.item.id}"
                data-csrf="${form.querySelector('[name=csrf_token]').value}">
          Delete
        </button>
      </td>
    `;

    tbody.prepend(tr);
    form.reset();
  });
});

// ================= DELETE ITEM =================
document.addEventListener('click', function(e) {
  if (!e.target.classList.contains('delete-btn')) return;

  if (!confirm('Delete this item?')) return;

  const btn  = e.target;
  const id   = btn.dataset.id;
  const csrf = btn.dataset.csrf;
  const row  = btn.closest('tr');

  fetch('ajax_delete_menu_item.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      id: id,
      csrf_token: csrf
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      row.remove();
    } else {
      alert('❌ ' + data.message);
    }
  });
});

function bindStatusForms() {
  document.querySelectorAll('.status-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const orderId = this.dataset.orderId;
      const status  = this.querySelector('select[name="status"]').value;
      const csrf    = this.querySelector('input[name="csrf_token"]').value;
      const msgBox  = this.querySelector('.status-msg');

      msgBox.textContent = 'Updating...';

      fetch('ajax_update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ order_id: orderId, status: status, csrf_token: csrf })
      })
      .then(res => res.json())
      .then(data => {
        msgBox.textContent = data.success ? '✅ Updated' : '❌ ' + data.message;
        msgBox.style.color = data.success ? 'green' : 'red';
      })
      .catch(() => {
        msgBox.textContent = '❌ Error';
        msgBox.style.color = 'red';
      });
    });
  });
}

function refreshOrders() {
  fetch('ajax_get_orders.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;
      const tbody = document.querySelector('.orders-table tbody');
      tbody.innerHTML = '';
      data.orders.forEach(order => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${order.id}</td>
          <td>${order.username}</td>
          <td>${order.items}</td>
          <td>${order.total_price}</td>
          <td>${order.status}</td>
          <td>${order.created_at}</td>
          <td>
            <form class="status-form" data-order-id="${order.id}">
              <input type="hidden" name="csrf_token" value="${data.csrf_token}">
              <select name="status">
                <option value="pending" ${order.status=="pending"?"selected":""}>Pending</option>
                <option value="preparing" ${order.status=="preparing"?"selected":""}>Preparing</option>
                <option value="completed" ${order.status=="completed"?"selected":""}>Completed</option>
              </select>
              <button type="submit" class="update-btn">Update</button>
              <span class="status-msg"></span>
            </form>
          </td>
        `;
        
        tbody.appendChild(tr);
      });
      bindStatusForms(); 
    });
}

setInterval(refreshOrders, 5000);

</script>

</body>
</html>