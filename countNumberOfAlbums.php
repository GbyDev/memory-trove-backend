<?php
include('db.php'); 

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



//Variables
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['userId'] ?? null; 
$numOfAlbums = 0; 
$message = "";
$messageType = "";

function setMessage($msgType, $msg){
    global $messageType, $message;
    $messageType = $msgType;
    $message = $msg;
}

function setNumOfAlbums($num) {
    global $numOfAlbums;
    $numOfAlbums = $num;
}

function count_number_of_albums(){
    global $conn, $user_id, $numOfAlbums;

    $sql = "SELECT COUNT(*) as album_count FROM albums WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $numOfAlbums = $row["album_count"];
        setNumOfAlbums($numOfAlbums);
        setMessage("success", "$numOfAlbums albums retrieved successfully for user ID $user_id.");
        return;
    } 
    setMessage("error", "No albums found for user ID $user_id.");
}

count_number_of_albums();




//Output the messages (final)
echo json_encode([
    "message" => $message,
    "messageType" => $messageType,
    "albumCount" => $numOfAlbums
]);
exit(0);
?>