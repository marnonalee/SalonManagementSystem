<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = intval($_POST['appointment_id']);
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);
    $user = $_SESSION['user'];

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if (!$userData) {
        header("Location: ../landing.php?rating=error");
        exit();
    }

    $userId = $userData['user_id'];

    $stmt = $conn->prepare("SELECT service_id, employee_id FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointmentData = $result->fetch_assoc();

    if (!$appointmentData) {
        header("Location: ../landing.php?rating=error");
        exit();
    }

    $serviceId = $appointmentData['service_id'];
    $employeeId = $appointmentData['employee_id'];

    $insert = $conn->prepare("INSERT INTO ratings (appointment_id, user_id, service_id, employee_id, rating, review, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $insert->bind_param("iiiiss", $appointment_id, $userId, $serviceId, $employeeId, $rating, $review);

    if ($insert->execute()) {
        header("Location: ../landing.php?rating=success");
    } else {
        header("Location: ../landing.php?rating=error");
    }

} else {
    header("Location: ../landing.php");
}

$conn->close();
?>
