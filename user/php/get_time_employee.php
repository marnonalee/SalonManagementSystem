<?php
header('Content-Type: application/json');
include '../db.php';

if (!isset($_GET['employee']) || !isset($_GET['date']) || !isset($_GET['duration'])) {
    echo json_encode([]);
    exit;
}

$employeeId = $conn->real_escape_string($_GET['employee']);
$date = $_GET['date']; 
$duration = intval($_GET['duration']); 

$sql = "SELECT start_time, end_time FROM employees WHERE employee_id = '$employeeId' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $start = $row['start_time']; 
    $end = $row['end_time'];    

    $slots = [];

    $startTime = strtotime("1970-01-01 $start");
    $endTime = strtotime("1970-01-01 $end");

    $appointmentsQuery = "SELECT start_time, end_time FROM appointments WHERE employee_id = '$employeeId' AND appointment_date = '$date' AND appointment_status != 'cancelled'";
    $appointmentsResult = $conn->query($appointmentsQuery);

    $bookedSlots = [];
    if ($appointmentsResult && $appointmentsResult->num_rows > 0) {
        while ($app = $appointmentsResult->fetch_assoc()) {
            $bookedSlots[] = [
                'start' => strtotime("1970-01-01 " . $app['start_time']),
                'end' => strtotime("1970-01-01 " . $app['end_time']),
            ];
        }
    }

    $breakTime = 10 * 60; // 10 minutes break

    while ($startTime + $duration * 60 <= $endTime) {
        $slotStart = $startTime;
        $slotEnd = $slotStart + ($duration * 60);

        $overlap = false;
        foreach ($bookedSlots as $booked) {
            if (
                ($slotStart >= $booked['start'] && $slotStart < $booked['end']) ||
                ($slotEnd > $booked['start'] && $slotEnd <= $booked['end']) ||
                ($slotStart <= $booked['start'] && $slotEnd >= $booked['end'])
            ) {
                $overlap = true;
                break;
            }
        }

        if (!$overlap) {
            $formattedStart = date("g:i A", $slotStart);
            $formattedEnd = date("g:i A", $slotEnd);
            $slots[] = "$formattedStart - $formattedEnd";
        }

        $startTime += ($duration * 60) + $breakTime;
    }

    echo json_encode($slots);
} else {
    echo json_encode([]);
}
$conn->close();
