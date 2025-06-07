<?php
include '../db.php';

$cancelReason = "Automatically cancelled due to non-payment within 24 hours";

$sql = "UPDATE appointments a
        JOIN payments p ON a.appointment_id = p.appointment_id
        SET a.appointment_status = 'Cancelled',
            a.cancel_reason = ?
        WHERE p.payment_status = 'Unpaid'
          AND a.created_at <= NOW() - INTERVAL 24 HOUR";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cancelReason);

if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    echo json_encode(['success' => true, 'message' => "$affectedRows unpaid appointments cancelled."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
