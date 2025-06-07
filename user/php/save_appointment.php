<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'] ?? null;
$service_name = trim($data['service'] ?? '');
$employee_name = trim($data['agent'] ?? '');
$appointment_date = trim($data['date'] ?? '');
$start_time = trim($data['time'] ?? '');

if (!$user_id || !$service_name || !$employee_name || !$appointment_date || !$start_time) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$stmt = $conn->prepare("SELECT service_id, price, appointment_fee, duration FROM services WHERE service_name = ?");
$stmt->bind_param("s", $service_name);
$stmt->execute();
$service_result = $stmt->get_result();

if ($service_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid service selected.']);
    exit;
}
$service = $service_result->fetch_assoc();

$stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name);
$stmt->execute();
$employee_result = $stmt->get_result();

if ($employee_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee selected.']);
    exit;
}
$employee = $employee_result->fetch_assoc();

$checkStmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE user_id = ? AND employee_id = ? AND service_id = ? AND appointment_date = ? AND start_time = ?");
$checkStmt->bind_param("iiiss", $user_id, $employee['employee_id'], $service['service_id'], $appointment_date, $start_time);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
if ($checkResult->num_rows > 0) {
    $existingAppointment = $checkResult->fetch_assoc();

    if (!empty($data['pay_now'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Already booked. Proceeding to payment.',
            'appointment_id' => $existingAppointment['appointment_id']
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'You already have an appointment booked for this time slot.']);
        exit;
    }
}


$startDateTime = new DateTime($appointment_date . ' ' . $start_time);
$endDateTime = clone $startDateTime;
$endDateTime->modify('+' . $service['duration'] . ' minutes');
$end_time = $endDateTime->format('H:i:s');

$status = 'Pending';

$stmt = $conn->prepare("INSERT INTO appointments (user_id, employee_id, service_id, appointment_date, start_time, end_time, price, appointment_fee, appointment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
$stmt->bind_param("iiisssdss", $user_id, $employee['employee_id'], $service['service_id'], $appointment_date, $start_time, $end_time, $service['price'], $service['appointment_fee'], $status);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to save appointment: ' . $stmt->error]);
    exit;
}

$appointment_id = $stmt->insert_id;

$payment_method_id = NULL;
$payment_type = 'Pay Later';
$payment_proof = NULL;
$payment_status = 'Unpaid';

$stmt_payment = $conn->prepare("INSERT INTO payments (appointment_id, payment_method_id, payment_type, payment_proof, payment_status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
if ($stmt_payment) {
    $stmt_payment->bind_param("iisss", $appointment_id, $payment_method_id, $payment_type, $payment_proof, $payment_status);
    $stmt_payment->execute();
} else {
    error_log("Payment insert prepare failed: " . $conn->error);
}

echo json_encode([
    'success' => true,
    'message' => 'Appointment booked successfully.',
    'appointment_id' => $appointment_id
]);
