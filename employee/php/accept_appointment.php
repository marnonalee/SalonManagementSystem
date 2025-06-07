<?php
include '../db.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $appointmentId = intval($_POST['appointment_id']);

    $stmt = $conn->prepare("UPDATE appointments SET appointment_status = 'Accepted' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointmentId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
