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
$album_id = $_POST["album_id"];
$album_folder_path = $_POST["album_folder_path"];
$current_image_num = $_POST["current_image_num"];

// Output Variables
$image_id = "";
$file_name = "";
$full_image_url = "";
$uploaded_at = "";

$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function get_image_details() {
    global $conn;
    global $album_id, $album_folder_path, $current_image_num;
    global $image_id, $file_name, $uploaded_at, $full_image_url;

    $sql = "SELECT img_id, file_name, uploaded_at 
            FROM images 
            WHERE album_id = '$album_id' 
            ORDER BY uploaded_at ASC 
            LIMIT 1 OFFSET $current_image_num";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_id = $row["img_id"];
        $file_name = $row["file_name"];
        $uploaded_at = $row["uploaded_at"];

        $relativePath = str_replace("C:/xampp/htdocs", "", $album_folder_path);
        $full_image_url = "http://localhost" . $relativePath . '/' . $file_name;
    } 
    else {
        setMessage("error", "No image found at that index.");
    }
}

function main(){
    clearstatcache();
    get_image_details();
}

main();

echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "imageId" => $image_id,
    "fileName" => $file_name,
    "image_url" => $full_image_url,
    "uploadedAt" => $uploaded_at
]);
exit(0);
?>