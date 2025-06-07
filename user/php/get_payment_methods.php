<?php
header('Content-Type: application/json');
include '../db.php'; 
$sql = "SELECT payment_method_id,  method_name, qr_code FROM payment_methods WHERE is_active = 1 ORDER BY method_name ASC";
$result = $conn->query($sql);

$methods = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $methods[] = $row;
    }
}

echo json_encode($methods);
?>
