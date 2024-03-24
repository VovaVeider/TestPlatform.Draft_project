<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/../api_errors/errors.php';
require_once __DIR__ .'/middleware.php';
$test_id = get_testid_url();

$conn = create_conn();
$stmt_prev = $conn->prepare('SELECT preview_id from tests where id =:id');
$stmt_prev->execute(['id'=>$test_id]);
$test = $stmt_prev->fetch();
if ($test_id === false || $test['preview_id']===null){
    header("HTTP/1.1 404 Not Found");
    exit;
}

// Путь к файлу изображения
$imagePath = __DIR__ .'/../previews/'.strval($test['preview_id']).'.jpg';
// Устанавливаем заголовки для передачи изображения
header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($imagePath));
// Открываем и отправляем содержимое файла
//$im = imagecreatefromjpeg($imagePath);
//imagejpeg($im);
readfile($imagePath);