<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if (isset($_SESSION['user'])) {
    echo json_encode(["status" => "success", "user" => $_SESSION['user']]);
} else {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
}
?>
