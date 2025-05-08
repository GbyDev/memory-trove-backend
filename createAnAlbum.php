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
$user_id = $data["user_id"];
$album_name = $data["album_name"];

$album_filepath = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name";
$album_qrcode_filepath = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name/qrcode";
$album_images_filepath = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name/images";
$message = "";
$messageType = "";


function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}


function album_already_exists(){
    global $album_filepath, $album_name;
    //Check if folder exists
    if (file_exists($album_filepath)) {
        setMessage("error", "Album of the same name already exists.");
        return true;
    }
    setMessage("success", "Album $album_name has not yet been created.");
    return false;
}

function album_folder_is_created(){
    global $album_filepath, $album_name;
    if (mkdir($album_filepath, 0777, true)) {
        setMessage("success", "Album $album_name has been created at $album_filepath");
        return true;
    } 
    setMessage("error", "There are problems creating the folder.");
    return false;
}


function main(){
    if (album_already_exists()) return;
    if (!album_folder_is_created()) return;
}



main();
//Output the messages (final)
echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "album_filepath" => $album_filepath,
]);
exit(0);
?>