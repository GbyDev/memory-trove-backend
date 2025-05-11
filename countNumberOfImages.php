<?php
include('db.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$album_id = $_POST["album_id"] ?? "";

$image_count = 0;
$message = "";
$messageType = "";

function setMessage($msgType, $msg) {
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function get_image_count() {
    global $conn, $album_id, $image_count;

    if (!$album_id) {
        setMessage("error", "Missing album_id");
        return;
    }

    $sql = "SELECT COUNT(*) AS total FROM images WHERE album_id = '$album_id'";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $image_count = intval($row['total']);
    } else {
        setMessage("error", "Failed to count images");
    }
}

get_image_count();

echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "imageCount" => $image_count
]);
exit(0);
?>