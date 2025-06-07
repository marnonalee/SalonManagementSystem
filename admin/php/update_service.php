<?php
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
$service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : null;
$price = isset($_POST['price']) ? floatval($_POST['price']) : null;
$price_max = isset($_POST['price_max']) ? floatval($_POST['price_max']) : null;
$hours = isset($_POST['hours']) ? intval($_POST['hours']) : 0;
$minutes = isset($_POST['minutes']) ? intval($_POST['minutes']) : 0;
$specialization_required = isset($_POST['specialization_required']) ? trim($_POST['specialization_required']) : null;

if (!$service_id || !$service_name || $price === null || $price_max === null || $specialization_required === null) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$total_duration = ($hours * 60) + $minutes;

$sql = "UPDATE services SET 
            service_name = ?, 
            price = ?, 
            price_max = ?, 
            duration = ?, 
            specialization_required = ? 
        WHERE service_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    exit;
}

$stmt->bind_param('sddisi', $service_name, $price, $price_max, $total_duration, $specialization_required, $service_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update service']);
}

$stmt->close();
$conn->close();
?>
