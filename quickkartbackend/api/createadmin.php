<?php
include '../db.php'; // ✅ Go one folder up
// Make sure this has $conn set up

$name = 'Admin User';
$email = 'admin@quickart.com';
$password = 'admin123'; // Choose a strong password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>
