<?php
header('Content-Type: application/json');
include '../db.php';

$paymentMethodId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($paymentMethodId > 0) {
    $stmt = $conn->prepare("SELECT method_name, details, contact_number, qr_code FROM payment_methods WHERE payment_method_id = ?");
    $stmt->bind_param("i", $paymentMethodId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($method = $result->fetch_assoc()) {
        echo json_encode($method);
    } else {
        echo json_encode(["error" => "Payment method not found."]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid payment method ID."]);
}
?>
