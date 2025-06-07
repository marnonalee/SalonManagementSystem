<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

require '../db.php'; 

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "User session invalid.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old-password'] ?? '';
    $new_password = $_POST['new-password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../profile.php?password_update=fail&msg=" . urlencode("All password fields are required."));
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../profile.php?password_update=fail&msg=" . urlencode("New password and confirm password do not match."));
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
        header("Location: ../profile.php?password_update=fail&msg=" . urlencode("New password must be at least 8 characters long and include uppercase, lowercase, number, and special character."));
        exit();
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        header("Location: ../profile.php?password_update=fail&msg=" . urlencode("User not found."));
        exit();
    }

    $row = $result->fetch_assoc();
    if (!password_verify($old_password, $row['password'])) {
        $_SESSION['password_error'] = "Old password is incorrect.";
        header("Location: ../profile.php");
        exit();
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $new_hash, $user_id);
    if ($update_stmt->execute()) {
        header("Location: ../profile.php?password_update=success");
        exit();
    } else {
        header("Location: ../profile.php?password_update=fail&msg=" . urlencode("Failed to update password."));
        exit();
    }
} else {
    header("Location: ../profile.php");
    exit();
}
