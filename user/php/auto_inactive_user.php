<?php
require_once '../db.php';

$threshold = date('Y-m-d H:i:s', time() - 5); 

$sql = "UPDATE users SET status = 'inactive' WHERE last_activity < ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $threshold);
$stmt->execute();
?>
