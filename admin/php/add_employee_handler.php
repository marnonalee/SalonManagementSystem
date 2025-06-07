<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO employees (name, specialization, email, password, status, start_time, end_time) VALUES (?, ?, ?, ?, 'active', ?, ?)");
    $stmt->bind_param("ssssss", $name, $specialization, $email, $hashed_password, $start_time, $end_time);

    if ($stmt->execute()) {
        header("Location: ../employees.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../employees.php");
    exit();
}
?>
