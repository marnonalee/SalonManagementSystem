<?php
include '../db.php';
session_start();

header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['appointment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID is required.']);
    exit;
}

$appointment_id = intval($input['appointment_id']);

$sqlCheck = "SELECT * FROM appointments WHERE appointment_id = ? AND user_id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $appointment_id, $user_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or you do not have permission.']);
    exit;
}
$sqlArchive = "UPDATE appointments SET is_deleted = 1 WHERE appointment_id = ?";
$stmtArchive = $conn->prepare($sqlArchive);
$stmtArchive->bind_param("i", $appointment_id);

if ($stmtArchive->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to archive appointment.']);
}
exit;
