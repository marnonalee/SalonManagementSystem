<?php
session_start();
require_once '../db.php';

if (isset($_POST['service_id'])) {
    $id = intval($_POST['service_id']);
    $stmt = $conn->prepare("UPDATE services SET is_archived = 1 WHERE service_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service archived successfully.";
    }
}
header("Location: ../services.php");
exit();
