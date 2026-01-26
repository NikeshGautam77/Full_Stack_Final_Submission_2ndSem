<?php
// my_orders.php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

// ✅ Only logged-in customers can view their orders
if (empty($_SESSION["user_id"])) {
    header("Location: login_secure.php");
    exit;
}

$user_id   = (int)$_SESSION["user_id"];
$username  = htmlspecialchars($_SESSION["username"]); // for display

// ✅ Fetch only orders belonging to the logged-in user
$sql = "SELECT id, items, total_price, status, created_at 
        FROM orders 
        WHERE customer_id = ? 
        ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
  <header class="site-header">
    <h1>Orders for <?= $username ?></h1>
    <nav>
      <a href="index.php">Back to Menu</a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
  </header>

  <?php if (!empty($_SESSION["flash_success"])): ?>
    <div class="flash success"><?= htmlspecialchars($_SESSION["flash_success"]); ?></div>
    <?php unset($_SESSION["flash_success"]); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION["flash_error"])): ?>
    <div class="flash error"><?= htmlspecialchars($_SESSION["flash_error"]); ?></div>
    <?php unset($_SESSION["flash_error"]); ?>
  <?php endif; ?>

  <table class="orders-table">
    <thead>
      <tr>
        <th>Order #</th>
        <th>Items</th>
        <th>Total</th>
        <th>Status</th>
        <th>Placed</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row["id"]; ?></td>
          <td>
            <?php
              $items = json_decode($row["items"], true);
              if (is_array($items)) {
                foreach ($items as $it) {
                  echo htmlspecialchars($it["name"]) . " x " . (int)$it["qty"] . "<br>";
                }
              }
            ?>
          </td>
          <td>Rs. <?= number_format((float)$row["total_price"], 2); ?></td>
          <td><?= htmlspecialchars($row["status"]); ?></td>
          <td><?= htmlspecialchars($row["created_at"]); ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>