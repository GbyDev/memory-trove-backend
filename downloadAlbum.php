<?php
include('db.php'); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents("php://input"), true);

// Variables
$album_id = $data['album_id'];
$album_folder_path = $data['album_folder_path'];
$album_name = $data['album_name'];

$album_images_folder = $album_folder_path . "/images";


function setMessage($type, $message){
    return [
        "messageType" => $type,
        "message" => $message
    ];
}

function respondAndExit($type, $message) {
    header("Content-Type: application/json");
    echo json_encode(setMessage($type, $message));
    exit;
}

function is_folder_empty(){
    global $album_images_folder;
    if (scandir($album_images_folder) === false) 
        respondAndExit("error", "Folder is empty.");
    return false;
}

function store_album_in_zip(){
    global $album_name, $album_images_folder;

    $zip_filename = $album_name . ".zip";
    $zip_filepath = $album_images_folder . "/../" . $zip_filename;

    $zip = new ZipArchive();
    if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        respondAndExit("error", "Failed to create ZIP file.");
    }

    $files = scandir($album_images_folder);
    foreach ($files as $file) {
        $file_path = $album_images_folder . "/" . $file;
        if (is_file($file_path)) {
            $zip->addFile($file_path, $file); // add with filename only
        }
    }

    $zip->close();

    if (!file_exists($zip_filepath)) {
        respondAndExit("error", "ZIP file not created.");
    }

    respondAndExit("success", "Album zipped successfully: " . basename($zip_filepath));
}


function main(){
    if(is_folder_empty()) return;
    store_album_in_zip();
}

main();


?>