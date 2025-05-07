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
$username_email = "";
$password = "";
$message = "";
$messageType = "";

//Output Variables
$user_id = null; 
$username = null; //need to be obtained, to be stored to AuthContext

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function setUserId($extractedUserId) {
    global $user_id;
    $user_id = $extractedUserId;
}

function setUsername($extractedUsername) {
    global $username;
    $username = $extractedUsername;
}

function storePasswordFromDb($result){
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["password"];
    } 
    return null;
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

function check_input_type() {
    global $username_email;
    if (strpos($username_email, '@') !== false) {
        setMessage("black", "Input is an email.");
        return "email";
    }
    setMessage("black", "Input is a username.");
    return "username";
}

function get_user_id_if_user_exists() {
    global $conn, $username_email;

    //Check if the username/email exists
    $sql = "SELECT * FROM users WHERE username='$username_email' OR email='$username_email'";
    $result = $conn->query($sql);
    
    //If users exists
    if ($result->num_rows > 0) {
        //get the user id
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        setUserId($user_id); 

        setMessage("success", "User exists. User ID is " . $user_id . ".");
        return true;
    } 
    setMessage("error", "User does not exist.");
    return false;
}

function inputted_correct_pasword(){
    global $conn, $user_id, $password;

    //Get password of the user id
    $sql = "SELECT * FROM users WHERE user_id='$user_id'";
    $result = $conn->query($sql);
    $extracted_password = storePasswordFromDb($result);
    
    // Debug check
    if ($extracted_password === null) {
        setMessage("error", "User found, but password not retrieved.");
        return false;
    }

    //Check if the password is correct
    if ($extracted_password === $password) {
        setMessage("success", "Password is correct.");
        return true;
    }
    setMessage("error", "Password is incorrect. Password is " . $extracted_password . ".");
    return false;
}

//Sure way to get the username from the user id
//username is what usually is displayed to the account details
function extract_username(){
    global $conn, $user_id;

    $sql = "SELECT * FROM users WHERE user_id='$user_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        setUsername($username); 
        setMessage("success", "Username of user id " . $user_id . " is " . $username . ".");
        return true;
    } 
    setMessage("error", "User not found.");
    return false;
}
//Function calls 
function main_function(){
    check_if_data_is_received(); //get the data and check if recieved
    check_input_type(); //check whether username or email (only for debugging purposes)
    if(!get_user_id_if_user_exists()) return; //if user doesn't exist, exit
    if(!inputted_correct_pasword()) return; //if password is incorrect, exit
    extract_username(); //after passing all checks, get the username
}

main_function();
//Output the messages (final)
echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "userId" => $user_id,
    "username" => $username
]);
exit(0);
?>