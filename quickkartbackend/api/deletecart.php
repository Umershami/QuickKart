<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['cart_id'])) {
    $cartId = $data['cart_id'];

    // Get product_id and quantity from cart
    $sql = "SELECT product_id, quantity FROM cart WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $productId = $row['product_id'];
        $cartQuantity = $row['quantity'];
        $stmt->close();

        // Restore quantity in products table
        $updateSql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $cartQuantity, $productId);
        $updateStmt->execute();
        $updateStmt->close();

        // Delete from cart
        $deleteSql = "DELETE FROM cart WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $cartId);
        if ($deleteStmt->execute() && $deleteStmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Item deleted and quantity restored."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No item deleted."]);
        }
        $deleteStmt->close();
    } else {
        $stmt->close();
        echo json_encode(["status" => "error", "message" => "Cart item not found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "cart_id not provided."]);
}
?>
