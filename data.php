<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "salon";

$conn = new mysqli('localhost:4306', 'root', '', 'salon');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = "admin";
$email = "crum639@gmail.com";
$plainPassword = "admin1234";
$status = "active";
$created_at = "2025-04-21 18:31:32";

$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, email, password, created_at, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $user, $email, $hashedPassword, $created_at, $status);

if ($stmt->execute()) {
    echo "New admin user inserted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
