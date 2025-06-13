<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $userId = $_SESSION['user_id'] ?? null;

    if (!$appointmentId || !$message || !$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required data']);
        exit;
    }
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointmentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $stmt->close();

    if (!$appointment) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid appointment or permission denied']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (appointment_id, sender_role, message) VALUES (?, 'user', ?)");
    $stmt->bind_param("is", $appointmentId, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        error_log("DB Error: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }

    $stmt->close();
    $conn->close();
}
?>
