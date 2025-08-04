<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
include '../db.php';

$data=json_decode(file_get_contents("php://input"), true);

$name=$data['name'];
$email=$data['email'];
$password=password_hash($data   ['password'], PASSWORD_BCRYPT);
$role = $data['role'];
$sql="INSERT INTO users(name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $password, $role);


$response = [];

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = "User registered successfully!";
} else {
    $response['success'] = false;
    $response['message'] = "Error: " . $stmt->error;
}

echo json_encode($response);
?>