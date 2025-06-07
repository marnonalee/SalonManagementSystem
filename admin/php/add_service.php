<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $price_max = isset($_POST['price_max']) ? floatval($_POST['price_max']) : 0;

    if ($price_max < $price) {
        die("Error: Max price cannot be less than Min price.");
    }

    $appointment_fee = $price * 0.10;

    $duration_hours = isset($_POST['duration_hours']) ? intval($_POST['duration_hours']) : 0;
    $duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 0;
    $total_minutes = ($duration_hours * 60) + $duration_minutes;

    $specialization = isset($_POST['specialization']) ? trim($_POST['specialization']) : '';

    $stmt = $conn->prepare("INSERT INTO services (service_name, price, price_max, appointment_fee, duration, specialization_required) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sdddis", $service_name, $price, $price_max, $appointment_fee, $total_minutes, $specialization);
        if ($stmt->execute()) {
            header("Location: ../services.php");
            exit();
        } else {
            echo "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Statement prepare failed: " . $conn->error;
    }

    $conn->close();
}
?>
