<?php
include '../db.php'; 

$employee = $_GET['employee'];
$date = $_GET['date'];

$sql = "SELECT start_time, end_time FROM appointments 
        WHERE appointment_date = ? AND employee_id = (
            SELECT id FROM employees WHERE name = ?
        ) AND appointment_status != 'cancelled'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $date, $employee);
$stmt->execute();
$result = $stmt->get_result();

$booked = [];
while ($row = $result->fetch_assoc()) {
    $booked[] = [
        "start" => $row['start_time'],
        "end" => $row['end_time']
    ];
}

echo json_encode($booked);
?>