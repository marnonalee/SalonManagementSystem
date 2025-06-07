<?php
session_start();
require_once '../../db.php'; 

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'], $_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $employee_id = $_SESSION['employee_id'];

    $checkSql = "SELECT appointment_id FROM appointments WHERE appointment_id = ? AND employee_id = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ii", $appointment_id, $employee_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 1) {
        $updateSql = "UPDATE appointments SET appointment_status = 'Cancelled' WHERE appointment_id = ?";
        $stmtUpdate = $conn->prepare($updateSql);
        $stmtUpdate->bind_param("i", $appointment_id);
        if ($stmtUpdate->execute()) {
            $_SESSION['message'] = "Appointment cancelled successfully.";
        } else {
            $_SESSION['error'] = "Failed to cancel appointment.";
        }
    } else {
        $_SESSION['error'] = "Appointment not found or unauthorized.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: ../my_appointments.php");
exit();
