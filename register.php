<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
        $_SESSION["flash_error"] = "Security token invalid. Please try again.";
        header("Location: register_secure.php");
        exit;
    }

    // Verify CAPTCHA
    if (!isset($_POST["captcha_answer"]) || !verifyCaptcha($_POST["captcha_answer"])) {
        $_SESSION["flash_error"] = "Incorrect answer to security question.";
        header("Location: register_secure.php");
        exit;
    }

    // Sanitize and validate input
    $username = sanitizeInput($_POST["username"]);
    $password = trim($_POST["password"]);

    // Validate username format (3-20 chars, alphanumeric and underscore)
    if (!validateUsername($username)) {
        $_SESSION["flash_error"] = "Username must be 3-20 characters (letters, numbers, underscore only).";
        header("Location: register_secure.php");
        exit;
    }

    // Validate password strength
    if (!validatePassword($password)) {
        $_SESSION["flash_error"] = "Password must be at least 6 characters.";
        header("Location: register_secure.php");
        exit;
    }

    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION["flash_error"] = "Username already exists.";
        header("Location: register_secure.php");
        exit;
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Always register as customer
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'customer')");
    $stmt->bind_param("ss", $username, $hash);

    if ($stmt->execute()) {
        $_SESSION["flash_success"] = "Registration successful. Please log in.";
        header("Location: login_secure.php");
    } else {
        $_SESSION["flash_error"] = "Registration failed. Please try again.";
        header("Location: register_secure.php");
    }
    exit;
}
header("Location: register_secure.php");
exit;
?>