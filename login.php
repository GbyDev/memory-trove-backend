<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once('../config/db.php');

$data = json_decode(file_get_contents("php://input"));

$username = $conn->real_escape_string($data->username);
$password = $data->password;

$result = $conn->query("SELECT * FROM users WHERE username='$username'");
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    echo json_encode([
        "message" => "Login successful.",
        "user_id" => $user['user_id']
    ]);
} else {
    echo json_encode(["error" => "Invalid credentials."]);
}
?>