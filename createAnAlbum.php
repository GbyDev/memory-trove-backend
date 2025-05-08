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

//Sanitize the Album Name
$album_name = trim(preg_replace('/[^A-Za-z0-9_\- ]/', '', $data["album_name"]));
$album_name = strtolower($album_name);

$album_path = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name";
$album_qrcode_path = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name/qrcode";
$album_cover_img_path = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name/coverPhoto";
$album_images_path = "C:/xampp/htdocs/memory-trove-backend/albums/$album_name/images";
$message = "";
$messageType = "";


function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}


function album_already_exists(){
    global $album_path, $album_name;
    //Check if folder exists
    if (file_exists($album_path)) {
        setMessage("error", "Album of the same name already exists.");
        return true;
    }
    setMessage("success", "Album $album_name has not yet been created.");
    return false;
}

function album_folder_is_created(){
    global $album_path, $album_name;
    if (mkdir($album_path, 0777, true)) {
        setMessage("success", "Album $album_name has been created at $album_path");
        return true;
    } 
    setMessage("error", "There are problems creating the folder.");
    return false;
}

function create_subfolders(){
    global $album_qrcode_path, $album_images_path, $album_cover_img_path;
    //If folder doesn't exist, create that folder
    if (!file_exists($album_qrcode_path)){
        mkdir($album_qrcode_path, 0777, true);
    }
    if (!file_exists($album_images_path)){
        mkdir($album_images_path, 0777, true);
    }
    if (!file_exists($album_cover_img_path)){
        mkdir($album_cover_img_path, 0777, true);
    }
}


function main(){
    if (album_already_exists()) return;
    if (!album_folder_is_created()) return;
    create_subfolders();
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