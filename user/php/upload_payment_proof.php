<?php
require '../db.php'; 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}

$appointmentId = $_POST['appointment_id'] ?? null;
$paymentMethodName = $_POST['payment_method_name'] ?? null;

if (!$appointmentId || !$paymentMethodName) {
    echo json_encode(['message' => 'Missing required fields.']);
    exit;
}

if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['message' => 'File upload failed.']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$fileType = mime_content_type($_FILES['payment_screenshot']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif.']);
    exit;
}

$uploadsDir = '../uploads/payment_proofs/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$filename = uniqid('proof_') . "_" . basename($_FILES['payment_screenshot']['name']);
$filepath = $uploadsDir . $filename;

if (!move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $filepath)) {
    echo json_encode(['message' => 'Failed to save uploaded file.']);
    exit;
}

$stmt = $conn->prepare("SELECT payment_method_id FROM payment_methods WHERE method_name = ?");
$stmt->bind_param("s", $paymentMethodName);
$stmt->execute();
$stmt->bind_result($methodId);
$stmt->fetch();
$stmt->close();

if (!$methodId) {
    echo json_encode(['message' => 'Payment method not found.']);
    exit;
}

$stmt = $conn->prepare("SELECT payment_id FROM payments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $stmt = $conn->prepare("UPDATE payments SET payment_method_id = ?, payment_proof = ?, payment_status = 'Pending verification', created_at = NOW() WHERE appointment_id = ?");
    $stmt->bind_param("isi", $methodId, $filename, $appointmentId);
    $success = $stmt->execute();
} else {
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO payments (appointment_id, payment_method_id, payment_proof, payment_status, created_at) VALUES (?, ?, ?, 'Pending verification', NOW())");
    $stmt->bind_param("iis", $appointmentId, $methodId, $filename);
    $success = $stmt->execute();
}

if ($success) {
    echo json_encode(['message' => 'Payment proof submitted successfully. Awaiting verification.']);
} else {
    echo json_encode(['message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
