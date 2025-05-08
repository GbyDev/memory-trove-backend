<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// === GET TEXT FIELDS FROM $_POST === //
$user_id = $_POST["user_id"];
$current_album_num = $_POST["current_album_num"];

//Variables
$album_cover_img_path = "";
$album_name = "";
$date_created = "";
$album_description = "";

$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function get_album_details() {
    global $conn;
    global $user_id;
    global $album_cover_img_path, $album_name, $date_created, $album_description;

    $sql = "SELECT album_name, date_created, album_description, album_cover_img_path FROM albums WHERE user_id = '$user_id'";
    $result = $conn->query($sql);
    //if 
}



function main(){
    clearstatcache(); 

}




main();

echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "album_path" => $album_path,
    "album_qrcode_path" => $album_qrcode_path,
    "album_images_path" => $album_images_path,
    "album_cover_img_path" => $album_cover_img_path
]);
exit(0);
?>