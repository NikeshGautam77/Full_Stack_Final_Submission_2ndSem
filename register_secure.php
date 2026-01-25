<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

$captcha_question = generateCaptcha();
$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, rgb(134,40,153), #ACB6E5);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }
    .register-container {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.25);
      width: 350px;
      text-align: center;
    }
    .register-container h2 {
      margin-bottom: 20px;
      color: rgb(134,40,153);
    }
    .register-container input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
    }
    .register-container button {
      width: 100%;
      padding: 12px;
      background: rgb(134,40,153);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-top: 10px;
    }
    .register-container button:hover {
      background: #6e1f9d;
    }
    .register-container p {
      margin-top: 15px;
      font-size: 14px;
    }
    .register-container a {
      color: rgb(134,40,153);
      text-decoration: none;
    }
    .register-container a:hover {
      text-decoration: underline;
    }
    .captcha-question {
      background-color: #f0f0f0;
      padding: 10px;
      border-radius: 5px;
      margin: 5px 0;
      font-size: 16px;
    }
    .flash.error {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
      text-align: left;
    }
    .help-text {
      font-size: 12px;
      color: #666;
      text-align: left;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Create Account</h2>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION["flash_error"])): ?>
      <div class="flash error"><?= htmlspecialchars($_SESSION["flash_error"]); ?></div>
      <?php unset($_SESSION["flash_error"]); ?>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
      
      <div>
        <input type="text" name="username" placeholder="Username (3-20 chars, alphanumeric/underscore)" required maxlength="20" pattern="[a-zA-Z0-9_]{3,20}">
        <div class="help-text">Only letters, numbers, and underscore allowed</div>
      </div>

      <div>
        <input type="password" name="password" placeholder="Password (minimum 6 characters)" required maxlength="255" minlength="6">
        <div class="help-text">Must be at least 6 characters</div>
      </div>

      <!-- CAPTCHA -->
      <div>
        <label><strong>Security Question:</strong></label>
        <p class="captcha-question"><strong><?= htmlspecialchars($captcha_question); ?></strong></p>
        <input type="text" name="captcha_answer" placeholder="Enter your answer" required maxlength="10">
      </div>

      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login_secure.php">Login here</a></p>
  </div>
</body>
</html>
