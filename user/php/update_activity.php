<?php
session_start();
require_once '../db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $now = date('Y-m-d H:i:s');

    $sql = "UPDATE users SET last_activity = ?, status = 'active' WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $now, $user_id);
    $stmt->execute();
}
?>
