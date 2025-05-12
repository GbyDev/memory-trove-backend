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
$user_id = "";
$username = "";
$email = "";
$password = "";
$message = "";
$messageType = "";

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function check_if_data_is_received() {
    global $data, $username, $email, $password, $user_id;

    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['user_id'])) {
        setMessage("error", "Username, email, and password are required.");
    } 
    //If valid, store the data
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];
    $user_id = $data['user_id'];
    setMessage("success", "Username is $username, email is $email, and password is $password.");
}


function input_is_taken() {
    global $conn, $user_id, $email, $username;

    $sql = "SELECT username FROM users WHERE user_id = '$user_id'";
    $result = $conn->query($sql);
    
    $old_username = $result->fetch_assoc()['username'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND username != '$old_username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Username is already taken by someone else
        setMessage("error", "Username is already taken by someone else.");
        return true;
    }

    $sql = "SELECT email FROM users WHERE user_id = '$user_id'";
    $result = $conn->query($sql);
    
    $old_email = $result->fetch_assoc()['email'];

    $sql - "SELECT * FROM users WHERE email = '$email' AND email != '$old_email'";

    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Email is already taken by someone else
        setMessage("error", "Email is already taken by someone else.");
        return true;
    }
    return false;
}

function storeValuesInDB() {
    global $conn, $username, $email, $password, $user_id;

    $sql = "UPDATE users 
            SET 
            username = '$username', 
            email = '$email', 
            password = '$password' 
            WHERE user_id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        setMessage("success", "User details changed successfully.");
    } 
    else {
        setMessage("error", "Error: " . $conn->error);
    }
}

function main() {
    check_if_data_is_received();
    storeValuesInDB();
}
main();
echo json_encode([
    'messageType' => $messageType,
    'message' => $message,
    'newUsername' => $username,
    'newEmail' => $email,
    'newPassword' => $password,
]);
exit;
?>