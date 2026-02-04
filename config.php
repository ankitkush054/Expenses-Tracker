<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "expense_tracker";

// Create connection
$con = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
// Connection is now ready for queries
?>
