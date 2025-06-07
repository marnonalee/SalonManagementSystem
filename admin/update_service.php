<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$servername = "localhost:4306";
$username = "root";
$password = "";
$dbname = "salon";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_POST['service_id']);
$service_name = $_POST['service_name'];
$price = floatval($_POST['price']);

$hours = intval($_POST['hours']);
$minutes = intval($_POST['minutes']);
$duration = ($hours * 60) + $minutes;

$specialization_required = $_POST['specialization_required'];

$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
    $target_dir = "../uploads/";
    $filename = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/" . $filename;
    }
}

if ($image_path) {
    $sql = "UPDATE services SET service_name=?, price=?, duration=?, specialization_required=? WHERE service_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddssi", $service_name, $price, $duration, $specialization_required, $image_path, $id);
} else {
    $sql = "UPDATE services SET service_name=?, price=?, duration=?, specialization_required=? WHERE service_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddsi", $service_name, $price, $duration, $specialization_required, $id);
}

if ($stmt->execute()) {
    header("Location: services.php");
    exit();
} else {
    echo "Error updating service: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
