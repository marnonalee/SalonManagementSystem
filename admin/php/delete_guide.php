<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guide_id'])) {
    $guide_id = intval($_POST['guide_id']);

    $result = $conn->query("SELECT media FROM beauty_guide WHERE id = $guide_id");
    if ($result && $row = $result->fetch_assoc()) {
        $mediaPath = $row['media'];
        if ($mediaPath && file_exists($mediaPath)) {
            unlink($mediaPath);
        }
    }

    $stmt = $conn->prepare("DELETE FROM beauty_guide WHERE id = ?");
    $stmt->bind_param("i", $guide_id);
    if ($stmt->execute()) {
        header("Location: ../beauty_guide.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting guide: " . $stmt->error;
    }
    $stmt->close();
} else {
    header("Location: ../beauty_guide.php");
    exit();
}
?>
