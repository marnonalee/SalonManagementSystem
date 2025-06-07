<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    $checkSql = "SELECT * FROM users WHERE user_id = ? AND archived = 1";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $updateSql = "UPDATE users SET archived = 0, archived_at = NULL WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $user_id);
        if ($updateStmt->execute()) {
            $_SESSION['success_message'] = "User restored successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to restore user. Please try again.";
        }
        $updateStmt->close();
    } else {
        $_SESSION['error_message'] = "User not found or already active.";
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

header("Location: ../service_archive.php");
exit();
?>
