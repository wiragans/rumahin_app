<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

require '../db_connection.php';

if($_SESSION['rumahinapp_admin_isLogin'] != "ya")
{
    $json = [
        'statusCode'=>401,
        'status'=>false,
        'message'=>'Anda harus login dulU!',
        'data'=>[
            
        ]
    ];

    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($json);

    exit();
}

$getRequestMethod = $_SERVER['REQUEST_METHOD'];

if($getRequestMethod != "POST")
{
    $json = [
        'statusCode'=>405,
        'status'=>false,
        'message'=>'Method Not Allowed',
        'data'=>[
            
        ]
    ];

    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($json);

    exit();
}

// GET KATALOG DATA
$getKatalogUUID = trim($_POST['katalogUUID']);

$validasiKatalogUUID = $conn->prepare("SELECT * FROM katalog_data WHERE BINARY katalogUUID=:katalogUUID");
$validasiKatalogUUID->bindParam(':katalogUUID', $getKatalogUUID);
$validasiKatalogUUID->execute();

$statusAPI = false;

if($validasiKatalogUUID->rowCount() <= 0)
{
    $messageText = "Wah, katalog ini tidak dapat ditemukan!";
    $goUrl = "";
    $statusAPI = false;
}

if($validasiKatalogUUID->rowCount() > 0)
{
    foreach($validasiKatalogUUID as $rowValidasiKatalogUUID)
    {
        $getKatalogID = intval($rowValidasiKatalogUUID['id']);
    }

    $messageText = "Katalog ditemukan, silakan klik link untuk melihat ";
    $goUrl = "https://www.netspeed.my.id/rumahinappadmin/katalog_details.php?katalogID=" . $getKatalogID;
    $statusAPI = true;
}

$json = [
    'statusCode'=>200,
    'status'=>$statusAPI,
    'message'=>$messageText,
    'data'=>[
        'goUrl'=>$goUrl
    ]
];

header('HTTP/1.1 200 OK');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($json);

exit();
?>