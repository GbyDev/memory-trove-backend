<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Fetch the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Log the incoming data for debugging
error_log(print_r($data, true));

// Validate incoming data
if (!isset($data['album_id']) || !isset($data['album_folder_path']) || !isset($data['selected_images'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$album_id = $data['album_id'];
$album_folder_path = $data['album_folder_path'];
$selected_images = json_decode($data['selected_images'], true);

// Validate selected images
if (!is_array($selected_images) || empty($selected_images)) {
    echo json_encode(['success' => false, 'message' => 'No images selected for deletion']);
    exit();
}

// Function to delete images from the database
function delete_images_from_database($album_id, $selected_images) {
    global $conn;

    // Prepare placeholders for the SQL query
    $placeholders = implode(',', array_fill(0, count($selected_images), '?'));

    // SQL query to delete images from the database
    $sql = "DELETE FROM images WHERE album_id = ? AND file_name IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically
    $types = str_repeat('s', count($selected_images)) . 'i'; // 's' for string, 'i' for integer
    $params = array_merge($selected_images, [$album_id]);
    $stmt->bind_param($types, ...$params);

    // Execute the query
    if ($stmt->execute()) {
        return true;
    } else {
        error_log("Error deleting images from database: " . $stmt->error);
        return false;
    }
}

// Function to delete image files from the folder
function delete_image_files($album_folder_path, $selected_images) {
    foreach ($selected_images as $image) {
        $image_path = $album_folder_path . '/' . $image;
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the file
        } else {
            error_log("File not found: $image_path");
        }
    }
}

// Delete images from the database
if (!delete_images_from_database($album_id, $selected_images)) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete images from database']);
    exit();
}

// Delete image files from the folder
delete_image_files($album_folder_path, $selected_images);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Successfully deleted selected images.',
]);
?>
