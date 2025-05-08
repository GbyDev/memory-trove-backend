<?php
//include('db.php'); 

//Access provider headers stuff para no errors
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0); 
}

// Variables
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data["user_id"];
$url = $data["url"];

// Sanitize the Album Name
$album_name = $data["album_name"];
$album_name = strtolower($album_name);

$user_folder_path = "C:/xampp/htdocs/memory-trove-backend/albums/$user_id";
$album_path = "$user_folder_path" . "/$album_name";
$album_qrcode_path = "$album_path" . "/qrcode";
$album_cover_img_path = "$album_path" . "/cover";
$album_images_path = "$album_path" . "/images";

// QR Code file path 
$qr_code_path = "$album_qrcode_path" . "/qrcode.png";

//Album Description
$album_desc = $data["album_desc"];

$message = "";
$messageType = "";

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function create_user_folder()  {
    global $user_folder_path;
    if (!file_exists($user_folder_path)){
        mkdir($user_folder_path, 0777, true);
        setMessage("success", "User folder has been created.");
    }
}

function create_album_folder(){
    global $album_path, $album_name;
    // Check if folder exists
    if (file_exists($album_path)) {
        setMessage("error", "Album of the same name already exists.");
    }
    setMessage("success", "Album $album_name has not yet been created.");
    mkdir($album_path, 0777, true);
}

function create_subfolders(){
    global $album_qrcode_path, $album_images_path, $album_cover_img_path;
    // If folder doesn't exist, create that folder
    if (!file_exists($album_qrcode_path)){
        mkdir($album_qrcode_path, 0777, true);
    }
    if (!file_exists($album_images_path)){
        mkdir($album_images_path, 0777, true);
    }
    if (!file_exists($album_cover_img_path)){
        mkdir($album_cover_img_path, 0777, true);
    }
    setMessage("success", "Subfolders have been created.");
}

//OH MY GOD YOU'RE LITERALLY THE MOST DIFFICULT PART OF THE CODE
// F****** HOURS OF TINKERING WITH THIS BS
function generate_QR_code() {
    global $url, $qr_code_path;

    include_once('./phpqrcode/qrlib.php');

    $dir = dirname($qr_code_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    QRcode::png($url, $qr_code_path, QR_ECLEVEL_L, 10);

    if (file_exists($qr_code_path)) {
        setMessage("success", "QR code generated successfully.");
    } 
    else {
        setMessage("error", "QR code generation failed.");
    }
}

function main(){
    clearstatcache();
    create_user_folder();
    create_album_folder();
    create_subfolders();
    generate_QR_code();
}

// Call the main function to execute the operations
main();

// Output the messages (final)
echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "album_path" => $album_path,
    "album_qrcode_path" => $album_qrcode_path,
    "album_images_path" => $album_images_path,
]);
exit(0);
?>