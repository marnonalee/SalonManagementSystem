<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['employee_id']) || !isset($input['message'])) {
    echo json_encode(['error' => 'Missing data']);
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['user_id'];

$employee_id = intval($input['employee_id']);
$message = trim($input['message']);

$stmt = $conn->prepare("
    SELECT appointment_id FROM appointments 
    WHERE user_id = ? AND employee_id = ? 
    ORDER BY appointment_id DESC LIMIT 1
");
$stmt->bind_param("ii", $user_id, $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['error' => 'No appointment found']);
    exit();
}

$appointment_id = $row['appointment_id'];

$stmt = $conn->prepare("INSERT INTO messages (appointment_id, sender_role, message, sent_at) VALUES (?, 'user', ?, NOW())");
$stmt->bind_param("is", $appointment_id, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}
?>
