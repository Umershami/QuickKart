<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:3000"); // ⚠️ No wildcard if using credentials
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['id'] ?? null;

if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Missing order ID"]);
    exit;
}

// Fetch order details
$stmt = $conn->prepare("SELECT name, phone, street, city, state, zip, product_name FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Order not found"]);
    exit;
}

$order = $result->fetch_assoc();
$customer_name = $order['name'];
$phone = $order['phone'];
$address = $order['street'] . ', ' . $order['city'] . ', ' . $order['state'] . ' - ' . $order['zip'];
$product_name = $order['product_name'];

$insert = $conn->prepare("INSERT INTO sent_parcels (order_id, product_name, customer_name, phone, address) VALUES (?, ?, ?, ?, ?)");
$insert->bind_param("issss", $order_id, $product_name, $customer_name, $phone, $address);

if (!$insert->execute()) {
    echo json_encode(["status" => "error", "message" => "Insert failed: " . $conn->error]);
    exit;
}

// Update status to sent (use the 'status' column or a separate 'sent' flag as needed)
$update = $conn->prepare("UPDATE orders SET status = 'sent' WHERE id = ?");
$update->bind_param("i", $order_id);
$update->execute();

echo json_encode(["status" => "success", "message" => "Parcel marked as sent."]);
$conn->close();
