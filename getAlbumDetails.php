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

// Output Variables
$album_id = ""; 
$album_cover_img_path = "";
$album_name = "";
$date_created = "";
$album_welcome_text = "";
$album_description = "";
$album_folder_path = "";

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
    global $current_album_num;
    global $album_id, $album_cover_img_path, $album_name, $date_created, $album_description, $album_folder_path, $album_welcome_text;

    $description_word = "description";

    $sql = "SELECT album_id, album_name, date_created, welcome_text, $description_word, album_cover_img_path, album_filepath 
            FROM albums 
            WHERE user_id = '$user_id' 
            ORDER BY date_created DESC 
            LIMIT 1 OFFSET $current_album_num";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $album_id = $row["album_id"]; 
        $album_name = $row["album_name"];
        $date_created = $row["date_created"];
        $album_welcome_text = $row["welcome_text"];
        $album_description = $row["description"];
        $album_cover_img_path = $row["album_cover_img_path"];
        $album_folder_path = $row["album_filepath"];
    }
}

function main(){
    clearstatcache(); 
    get_album_details();
}

main();

echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "albumId" => $album_id, // Include album_id in response
    "albumCoverImagePath" => $album_cover_img_path,
    "albumName" => $album_name,
    "dateCreated" => $date_created,
    "welcomeText" => $album_welcome_text,
    "albumDescription" => $album_description,
    "albumFolderPath" => $album_folder_path
]);
exit(0);
?>
