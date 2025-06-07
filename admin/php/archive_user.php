<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $archived_at = date('Y-m-d H:i:s'); 

    $query = "UPDATE users SET archived = 1, archived_at = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $archived_at, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "User successfully archived.";
    } else {
        $_SESSION['success'] = "Failed to archive user.";
    }

    mysqli_stmt_close($stmt);
}

header("Location: ../user_management.php");
exit();
?>
