<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    $sql = "UPDATE appointments SET appointment_status = 'Paid' WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment accepted.";
    } else {
        $_SESSION['error'] = "Failed to accept payment.";
    }

    $stmt->close();
    $conn->close();
}

header("Location: appointments.php");
exit();
