<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get the Variables
$user_id = $_POST["user_id"];
$album_id = $_POST["album_id"];
$old_album_name = $_POST["old_album_name"];
$new_album_name = $_POST["new_album_name"];
$welcome_text = $_POST["welcome_text"];
$album_desc = $_POST["album_desc"];
$qr_code_url = $_POST["url"];
$cover_photo = $_FILES["cover_photo"];

// Folder Paths
$old_folder_path = "C:/xampp/htdocs/memory-trove-backend/albums/$user_id/$old_album_name";
$new_folder_path = "C:/xampp/htdocs/memory-trove-backend/albums/$user_id/$new_album_name";

// Output message function
function output_out_message($message, $message_type) {
    $output = array(
        'message' => $message,
        'messageType' => $message_type
    );
    echo json_encode($output);
    exit(0);
}

// Change the folder name
function change_folder_name() {
    global $old_folder_path, $new_folder_path;

    if ($old_folder_path !== $new_folder_path) {
        if (!is_dir($old_folder_path)) {
            output_out_message("Old folder does not exist.", "error");
        }

        if (!rename($old_folder_path, $new_folder_path)) {
            output_out_message("Failed to rename the folder.", "error");
        }
    }
}

// Create a new QR code
function create_new_qr_code() {
    global $qr_code_url, $new_folder_path;
    $qr_code_path = $new_folder_path . "/qrcode/qrcode.png";

    // Delete the old QR code if it exists
    if (file_exists($qr_code_path)) {
        unlink($qr_code_path);
    }

    // Create the new one
    include_once('./phpqrcode/qrlib.php');
    $dir = dirname($qr_code_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    QRcode::png($qr_code_url, $qr_code_path, QR_ECLEVEL_L, 10);
}

// Function to delete a directory and its contents recursively
function delete_directory($dirPath) {
    // Check if the directory exists
    if (!is_dir($dirPath)) return;

    // Get all the files and subdirectories inside the directory
    $files = array_diff(scandir($dirPath), array('.', '..'));

    // Loop through all files/subdirectories and delete them
    foreach ($files as $file) {
        $filePath = $dirPath . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filePath)) {
            delete_directory($filePath); // Recursively delete subdirectories
        } else {
            unlink($filePath); // Delete files
        }
    }

    // Once the directory is empty, delete it
    rmdir($dirPath);
}

// Replace the cover photo
function replace_cover_photo() {
    global $new_folder_path, $cover_photo;

    // Define the cover folder path
    $cover_folder = $new_folder_path . "/cover";

    // Check if the cover folder exists and delete it entirely
    if (is_dir($cover_folder)) {
        // Recursively delete the folder and all its contents
        delete_directory($cover_folder);
    }

    // Recreate the cover folder
    if (!mkdir($cover_folder, 0777, true)) {
        output_out_message("Failed to create cover folder.", "error");
    }

    // Check if a new file was uploaded
    if (!isset($cover_photo) || $cover_photo['error'] !== UPLOAD_ERR_OK) {
        output_out_message("No valid cover photo uploaded or an error occurred.", "error");
    }

    // Get file information and extension
    $originalName = basename($cover_photo['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Validate the file extension (only allow certain file types)
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($extension, $allowed_extensions)) {
        output_out_message("Invalid file type. Only JPG, JPEG, and PNG files are allowed.", "error");
    }

    // Save with standard name and detected extension (always "cover_photo.*")
    $targetFile = $cover_folder . "/cover_photo." . $extension;

    // Move the uploaded file to the target location
    if (!move_uploaded_file($cover_photo['tmp_name'], $targetFile)) {
        output_out_message("Failed to save the new cover photo.", "error");
    }

    // Success message
    output_out_message("Cover photo replaced successfully.", "success");
}


// Call the functions in order
change_folder_name();
create_new_qr_code();
replace_cover_photo();

output_out_message("Album updated successfully", "success");
?>
