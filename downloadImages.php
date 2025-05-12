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
$album_folder_path = $data['album_folder_path'];
$album_name = $data['album_name'];
$selected_images = $data['selected_images'];

function setMessage($type, $message) {
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

function concatenate_all_filepaths($selected_images, $album_folder_path) {
    return array_map(fn($filename) => $album_folder_path . "/images/" . $filename, $selected_images);
}

function check_files_exist($all_filepaths) {
    foreach ($all_filepaths as $filePath) {
        if (!file_exists($filePath)) {
            respondAndExit("error", "File not found: $filePath");
        }
    }
}

function store_images_in_zip($album_name, $album_folder_path, $selected_images) {
    $zip = new ZipArchive();
    $zip_file_name = sys_get_temp_dir() . "/" . uniqid($album_name . "_") . ".zip";

    if ($zip->open($zip_file_name, ZipArchive::CREATE) !== TRUE) {
        respondAndExit("error", "Failed to create zip file.");
    }

    // Add files to the ZIP in a simplified way
    foreach ($selected_images as $filename) {
        $filePath = $album_folder_path . "/images/" . $filename;

        // Check if file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            error_log("Skipping unreadable or missing file: " . $filePath);
            continue; // Skip if the file doesn't exist or is unreadable
        }

        // Add the file to the zip with its base name
        $zip->addFile($filePath, basename($filePath));
    }

    // Closing the zip file
    $zip->close();

    return $zip_file_name;
}

function send_file_for_download($file_path) {
    if (!file_exists($file_path)) {
        respondAndExit("error", "File does not exist.");
    }

    // Send the file as a download
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    ob_clean(); // Clean the output buffer
    flush();
    readfile($file_path); // Send the file to the user

    // Clean up the temp zip file after download
    unlink($file_path);

    exit;
}

// MAIN
if (empty($selected_images)) {
    respondAndExit("error", "No images selected.");
}

$all_filepaths = concatenate_all_filepaths($selected_images, $album_folder_path);
check_files_exist($all_filepaths);

// Create the zip file and send it for download
$zip_file_name = store_images_in_zip($album_name, $album_folder_path, $selected_images);
send_file_for_download($zip_file_name);
?>
