<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['employee'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['user_id']) || !isset($input['message'])) {
    echo json_encode(['error' => 'Missing data']);
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database error']);
    exit();
}

$employee_name = $_SESSION['employee'];
$stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$res = $stmt->get_result();
$emp = $res->fetch_assoc();
$employee_id = $emp['employee_id'];

$user_id = intval($input['user_id']);
$message = trim($input['message']);

$stmt = $conn->prepare("
    SELECT appointment_id FROM appointments 
    WHERE user_id = ? AND employee_id = ?
    ORDER BY appointment_id DESC LIMIT 1
");
$stmt->bind_param("ii", $user_id, $employee_id);
$stmt->execute();
$res = $stmt->get_result();
$appt = $res->fetch_assoc();

if (!$appt) {
    echo json_encode(['error' => 'No appointment found']);
    exit();
}

$appointment_id = $appt['appointment_id'];

$stmt = $conn->prepare("INSERT INTO messages (appointment_id, sender_role, message, sent_at) VALUES (?, 'employee', ?, NOW())");
$stmt->bind_param("is", $appointment_id, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}
