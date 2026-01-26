<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: login_secure.php");
exit;
?>