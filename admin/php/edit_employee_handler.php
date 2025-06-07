<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['employee_id'];
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $conn->prepare("UPDATE employees SET name = ?, specialization = ?, email = ?, start_time = ?, end_time = ? WHERE employee_id = ?");
    $stmt->bind_param("sssssi", $name, $specialization, $email, $start_time, $end_time, $id);

    if ($stmt->execute()) {
        header("Location: ../employees.php?status=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
