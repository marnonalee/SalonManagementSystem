<?php
include '../../db.php'; 

if (isset($_POST['update_status']) && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    $query = "UPDATE payments SET payment_status = 'Paid' WHERE appointment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        header("Location: ../payments.php?status=confirmed");
        exit();
    } else {
        echo "Error updating payment status: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
