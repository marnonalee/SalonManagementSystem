<?php
include '../db.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$appointment_id || !$employee_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$sql_check = "SELECT * FROM appointments WHERE appointment_id = $appointment_id AND user_id = $user_id AND employee_id = $employee_id AND is_deleted = 0";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment or employee']);
    exit;
}

$sender_id = $user_id; 
$receiver_id = $employee_id;
$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO messages (appointment_id, sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiss", $appointment_id, $sender_id, $receiver_id, $message, $now);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
$conn->close();
?>
