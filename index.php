<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login_secure.php");
  exit;
}

$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <script src="script.js"></script>
  <title>Cafe Menu</title>
</head>
<body>
<?php if (!empty($_SESSION["flash_success"])): ?>
  <div class="flash success"><?= $_SESSION["flash_success"]; ?></div>
  <?php unset($_SESSION["flash_success"]); ?>
<?php endif; ?>

<?php if (!empty($_SESSION["flash_error"])): ?>
  <div class="flash error"><?= $_SESSION["flash_error"]; ?></div>
  <?php unset($_SESSION["flash_error"]); ?>
<?php endif; ?>

<!-- Header Section -->
<div class="header">
  <img src="logo.png" alt="Cafe Logo" class="logo">
  <h1>Next-Gen Menu Ordering System 🍕</h1>
  <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Sidebar -->
<div class="sidebar">
  <h2 class="page_header">Categories</h2>

  <!-- Search Bar -->
  <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search menu..." 
           onkeypress="if(event.key==='Enter') searchMenu()" />
    <button onclick="searchMenu()">Search</button>
  </div>

  <!-- Filter Dropdown -->
  <div class="filter-container">
    <label for="filter">Sort by:</label>
    <select id="filter" onchange="filterMenu()">
      <option value="default">Default</option>
      <option value="price-asc">Price: Low to High</option>
      <option value="price-desc">Price: High to Low</option>
    </select>
  </div>

  <!-- Category Tabs -->
  <div class="tab" id="veg-tab" onclick="showVegItems()">Veg Items</div>  
  <div class="tab" id="nonveg-tab" onclick="showNonVegItems()">Non-Veg Items</div>
  <div class="tab" id="drinks-tab" onclick="showDrinks()">Drinks Items</div>
  <div class="tab" id="dessert-tab" onclick="showDessert()">Desserts</div>

  <!-- Cart Button -->
  <button class="cart-icon-btn" onclick="openCartModal()">
    🛒 Cart <span id="cart-count" class="cart-count">0</span>
  </button>
</div>

<!-- Main Content Area -->
<div class="main-content">

  <!-- Veg Menu -->
  <div id="vegcontents" class="tab-content">
    <h2 class="section-title">Veg Menu</h2>
    <div class="menu-grid">
      <?php
      $veg_items = $conn->query("SELECT * FROM menu_items WHERE category='veg' AND available=1");
      while($item = $veg_items->fetch_assoc()):
      ?>
        <div class="menu-card">
          <h4><?= htmlspecialchars($item['name']) ?></h4>
          <p>Rs. <?= $item['price'] ?></p>
          <button onclick="addToCart('<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">Add to Cart</button>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Non-Veg Menu -->
  <div id="nonvegcontents" class="tab-content">
    <h2 class="section-title">Non-Veg Menu</h2>
    <div class="menu-grid">
      <?php
      $nonveg_items = $conn->query("SELECT * FROM menu_items WHERE category='nonveg' AND available=1");
      while($item = $nonveg_items->fetch_assoc()):
      ?>
        <div class="menu-card">
          <h4><?= htmlspecialchars($item['name']) ?></h4>
          <p>Rs. <?= $item['price'] ?></p>
          <button onclick="addToCart('<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">Add to Cart</button>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Drinks Menu -->
  <div id="drinkcontents" class="tab-content">
    <h2 class="section-title">Drinks</h2>
    <div class="menu-grid">
      <?php
      $drink_items = $conn->query("SELECT * FROM menu_items WHERE category='drinks' AND available=1");
      while($item = $drink_items->fetch_assoc()):
      ?>
        <div class="menu-card">
          <h4><?= htmlspecialchars($item['name']) ?></h4>
          <p>Rs. <?= $item['price'] ?></p>
          <button onclick="addToCart('<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">Add to Cart</button>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Desserts Menu -->
  <div id="dessertcontents" class="tab-content">
    <h2 class="section-title">Desserts</h2>
    <div class="menu-grid">
      <?php
      $dessert_items = $conn->query("SELECT * FROM menu_items WHERE category='desserts' AND available=1");
      while($item = $dessert_items->fetch_assoc()):
      ?>
        <div class="menu-card">
          <h4><?= htmlspecialchars($item['name']) ?></h4>
          <p>Rs. <?= $item['price'] ?></p>
          <button onclick="addToCart('<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">Add to Cart</button>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

</div>

<!-- Cart Modal -->
<div id="cart-modal" class="cart-modal">
  <div class="cart-modal-content">
    <div class="cart-modal-header">
      <h2>Your Cart</h2>
      <button class="close-btn" onclick="closeCartModal()">✕</button>
    </div>
    <ul id="cart-items" class="cart-modal-items"></ul>
    <p id="cart-total" class="cart-modal-total">Total: Rs. 0</p>
    <div class="cart-modal-buttons">
      <button onclick="resetCart()" id="reset-btn" class="reset-btn">🗑️ Reset Cart</button>
      <button onclick="finalOrder()" id="final-order-btn" class="final-order-btn">Confirm Order</button>
    </div>
  </div>
</div>

<!-- Order Summary Modal -->
<div id="order-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:2000;align-items:center;justify-content:center;">
  <div style="background:#fff;padding:30px 20px 20px 20px;border-radius:10px;max-width:400px;width:90%;margin:auto;position:relative;top:10vh;">
    <h2 style="margin-bottom:15px;">Order Summary</h2>
    <ul id="order-summary-list" style="list-style:none;padding:0;margin-bottom:15px;"></ul>
    <p id="order-summary-total" style="font-weight:bold;"></p>
    
    <form id="checkout-form" action="checkout.php" method="POST" style="margin-top:15px;" onsubmit="return submitOrder(event)">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
      <input type="hidden" id="cart-json" name="cart_json" value="">
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;background:#28a745;color:#fff;padding:8px 16px;border:none;border-radius:5px;cursor:pointer;font-weight:bold;">Place Order</button>
        <button type="button" onclick="closeOrderModal()" style="flex:1;background:#6c757d;color:#fff;padding:8px 16px;border:none;border-radius:5px;cursor:pointer;">Cancel</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>