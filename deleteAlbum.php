<?php
include ('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents('php://input'), true);
$album_id = $data['albumId'];
$album_folder_path = $data['albumFolderPath'];


function delete_album_from_database(){
    global $conn, $album_id;
    $sql = "DELETE FROM album WHERE album_id = $album_id";
    $conn->query($sql);
}

function delete_album_folder(){
    global $album_folder_path;
    delete_directory($album_folder_path);
}

delete_album_from_database();
delete_album_folder();

exit(json_encode([
    "message" => "Album deleted successfully.",
    "messageType" => "success"
]));
?>