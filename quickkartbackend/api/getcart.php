<?php
// --- CORS & Headers ---
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// --- Include DB Connection ---
include '../db.php';

// --- SQL to Join Cart and Products ---
$sql = "SELECT 
            cart.id AS cart_id, 
            cart.product_id, 
            cart.price, 
            cart.quantity, 
            products.name AS product_name, 
            products.image
        FROM cart 
        JOIN products ON cart.product_id = products.id";

// --- Execute SQL ---
$result = mysqli_query($conn, $sql);

// --- Prepare Response ---
$cart = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cart[] = [
            'id' => $row['cart_id'],             // ✅ Use cart.id for delete
            'product_id' => $row['product_id'],  // Optional: useful for tracking
            'name' => $row['product_name'],      // ✅ Correct product name from products table
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'image' => $row['image']
        ];
    }

    echo json_encode([
        "status" => "success",
        "data" => $cart
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No items in cart."
    ]);
}

// --- Close Connection ---
$conn->close();
?>
