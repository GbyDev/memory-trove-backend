<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Variables
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;
$album_folder_path = isset($_POST['album_folder_path']) ? $_POST['album_folder_path'] : null;
$received_files = isset($_FILES['images']) ? $_FILES['images'] : null;

// Images folder path (based on album folder path)
$images_folder_path = $album_folder_path . "/images/";

$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function areFilesReceived($user_id, $received_files, $album_folder_path, $album_id) {
    if ($user_id == null) {
        setMessage("error", "No user id received.");
        return false;
    }
    if ($received_files == null) {
        setMessage("error", "No files received.");
        return false;
    }
    if ($album_folder_path == null) {
        setMessage("error", "No album folder path received.");
        return false;
    }
    if ($album_id == null || $album_id == 0) {
        setMessage("error", "No album id received.");
        return false;
    }
    setMessage("success", "Files received.");
    return true;
}

function storeValuesInDb($file_path, $album_id) {
    global $conn;
    $query = "INSERT INTO images (album_id, file_path, uploaded_at) VALUES (?, ?, CURDATE())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $album_id, $file_path);
    $stmt->execute();
    $stmt->close();
}

function uploadFiles($received_files, $images_folder_path, $album_id){
    $uploaded_files = [];

    // Ensure the image directory exists, create if necessary
    if (!file_exists($images_folder_path)) {
        mkdir($images_folder_path, 0777, true);  // Make sure the directory exists and is writable
    }

    foreach ($received_files['tmp_name'] as $index => $tmp_name) {
        $file_name = $received_files['name'][$index];
        $file_tmp = $received_files['tmp_name'][$index];
        $file_size = $received_files['size'][$index];
        $file_type = $received_files['type'][$index];
        $file_error = $received_files['error'][$index];

        if ($file_error === UPLOAD_ERR_OK) {
            $new_file_name = uniqid() . "_" . basename($file_name);
            $file_path = $images_folder_path . $new_file_name;  // Use dynamic folder path

            // Move the file to the target directory
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Store the file path in the database
                storeValuesInDb($file_path, $album_id);
                $uploaded_files[] = $file_path;
            } 
            else {
                setMessage("error", "Failed to move uploaded file.");
                return false;
            }
        }
    }
    return $uploaded_files;
}

function main(){
    global $user_id, $album_id, $album_folder_path, $received_files, $images_folder_path;
    // function calls only here
    if (!areFilesReceived($user_id, $received_files, $album_folder_path, $album_id)) return;
    setMessage("", "Filename is ". $album_folder_path);
    uploadFiles($received_files, $images_folder_path, $album_id);
}

main();

echo json_encode([
    "message" => $message,
    "messageType" => $messageType
]);
exit(0);
?>
