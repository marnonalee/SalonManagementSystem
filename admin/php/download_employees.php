<?php
include '../db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="employee_list.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['EMPLOYEE ID', 'NAME', 'SPECIALIZATION', 'EMAIL', 'STATUS', 'START TIME', 'END TIME']);

$query = "SELECT * FROM employees WHERE status != 'inactive' ORDER BY created_at ASC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['employee_id'],
        $row['name'],
        $row['specialization'],
        $row['email'],
        $row['status'],
        date("g:i A", strtotime($row['start_time'])),
        date("g:i A", strtotime($row['end_time']))
    ]);
}

fclose($output);
exit;
