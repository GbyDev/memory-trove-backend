<?php
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
$username_email = "";
$password = "";
$message = "";
$messageType = "";

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function check_if_data_is_received() {
    global $data, $username_email, $password;

    if (!isset($data['username_email']) || !isset($data['password'])) {
        setMessage("error", "Username/email, and password are required.");
    } 
    //If valid, store the data
    $username_email = $data['username_email'];
    $password = $data['password'];
    setMessage("success", "Username/email is $username_email, and password is $password.");
}

function input_type_if_email_or_username() {
    global $username_email, $username, $email;
    if (strpos($username_email, '@') !== false) {
        setMessage("black", "Input is an email.");
    }
    setMessage("black", "Input is a username.");
}

/*
function user_exists() {
    global $conn, $username_email;

    $numOfInvalidFields = 0;
    $usernameMessage = "";
    $emailMessage = "";
    $andText = "";
    $msgType = "";
    $description = "";
    $fullMessage = "$usernameMessage $andText $emailMessage $description";

    //Check if the username/email exists
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
*/

//Function calls 

check_if_data_is_received();
input_type_if_email_or_username();
//Output the messages (final)
/*
echo json_encode([
    "message" => $message,
    "messageType" => $messageType
]);
exit(0);
*/
?>