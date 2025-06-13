<?php
include '../db.php';
session_start();

header('Content-Type: application/json'); 

if (!isset($_POST['appointment_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request or not logged in.']);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);
$user_id = intval($_SESSION['user_id']);
$cancelReason = "Cancelled by user";
$sqlCheck = "SELECT appointment_status FROM appointments WHERE appointment_id = ? AND user_id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $appointment_id, $user_id);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or unauthorized.']);
    exit;
}

$stmtCheck->bind_result($currentStatus);
$stmtCheck->fetch();

if (strtolower($currentStatus) === 'cancelled') {
    echo json_encode(['success' => false, 'message' => 'Appointment already cancelled.']);
    exit;
}

$stmtCheck->close();

$sqlUpdate = "UPDATE appointments 
              SET appointment_status = 'Cancelled', cancel_reason = ?
              WHERE appointment_id = ? AND user_id = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("sii", $cancelReason, $appointment_id, $user_id);

if ($stmtUpdate->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment.']);
}

$stmtUpdate->close();
$conn->close();
