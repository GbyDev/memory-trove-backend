<?php
include('db.php'); 

//Headers
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
    global $data, $username, $email, $password;

    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
        setMessage("error", "Username, email, and password are required.");
    } 
    //If valid, store the data
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];
    setMessage("success", "Username is $username, email is $email, and password is $password.");
}


//Check if the username already exists
function user_already_exists() {
    global $conn, $username, $email;

    $numOfInvalidFields = 0;
    $usernameMessage = "";
    $emailMessage = "";
    $andText = "";
    $msgType = "";
    $description = "";
    $fullMessage = "$usernameMessage $andText $emailMessage $description";

    //Check if the username is already taken
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $usernameMessage = "Username";
        $description = "already exists.";
        $numOfInvalidFields++;
    } 
    //Check if the email is already taken
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $emailMessage = "Email";
        $description = "already exists.";
        $numOfInvalidFields++;
    } 

    switch ($numOfInvalidFields) {
        case 0:
            $fullMessage = "Username and email are available.";
            $msgType = "success";
            setMessage($msgType, $fullMessage);
            return false;
        case 1:
            $fullMessage = "$usernameMessage $andText $emailMessage $description";
            $msgType = "error";
            setMessage($msgType, $fullMessage);
            return true;
        case 2:
            $andText = "and";
            $fullMessage = "$usernameMessage $andText $emailMessage $description";
            $msgType = "error";
            setMessage($msgType, $fullMessage);
            return true;
    }
}

function storeValuesInDB() {
    global $conn, $username, $email, $password;

    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        setMessage("success", "User registered successfully.");
    } 
    else {
        setMessage("error", "Error: " . $conn->error);
    }
}


check_if_data_is_received();
if(!user_already_exists()){
    //Must be implemented in the future
    //hashPassword();
    storeValuesInDB();
}
    


//Output the messages (final)
echo json_encode([
    "message" => $message,
    "messageType" => $messageType
]);
exit(0);
?>