<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['employee_id'];

    $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../employees.php?deleted=success");
        exit();
    } else {
        echo "Error deleting employee: " . $stmt->error;
    }
}
?>
