<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include '../db.php';

$sql = "
  SELECT 
    orders.id,
    orders.name,
    orders.city,
    orders.quantity,
    orders.price,
    orders.status,
    orders.product_id,
    products.name AS product_name,
    products.image AS product_image
  FROM orders
  JOIN products ON orders.product_id = products.id
  ORDER BY orders.id DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $orders]);
} else {
    echo json_encode(["status" => "error", "message" => "No orders found"]);
}
?>
