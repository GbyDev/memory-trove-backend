<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// === GET TEXT FIELDS FROM $_POST === //
$user_id = $_POST["user_id"];
$album_name = trim($_POST["album_name"]);
$url = $_POST["url"];
$album_desc = $_POST["album_desc"];

$user_folder_path = "C:/xampp/htdocs/memory-trove-backend/albums/$user_id";
$album_path = "$user_folder_path/$album_name";
$album_qrcode_path = "$album_path/qrcode";
$album_cover_img_path = "$album_path/cover";
$album_images_path = "$album_path/images";
$qr_code_path = "$album_qrcode_path/qrcode.png";

$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function create_user_folder() {
    global $user_folder_path;
    if (!file_exists($user_folder_path)) {
        mkdir($user_folder_path, 0777, true);
    }
}

function create_album_folder() {
    global $album_path, $album_name;
    if (file_exists($album_path)) {
        setMessage("error", "Album of the same name already exists.");
        exit(json_encode([
            "message" => "Album already exists.",
            "messageType" => "error"
        ]));
    }
    mkdir($album_path, 0777, true);
}

function create_subfolders() {
    global $album_qrcode_path, $album_images_path, $album_cover_img_path;
    if (!file_exists($album_qrcode_path)) {
        mkdir($album_qrcode_path, 0777, true);
    }
    if (!file_exists($album_images_path)) {
        mkdir($album_images_path, 0777, true);
    }
    if (!file_exists($album_cover_img_path)) {
        mkdir($album_cover_img_path, 0777, true);
    }
}

//This part sucks. I hate how obscure its fix is.
function generate_QR_code() {
    global $url, $qr_code_path;

    include_once('./phpqrcode/qrlib.php');
    $dir = dirname($qr_code_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    QRcode::png($url, $qr_code_path, QR_ECLEVEL_L, 10);
}

//Code sphagetti, if it works, DONT TOUCH IT
function upload_cover_photo() {
    global $album_cover_img_path;

    // No file uploaded at all
    if (!isset($_FILES['cover_photo']) || $_FILES['cover_photo']['error'] === UPLOAD_ERR_NO_FILE) {
        setMessage("success", "Album created but with no cover image.");
        return;
    }

    // Error while uploading
    if ($_FILES['cover_photo']['error'] !== UPLOAD_ERR_OK) {
        setMessage("error", "There was an error uploading the cover photo.");
        exit(json_encode([
            "message" => "Cover photo upload failed.",
            "messageType" => "error"
        ]));
    }

    // File exists and has no upload error — proceed
    $originalName = basename($_FILES['cover_photo']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeFileName = "cover_photo." . $extension;
    $targetFilePath = "$album_cover_img_path/$safeFileName";

    if (!move_uploaded_file($_FILES['cover_photo']['tmp_name'], $targetFilePath)) {
        setMessage("error", "Failed to save the cover photo.");
        exit(json_encode([
            "message" => "Could not save uploaded image.",
            "messageType" => "error"
        ]));
    }

    setMessage("success", "Album created successfully with cover photo.");
}

function save_album_to_database() {
    global $conn;
    global $user_id, $album_name, $album_desc;
    global $album_path, $album_qrcode_path, $album_cover_img_path;

    // Detect cover photo path, or set it to "empty" if not available
    $coverPhotoFiles = glob("$album_cover_img_path/cover_photo.*");
    $coverPhotoPath = count($coverPhotoFiles) > 0 ? $coverPhotoFiles[0] : "empty";

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO albums (
        user_id, album_name, date_created, description,
        album_img_filepath, album_filepath, album_qr_code_path, album_cover_img_path
    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?)");

    if (!$stmt) {
        setMessage("error", "Failed to prepare database statement.");
        exit(json_encode([ 
            "message" => "DB prepare failed: " . $conn->error,
            "messageType" => "error"
        ]));
    }

    // Bind the parameters for the prepared statement
    // 'i' for integer, 's' for string, 'empty' for empty cover photo
    $stmt->bind_param("issssss", 
        $user_id,  // user_id (integer)
        $album_name,  // album_name (string)
        $album_desc,  // album_desc (string)
        $album_path,  // album_path (string)
        $album_qrcode_path,  // album_qr_code_path (string)
        $album_cover_img_path,  // album_cover_img_path (string)
        $coverPhotoPath  // cover photo (string, "empty" if no file uploaded)
    );

    // Execute the statement and check for errors
    if (!$stmt->execute()) {
        setMessage("error", "Failed to insert album into database.");
        exit(json_encode([
            "message" => "DB insert failed: " . $stmt->error,
            "messageType" => "error"
        ]));
    }

    // Close the statement after execution
    $stmt->close();

    setMessage("success", "Album saved to the database successfully.");
}

function main() {
    clearstatcache();
    create_user_folder();
    create_album_folder();
    create_subfolders();
    generate_QR_code();
    upload_cover_photo();
    save_album_to_database();
}

main();

echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "album_path" => $album_path,
    "album_qrcode_path" => $album_qrcode_path,
    "album_images_path" => $album_images_path,
    "album_cover_img_path" => $album_cover_img_path
]);
exit(0);
?>
