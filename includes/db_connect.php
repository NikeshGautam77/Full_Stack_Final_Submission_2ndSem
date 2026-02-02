<?php
// Database connection settings
$servername = "localhost";   
$username   = "np02cs4a240039";       
$password   = "PF2U2fO7in";            
$dbname     = "np02cs4a240039"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>