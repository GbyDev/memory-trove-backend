

<?php
//THIS CODE IS UNFINISHED. WORK ON IT LATER.

include ('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];    

$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function delete_account_from_database(){
    global $conn, $album_id;

}

function delete_directory($dir) {
    if (!file_exists($dir)) {
        return false; // Folder does not exist
    }

    if (!is_dir($dir)) {
        return unlink($dir); // Just a file
    }

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            delete_directory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

function delete_album_folder(){
    global $album_folder_path;
    delete_directory($album_folder_path);
}

delete_album_from_database();
delete_album_folder();
setMessage("black", "album id: $album_id album folder path: $album_folder_path");

exit(json_encode([
    "message" => $message,
    "messageType" => $messageType,
]));
?>