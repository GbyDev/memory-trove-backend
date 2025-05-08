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

//Sanitize the Album Name
$album_name = trim(preg_replace('/[^A-Za-z0-9_\- ]/', '', $data["album_name"]));
$album_name = strtolower($album_name);

$album_qrcode_path = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name/qrcode";

$message = "";
$messageType = "";

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function qrcode_folder_exists(){
    global $album_qrcode_path;

    if (!file_exists($album_qrcode_path)) {
        setMessage("error", "Album qrcode folder does not exist. Creating now.");
        mkdir($album_qrcode_path, 0777, true);
        return false;
    } 
    setMessage("success", "Album qrcode folder exists.");
    return true;
}






main();
//Output the messages (final)
echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "album_path" => $album_path,
    "album_qrcode_path" => $album_qrcode_path,
    "album_images_path" => $album_images_path,
    
]);
exit(0);
?>