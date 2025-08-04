<?php
session_start();

// Debugging (dev only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// DB connection
include '../db.php';

// Auth check
if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user']['id'];

// Decode incoming JSON
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data || !isset($data['address']) || !isset($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request data"]);
    exit;
}

$address = $data['address'];
$cart = $data['cart'];

// Validate address fields
$requiredFields = ['name', 'phone', 'street', 'city', 'state', 'zip'];
foreach ($requiredFields as $field) {
    if (empty($address[$field])) {
        echo json_encode(["status" => "error", "message" => "Missing address field: $field"]);
        exit;
    }
}

// Prepare order insert
$orderStmt = $conn->prepare("INSERT INTO orders 
    (user_id, name, phone, street, city, state, zip, product_id, product_name, price, quantity, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

// Loop through cart items
foreach ($cart as $item) {
    // **IMPORTANT: Use product_id here, NOT id**
    $product_id = $item['product_id'] ?? null;
    $quantity = $item['quantity'] ?? 1;
    $price = $item['price'] ?? 0.00;

    if (!$product_id) continue;

    // Get product name from products table
    $stmtProduct = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmtProduct->bind_param("i", $product_id);
    $stmtProduct->execute();
    $result = $stmtProduct->get_result();
    $product = $result->fetch_assoc();
    $product_name = $product['name'] ?? "Unknown Product";

    // Insert order
    $orderStmt->bind_param(
        "issssssssdi",
        $user_id,
        $address['name'],
        $address['phone'],
        $address['street'],
        $address['city'],
        $address['state'],
        $address['zip'],
        $product_id,
        $product_name,
        $price,
        $quantity
    );
    $orderStmt->execute();
}

echo json_encode(["status" => "success", "message" => "Order submitted successfully"]);
