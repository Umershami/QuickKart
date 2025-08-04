<?php

header("Access-control-Allow-Origin:http://localhost:3000");
header("Access-Control-Allow-Methods:POST, GET, OPTIONS");
header("Access-Control-Allow-Headers:Content-Type");
header("Access-Control-Allow-Credentials:true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


include '../db.php';

$data=json_decode(file_get_contents("php://input"),true);



$name=$data['name'] ?? '';
$image = $data['image'] ?? '';
$price = $data['price'] ?? '';
$category = $data['category'] ?? '';
$quantitycount= $data['quantitycount'] ?? '';


if($name&& $image && $price && $category &&$quantitycount){
    $stmt=$conn->prepare("INSERT INTO products (name,image,price,category,quantity) VALUES (?,?,?,?,?)");

$stmt->bind_param("ssdsi", $name, $image, $price, $category, $quantitycount);

if($stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Product added successfully"
    ]);
}
 else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();

}
else {
    echo json_encode(["status" => "error", "message" => "Missing or invalid input"]);
}

$conn->close();
?>