<?php
session_start();
require_once '../db.php';

$user_id = $_POST['user_id'];
$username = $_POST['username'];
$phone = $_POST['phone'];
$email = $_POST['email'];

$sql = "UPDATE users SET username=?, phone=?, email=? WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $username, $phone, $email, $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "User updated successfully!";
} else {
    $_SESSION['error'] = "Update failed!";
}

$stmt->close();
$conn->close();

header("Location: ../user_management.php"); 
exit();

?>
