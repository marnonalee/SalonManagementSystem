<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$appointment_id = $_POST['appointment_id'] ?? null;
$payment_method_id = $_POST['payment_method_id'] ?? null;
$payment_type = $_POST['payment_type'] ?? 'Pay Now';

if (!$appointment_id || !$payment_method_id || !isset($_FILES['payment_proof'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data or file']);
    exit;
}

$file = $_FILES['payment_proof'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.']);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'payment_' . $appointment_id . '_' . time() . '.' . $ext;
$uploadDir = '../uploads/payment_proofs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$destination = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
    exit;
}

$stmt = $conn->prepare("UPDATE payments SET payment_method_id = ?, payment_proof = ?, payment_status = 'Pending Verification', payment_type = ? WHERE appointment_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$proof_path = 'payment_proofs/' . $filename;
$stmt->bind_param("issi", $payment_method_id, $proof_path, $payment_type, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Payment proof submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update payment proof: ' . $stmt->error]);
}
