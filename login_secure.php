<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

$captcha_question = generateCaptcha();
$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafe System Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .captcha-question {
      background-color: #f0f0f0;
      padding: 10px;
      border-radius: 5px;
      margin: 5px 0;
      font-size: 18px;
    }
    .flash.error {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body>

  <!-- Login Container -->
  <div class="login-container">
    <h2>Login to Cafe Ordering System</h2>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION["flash_error"])): ?>
      <div class="flash error"><?= htmlspecialchars($_SESSION["flash_error"]); ?></div>
      <?php unset($_SESSION["flash_error"]); ?>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="login.php" method="POST" class="login-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
      
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required placeholder="Enter your username" maxlength="20">
      </div>

      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required placeholder="Enter your password" maxlength="255">
      </div>

      <!-- CAPTCHA -->
      <div class="form-group">
        <label for="captcha">Security Question:</label>
        <p class="captcha-question"><strong><?= htmlspecialchars($captcha_question); ?></strong></p>
        <input type="text" id="captcha" name="captcha_answer" required placeholder="Enter your answer" maxlength="10">
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <!-- Optional Register Link -->
    <p class="register-link">
      Don't have an account? <a href="register_secure.php">Register here</a>
    </p>
  </div>

</body>
</html>
