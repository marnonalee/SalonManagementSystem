<?php
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$conn = new mysqli('localhost:4306', 'root', '', 'salon');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'], $data['title'], $data['content'], $data['media'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

$id = intval($data['id']);
$title = $conn->real_escape_string($data['title']);
$content = $conn->real_escape_string($data['content']);
$media = $data['media']; 

$mediaPath = null;

if (strpos($media, 'data:image') === 0) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    preg_match('/^data:image\/(\w+);base64,/', $media, $type);
    $imageType = strtolower($type[1]); 

    if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unsupported image type']);
        exit();
    }

    $mediaData = substr($media, strpos($media, ',') + 1);
    $mediaData = base64_decode($mediaData);

    if ($mediaData === false) {
        echo json_encode(['status' => 'error', 'message' => 'Base64 decode failed']);
        exit();
    }

    $fileName = uniqid() . '.' . $imageType;
    $filePath = $uploadDir . $fileName;

    if (file_put_contents($filePath, $mediaData) === false) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save image']);
        exit();
    }

    $mediaPath = 'uploads/' . $fileName;
} else {
    $mediaPath = $conn->real_escape_string($media);
}

$stmt = $conn->prepare("UPDATE beauty_guide SET title=?, content=?, media=? WHERE id=?");
$stmt->bind_param("sssi", $title, $content, $mediaPath, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
