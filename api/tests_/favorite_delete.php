<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/middleware.php';
header('Content-Type: application/json');
$answer = ['error' => null, 'error_descr' => null];
$payload=rights_auth_check(['admin','user']);
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri,PHP_URL_PATH);

$test_id= (int)explode('/',$uri)[5];
$user_id =$payload['id'];

$conn = create_conn();
$stmt=$conn->prepare('DELETE FROM favorites where test_id=:test_id and user_id=:user_id');
$stmt->execute(['test_id'=>$test_id,'user_id'=>$user_id]);

http_response_code(200);
echo json_encode($answer);
exit;
