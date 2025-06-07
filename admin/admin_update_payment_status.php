<?php
// admin_update_payment_status.php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$booking_id || !in_array($status, ['Verified', 'Rejected'])) {
        die("Invalid request.");
    }

    $conn = openDBConnection();

    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: admin_verify_payments.php?message=Status updated.");
        exit();
    } else {
        die("Database update failed: " . $conn->error);
    }
} else {
    die("Invalid request.");
}
