<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    isset($data['product_id']) &&
    isset($data['name']) &&
    isset($data['price']) &&
    isset($data['quantity'])
) {
    $product_id = intval($data['product_id']);
    $name = $data['name'];
    $price = floatval($data['price']);
    $new_quantity = intval($data['quantity']);

    if ($new_quantity <= 0) {
        echo json_encode(["status" => "error", "message" => "Quantity must be at least 1."]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Step 1: Check current stock
        $stockCheck = $conn->prepare("SELECT quantity FROM products WHERE id = ? FOR UPDATE");
        $stockCheck->bind_param("i", $product_id);
        $stockCheck->execute();
        $stockResult = $stockCheck->get_result()->fetch_assoc();
        $stockCheck->close();

        if (!$stockResult) {
            throw new Exception("Product not found.");
        }

        $availableStock = intval($stockResult['quantity']);

        // Step 2: Check if product already in cart
        $checkCart = $conn->prepare("SELECT quantity FROM cart WHERE product_id = ?");
        $checkCart->bind_param("i", $product_id);
        $checkCart->execute();
        $cartResult = $checkCart->get_result()->fetch_assoc();
        $checkCart->close();

        if ($cartResult) {
            $old_cart_qty = intval($cartResult['quantity']);
            $diff_qty = $new_quantity - $old_cart_qty;

            if ($diff_qty > $availableStock) {
                throw new Exception("Not enough stock to increase quantity.");
            }

            // Update cart quantity
            $updateCart = $conn->prepare("UPDATE cart SET quantity = ? WHERE product_id = ?");
            $updateCart->bind_param("ii", $new_quantity, $product_id);
            $updateCart->execute();
            $updateCart->close();

            // Update stock (only adjust by difference)
            if ($diff_qty > 0) {
                $updateProduct = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $updateProduct->bind_param("ii", $diff_qty, $product_id);
                $updateProduct->execute();
                $updateProduct->close();
            }
        } else {
            // New product in cart
            if ($new_quantity > $availableStock) {
                throw new Exception("Not enough stock available.");
            }

            $insert = $conn->prepare("INSERT INTO cart (product_id, name, price, quantity) VALUES (?, ?, ?, ?)");
            $insert->bind_param("isdi", $product_id, $name, $price, $new_quantity);
            if (!$insert->execute()) {
                throw new Exception("Insert failed.");
            }
            $insert->close();

            // Decrease stock
            $updateProduct = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $updateProduct->bind_param("ii", $new_quantity, $product_id);
            $updateProduct->execute();
            $updateProduct->close();
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Cart updated and product stock adjusted."]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
}
?>
