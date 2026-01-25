<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
        $_SESSION["flash_error"] = "Security token invalid. Please try again.";
        header("Location: login_secure.php");
        exit;
    }

    // Verify CAPTCHA
    if (!isset($_POST["captcha_answer"]) || !verifyCaptcha($_POST["captcha_answer"])) {
        $_SESSION["flash_error"] = "Incorrect answer to security question.";
        header("Location: login_secure.php");
        exit;
    }

    // Sanitize input
    $username = sanitizeInput($_POST["username"]);
    $password = trim($_POST["password"]);

    // Validate username format
    if (!validateUsername($username)) {
        $_SESSION["flash_error"] = "Invalid username format.";
        header("Location: login_secure.php");
        exit;
    }

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password_hash"])) {
            
            // ✅ Separate sessions for admin vs customer
            if ($user["role"] === "admin") {
                $_SESSION["admin_id"] = $user["id"];
                $_SESSION["role"]     = "admin";
                header("Location: admin_dashboard.php");
            } else {
                $_SESSION["user_id"]  = $user["id"];
                $_SESSION["username"] = htmlspecialchars($user["username"]);
                $_SESSION["role"]     = "customer";
                header("Location: index.php");
            }
            exit;
        }
    }

    $_SESSION["flash_error"] = "Invalid username or password.";
    header("Location: login_secure.php");
    exit;
}

header("Location: login_secure.php");
exit;
?>