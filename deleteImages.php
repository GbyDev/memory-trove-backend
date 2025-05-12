<?php
include('db.php'); // Include your database connection file

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0); // Exit early for OPTIONS requests (CORS preflight)
}

// Read the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Isolate the individual values
$album_id = $data['album_id'];
$album_folder_path = $data['album_folder_path'];
$selected_images = $data['selected_images']; 
$num_of_images = count($selected_images);

$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

//For testing
//Adding the "/images/" between the album's folder path and the image's file name
function concatenate_all_filepaths() {
    global $selected_images, $album_folder_path;
    $all_filepaths = [];
    foreach ($selected_images as $filename) {
        $all_filepaths[] = $album_folder_path . "/images/" . $filename;
    }
    return $all_filepaths; 
}

//The images are stored like this: /images/filename
//so just add the "images/"
function concatenate_all_filenames() {
    global $selected_images;
    $all_filenames = [];
    foreach ($selected_images as $filename) {
        $all_filenames[] = "images/" . $filename;
    }
    return implode(",", $all_filenames);
}

function delete_images_from_folder($all_filepaths) {
    if (count($all_filepaths) == 0) {
        setMessage("error", "No files selected");
        return;
    }
    foreach ($all_filepaths as $filepath) {
        unlink($filepath);
    }
}

function delete_images_from_database(){
    global $conn, $album_id, $num_of_images;
    $sql = "DELETE FROM images WHERE album_id = ? LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $album_id, $num_of_images);
    $stmt->execute();
    $stmt->close();
}


$all_filepaths = concatenate_all_filepaths();
$all_filenames = concatenate_all_filenames();
setMessage("black", "$all_filenames");

exit(json_encode([
    "message" => $message,
    "messageType" => $messageType,
]));
?>
