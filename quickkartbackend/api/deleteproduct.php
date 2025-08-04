<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../db.php';

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);

$data = json_decode(file_get_contents("php://input"), true); // ✅ fixed

if (isset($data['id'])) {
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "ID not provided."]);
}

$stmt->close();
$conn->close();
