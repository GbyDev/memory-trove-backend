<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

//DEBUG: Save all incoming POST and FILE data to disk for inspection
file_put_contents("debug_post.txt", print_r($_POST, true));
file_put_contents("debug_files.txt", print_r($_FILES, true));


$user_id = $_POST["user_id"] ?? null;
$album_id = $_POST["album_id"] ?? null;
$old_album_name = $_POST["old_album_name"] ?? null;
$new_album_name = $_POST["new_album_name"] ?? null;
$welcome_text = $_POST["welcome_text"] ?? null;
$album_desc = $_POST["album_desc"] ?? null;
$qr_code_url = $_POST["url"] ?? null;
$cover_photo = $_FILES["cover_photo"] ?? null;

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

    include_once('./phpqrcode/qrlib.php');
    $dir = dirname($qr_code_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    QRcode::png($qr_code_url, $qr_code_path, QR_ECLEVEL_L, 10);
}

// Function to delete a directory and its contents recursively
function delete_directory($dirPath) {
    if (!is_dir($dirPath)) return;
    $files = array_diff(scandir($dirPath), array('.', '..'));
    foreach ($files as $file) {
        $filePath = $dirPath . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filePath)) {
            delete_directory($filePath);
        } else {
            unlink($filePath);
        }
    }
    rmdir($dirPath);
}

// Replace the cover photo
function replace_cover_photo() {
    global $new_folder_path, $cover_photo, $cover_folder;

    // Define the cover folder path
    $cover_folder = $new_folder_path . "/cover";

    // Check if the cover folder exists and delete it entirely
    if (is_dir($cover_folder)) {
        delete_directory($cover_folder);
    }

    // Recreate the cover folder
    if (!mkdir($cover_folder, 0777, true)) {
        output_out_message("Failed to create cover folder.", "error");
    }

    // Check for valid upload
    if (!isset($cover_photo)) {
        output_out_message("Cover photo is missing from form submission.", "error");
    }

    if (!isset($cover_photo['error'])) {
        output_out_message("Cover photo does not have an error code.", "error");
    }

    if ($cover_photo['error'] !== UPLOAD_ERR_OK) {
        output_out_message("Upload error code: " . $cover_photo['error'], "error");
    }

    // Get file extension
    $originalName = basename($cover_photo['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($extension, $allowed_extensions)) {
        output_out_message("Invalid file type. Only JPG, JPEG, and PNG files are allowed.", "error");
    }

    $targetFile = $cover_folder . "/cover_photo." . $extension;

    if (!move_uploaded_file($cover_photo['tmp_name'], $targetFile)) {
        output_out_message("Failed to save the new cover photo.", "error");
    }

    // Success message
    output_out_message("Cover photo replaced successfully.", "success");
}

function update_album_in_database() {
    global $conn;
    global $user_id, $new_album_name, $welcome_text, $album_desc, $new_folder_path, $cover_folder, $album_id;

    $sql = "UPDATE albums SET 
        album_name = ?, 
        welcome_text = ?, 
        description = ?, 
        album_filepath = ?, 
        album_cover_img_path = ? 
        WHERE user_id = ? AND album_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", 
        $new_album_name, 
        $welcome_text, 
        $album_desc,  
        $new_folder_path,
        $cover_folder,
        $user_id,
        $album_id
    );
    $stmt->execute();
}

// MAIN EXECUTION
change_folder_name();
create_new_qr_code();

if (isset($cover_photo)) {
    replace_cover_photo();
}

update_album_in_database();

echo json_encode([
    "message" => "Album updated successfully.",
    "messageType" => "success",
    "albumId" => $album_id,
    "albumName" => $new_album_name,
    "albumFolderPath" => $new_folder_path,
    "albumWelcomeText" => $welcome_text,
    "albumDescription" => $album_desc,
    "albumCoverImagePath" => isset($cover_folder) ? $cover_folder : "unchanged"
]);
exit(0);

?>