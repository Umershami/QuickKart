<?php
session_start();

// ✅ Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ✅ Auth check
if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in."]);
    exit;
}

$userId = $_SESSION['user']['id'];

include '../db.php';

// ✅ Fetch orders for the logged-in user
$sql = "SELECT id, product_id, product_name, quantity, price, status, order_time
        FROM orders
        WHERE user_id = ?
        ORDER BY order_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(["status" => "success", "data" => $orders]);
?>
