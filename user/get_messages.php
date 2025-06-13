<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die(json_encode(['error' => 'DB connection failed']));
}

$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['user_id'];

$employee_id = intval($_GET['employee_id']);

// Fetch messages
$query = "
    SELECT m.message, m.sender_role, m.sent_at
    FROM messages m
    JOIN appointments a ON m.appointment_id = a.appointment_id
    WHERE a.user_id = ? AND a.employee_id = ?
    ORDER BY m.sent_at ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$updateQuery = "
    UPDATE messages
    SET is_read = 1
    WHERE sender_role = 'employee'
      AND appointment_id IN (
          SELECT appointment_id FROM appointments
          WHERE user_id = ? AND employee_id = ?
      )
      AND is_read = 0
";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ii", $user_id, $employee_id);
$updateStmt->execute();

header('Content-Type: application/json');
echo json_encode($messages);
?>
