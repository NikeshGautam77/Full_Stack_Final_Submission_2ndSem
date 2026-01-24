<?php
session_start();
require_once "includes/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if ($username === "" || $password === "") {
        $_SESSION["flash_error"] = "Username and password required.";
        header("Location: register.html");
        exit;
    }

    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION["flash_error"] = "Username already exists.";
        header("Location: register.html");
        exit;
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Always register as customer
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'customer')");
    $stmt->bind_param("ss", $username, $hash);

    if ($stmt->execute()) {
        $_SESSION["flash_success"] = "Registration successful. Please log in.";
        header("Location: login.html");
    } else {
        $_SESSION["flash_error"] = "Registration failed.";
        header("Location: register.html");
    }
    exit;
}
header("Location: register.html");
exit;
?>