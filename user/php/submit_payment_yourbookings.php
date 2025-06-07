<?php
include '../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $payment_method_id = $_POST['payment_method_id'];
    $payment_type = $_POST['payment_type'];
    $created_at = date('Y-m-d H:i:s');
    $payment_status = "pending verification";

    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['payment_screenshot']['tmp_name'];
        $fileName = $_FILES['payment_screenshot']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedfileExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadDir = './uploads/payment_proofs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $newFileName = 'payment_' . $appointment_id . '_' . time() . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $stmt = $conn->prepare("INSERT INTO payments (appointment_id, payment_method_id, payment_type, payment_proof, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $appointment_id, $payment_method_id, $payment_type, $newFileName, $payment_status, $created_at);

                if ($stmt->execute()) {
                    header("Location: ../your_bookings.php?msg=Payment+submitted+for+verification");
                    exit();
                } else {
                    echo "Failed to insert payment: " . $stmt->error;
                }
            } else {
                echo "Failed to upload file.";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG allowed.";
        }
    } else {
        echo "No file uploaded or upload error.";
    }
} else {
    echo "Invalid request.";
}
?>
