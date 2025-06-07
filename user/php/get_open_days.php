<?php
header('Content-Type: application/json');
include '../db.php'; 
$conn = new mysqli("localhost:4306", "username", "password", "salon");

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$result = $conn->query("SELECT day FROM calendar_settings WHERE is_open = 1");

$open_days = [];
while ($row = $result->fetch_assoc()) {
    $open_days[] = $row['day'];
}

echo json_encode($open_days);
?>
