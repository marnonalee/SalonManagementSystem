<?php
session_start();
require_once '../db.php';

if (!isset($_POST['submit'])) {
    header("Location: ../employees.php");
    exit();
}

$id = $_POST['employee_id'];
$name = trim($_POST['name']);
$specialization = trim($_POST['specialization']);
$email = trim($_POST['email']);
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$stmt = $conn->prepare("SELECT name, specialization, email, start_time, end_time FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    header("Location: ../employees.php?status=notfound");
    exit();
}

$stmt->bind_result($curr_name, $curr_spec, $curr_email, $curr_start, $curr_end);
$stmt->fetch();
$stmt->close();

if (
    $name === $curr_name &&
    $specialization === $curr_spec &&
    $email === $curr_email &&
    $start_time === $curr_start &&
    $end_time === $curr_end
) {
    header("Location: ../employees.php");
    exit();
}

$stmt = $conn->prepare("UPDATE employees SET name = ?, specialization = ?, email = ?, start_time = ?, end_time = ? WHERE employee_id = ?");
$stmt->bind_param("sssssi", $name, $specialization, $email, $start_time, $end_time, $id);

if ($stmt->execute()) {
    header("Location: ../employees.php?status=success");
    exit();
} else {
    header("Location: ../employees.php?status=error");
    exit();
}
