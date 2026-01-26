<?php
session_start();
require_once "includes/captcha.php";

// Generate CSRF + CAPTCHA safely
$csrf_token = generateCsrfToken();
$captcha_question = generateCaptcha();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cafe System Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, rgb(134,40,153), #ACB6E5);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.login-container {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    width: 350px;
    text-align: center;
}
.login-container h2 {
    margin-bottom: 20px;
    color: rgb(134,40,153);
}
.login-container input {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
}
.login-container button {
    width: 100%;
    padding: 12px;
    background: rgb(134,40,153);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.login-container button:hover {
    background: #6e1f9d;
}
.flash-error {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}
.captcha-question {
    background: #f0f0f0;
    padding: 8px;
    border-radius: 5px;
    margin: 6px 0;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="login-container">
    <h2>Login to Cafe Ordering System</h2>

    <?php if (!empty($_SESSION["flash_error"])): ?>
        <div class="flash-error">
            <?= htmlspecialchars($_SESSION["flash_error"]); ?>
        </div>
        <?php unset($_SESSION["flash_error"]); ?>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($csrf_token); ?>">

        <input type="text"
               name="username"
               placeholder="Username"
               required
               maxlength="20">

        <input type="password"
               name="password"
               placeholder="Password"
               required>

        <label>Security Question:</label>
        <div class="captcha-question">
            <?= htmlspecialchars($captcha_question); ?>
        </div>

        <input type="text"
               name="captcha_answer"
               placeholder="Your Answer"
               required
               maxlength="10">

        <button type="submit">Login</button>
    </form>

    <p style="margin-top:15px;">
        Don't have an account?
        <a href="register_secure.php">Register</a>
    </p>
</div>

</body>
</html>
