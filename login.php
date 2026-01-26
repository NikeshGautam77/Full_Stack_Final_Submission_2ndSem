<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/captcha.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_secure.php");
    exit;
}

/* =========================
   CSRF CHECK
========================= */
if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {
    $_SESSION["flash_error"] = "Security token invalid.";
    header("Location: login_secure.php");
    exit;
}

/* =========================
   CAPTCHA CHECK
========================= */
if (!isset($_POST["captcha_answer"]) || !verifyCaptcha($_POST["captcha_answer"])) {
    $_SESSION["flash_error"] = "Incorrect CAPTCHA answer.";
    header("Location: login_secure.php");
    exit;
}

/* =========================
   INPUT VALIDATION
========================= */
$username = sanitizeInput($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if (!validateUsername($username) || empty($password)) {
    $_SESSION["flash_error"] = "Invalid login details.";
    header("Location: login_secure.php");
    exit;
}

/* =========================
   FETCH USER
========================= */
$sql = "SELECT id, username, password_hash, role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION["flash_error"] = "Invalid username or password.";
    header("Location: login_secure.php");
    exit;
}

$user = $result->fetch_assoc();

/* =========================
   PASSWORD VERIFY
========================= */
if (!password_verify($password, $user["password_hash"])) {
    $_SESSION["flash_error"] = "Invalid username or password.";
    header("Location: login_secure.php");
    exit;
}

/* =========================
   LOGIN SUCCESS
========================= */
session_regenerate_id(true); // prevent session fixation

$_SESSION["user_id"]  = $user["id"];     // ✅ ALWAYS SET
$_SESSION["username"] = $user["username"];
$_SESSION["role"]     = $user["role"];

unset($_SESSION["csrf_token"]); // rotate CSRF after login

if ($user["role"] === "admin") {
    $_SESSION["admin_id"] = $user["id"];
    header("Location: admin_dashboard.php");
} else {
    header("Location: index.php");
}
exit;
