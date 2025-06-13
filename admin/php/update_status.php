<?php
require_once '../../db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action === 'confirm') {
        $update = "UPDATE payments SET payment_status = 'Paid' WHERE appointment_id = ?";
    } elseif ($action === 'reject') {
        $update = "UPDATE payments SET payment_status = 'Rejected' WHERE appointment_id = ?";
        
        // Optional: cancel appointment too
        $cancel_appointment = "UPDATE appointments SET appointment_status = 'Cancelled' WHERE appointment_id = ?";
        $stmt2 = $conn->prepare($cancel_appointment);
        $stmt2->bind_param("i", $appointment_id);
        $stmt2->execute();
    }

    $stmt = $conn->prepare($update);
    $stmt->bind_param("i", $appointment_id);
    if ($stmt->execute()) {
        header("Location: ../payments.php?status=success");
        exit();
    } else {
        echo "Error updating payment.";
    }
}
?>
