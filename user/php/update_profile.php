<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost:4306';
$dbname = 'salon';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_phone = trim($_POST['phone']);
    $current_username = $_SESSION['user'];
    $current_phone = $_SESSION['phone'];

    if ($new_username === $current_username && $new_phone === $current_phone) {
        header("Location: ../profile.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, phone = ? WHERE email = ?");
    $stmt->bind_param("sss", $new_username, $new_phone, $email);

    if ($stmt->execute()) {
        $_SESSION['user'] = $new_username;
        $_SESSION['phone'] = $new_phone;
        header("Location: ../profile.php?update=success");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
