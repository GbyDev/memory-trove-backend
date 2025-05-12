<?php
include('db.php'); 

//Access provider headers stuff para no errors
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0); 
}

//Variables
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? null;

//Output
$email = "";

$message = "";
$messageType = "";

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function getEmail(){
    global $conn, $user_id, $email;
    $sql = "SELECT email FROM users WHERE user_id = '$user_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $email = $row["email"];
        }
    }
}

getEmail();

echo json_encode([
    'newEmail' => $email,
]);
exit;
?>