<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error'] = "Missing parameters.";
    header("Location: ../user_management.php");
    exit();
}

$user_id = intval($_GET['id']);
$action = $_GET['action'];
$new_status = "";

$current_query = "SELECT status FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $current_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $current_status);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$current_status) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../user_management.php");
    exit();
}

switch ($action) {
    case 'block':
        $new_status = 'blocked';
        break;
    case 'unblock':
        $new_status = 'active';
        break;
    case 'deactivate':
        $new_status = 'inactive';
        break;
    case 'activate':
        $new_status = 'active';
        break;
    default:
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../user_management.php");
        exit();
}

$update_sql = "UPDATE users SET status = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $update_sql);

if (!$stmt) {
    $_SESSION['error'] = "Prepare failed: " . mysqli_error($conn);
    header("Location: ../user_management.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success'] = "User status updated to " . ucfirst($new_status) . ".";
    } else {
        $_SESSION['error'] = "No changes made. Status may already be " . ucfirst($new_status) . ".";
    }
} else {
    $_SESSION['error'] = "Execution failed: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

// Redirect
header("Location: ../user_management.php");
exit();
