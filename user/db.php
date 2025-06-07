<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "salon";

$conn = new mysqli('localhost:4306', 'root', '', 'salon');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
