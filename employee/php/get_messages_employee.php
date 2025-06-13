<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['employee'])) {
    echo json_encode([]);
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

$employee_name = $_SESSION['employee'];
$stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$res = $stmt->get_result();
$emp = $res->fetch_assoc();
$employee_id = $emp['employee_id'];

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("
    SELECT m.message, m.sent_at, m.sender_role, u.username AS sender_name
    FROM messages m
    JOIN appointments a ON m.appointment_id = a.appointment_id
    JOIN users u ON a.user_id = u.user_id
    WHERE a.employee_id = ? AND a.user_id = ?
    ORDER BY m.sent_at ASC
");
$stmt->bind_param("ii", $employee_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);
