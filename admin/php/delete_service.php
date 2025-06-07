<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

if (isset($_POST['service_id'])) {
    $service_id = $_POST['service_id'];

    $sql = "DELETE FROM services WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Service deleted successfully!";
    } else {
        $_SESSION['message'] = "Failed to delete service!";
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "No service ID provided!";
}

header("Location: ../service_archive.php");
exit();
?>
