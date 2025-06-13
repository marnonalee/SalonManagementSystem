<?php 
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost:4306';
$db = 'salon';         
$user = 'root';          
$pass = '';              

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$raw = file_get_contents("php://input");
$input = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg(),
        'raw_input' => $raw
    ]);
    exit;
}

if (!isset($input['appointment_id'], $input['new_date'], $input['new_time'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields.',
        'input' => $input
    ]);
    exit;
}

$appointment_id = $input['appointment_id'];
$new_date = $input['new_date'];
$new_time = $input['new_time'];  

$stmt = $conn->prepare("
    UPDATE appointments 
    SET appointment_date = ?, start_time = ?, end_time = ?
    WHERE appointment_id = ?
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssi", $new_date, $new_time, $new_time, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update appointment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
