<?php
session_start();
if (empty($_SESSION["user_id"])) {
    header("Location: login_secure.php");
    exit;
}
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
    <h1>My Orders</h1>
    <nav>
      <a href="index.php">Back to Menu</a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
  </header>

  <!-- ✅ Ajax will inject orders here -->
  <div id="orders-container"></div>

  <script src="script.js"></script>
  <script>
    // Load orders immediately when page opens
    document.addEventListener("DOMContentLoaded", function() {
      loadMyOrders();
    });
  </script>
</body>
</html>