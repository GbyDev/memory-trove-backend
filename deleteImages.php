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
    return $all_filenames;
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

function delete_images_from_database($all_filenames){
    global $conn;
    
    if (count($all_filenames) == 0) {
        setMessage("error", "No files selected");
        return;
    }
    foreach ($all_filenames as $filename) {
        $sql = "DELETE FROM images WHERE file_name='$filename'";
        $conn->query($sql);
    }
    if ($conn->affected_rows > 0) {
        setMessage("success", "Images deleted successfully");
    } 
    else {
        setMessage("error", "Failed to delete images");
    }
}

function test_query($all_filenames) {
    global $conn;

    if (!is_array($all_filenames)) {
        $all_filenames = explode(",", $all_filenames);  // Split string into array
    }

    foreach ($all_filenames as $filename) {
        $sql = "SELECT * FROM images WHERE file_name='$filename'";
        $conn->query($sql);
    }

    if ($conn->affected_rows > 0) {
        return "Success";
    } 
    else {
        return "Failed";
    }
}


//Delete images from folder first
$all_filepaths = concatenate_all_filepaths();
delete_images_from_folder($all_filepaths);

//Then, delete images from database
$all_filenames = concatenate_all_filenames();
delete_images_from_database($all_filenames);
//$test_response = test_query($all_filenames);
//setMessage("black", "$test_response");

exit(json_encode([
    "message" => $message,
    "messageType" => $messageType,
]));
?>
