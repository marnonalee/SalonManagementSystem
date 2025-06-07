<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employee = $_GET['employee'] ?? '';
$date = $_GET['date'] ?? '';

if (!$employee || !$date) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT start_time, duration FROM appointments WHERE employee_name = ? AND date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $employee, $date);
$stmt->execute();
$result = $stmt->get_result();

$bookedTimes = [];
while ($row = $result->fetch_assoc()) {
    $bookedTimes[] = [
        'start_time' => $row['start_time'],
        'duration' => (int)$row['duration']
    ];
}

echo json_encode($bookedTimes);
