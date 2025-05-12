<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents("php://input"), true);

$data = json_decode($_POST['payload'], true);
$album_id = $data['album_id'];
$album_folder_path = $data['album_folder_path'];
$album_images_folder = $album_folder_path . "/images";

// Validate
if (!is_dir($album_images_folder)) {
    http_response_code(400);
    echo json_encode(["messageType" => "error", "message" => "Invalid album path."]);
    exit;
}

$zip_name = basename($album_folder_path) . ".zip";
$zip_path = $album_folder_path . "/" . $zip_name;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    http_response_code(500);
    echo json_encode(["messageType" => "error", "message" => "Could not create ZIP file."]);
    exit;
}

$files = scandir($album_images_folder);
foreach ($files as $file) {
    $filePath = $album_images_folder . "/" . $file;
    if (is_file($filePath)) {
        $zip->addFile($filePath, $file);
    }
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
header('Content-Length: ' . filesize($zip_path));
readfile($zip_path);
unlink($zip_path); // 🧹 Remove ZIP after sending it
exit;