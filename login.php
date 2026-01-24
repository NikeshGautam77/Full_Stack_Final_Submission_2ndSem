<?php
session_start();
require_once "includes/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

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
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"]     = "customer";
                header("Location: index.php");
            }
            exit;
        }
    }

    $_SESSION["flash_error"] = "Invalid username or password.";
    header("Location: login.html");
    exit;
}

header("Location: login.html");
exit;
?>