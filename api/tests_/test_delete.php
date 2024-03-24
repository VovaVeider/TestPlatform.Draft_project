<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/utils.php';
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/middleware.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin']);
$test_id = check_get_test();

$conn = create_conn();
$stmt=$conn->prepare('DELETE FROM TESTS WHERE ID = :id');
$stmt->execute(['id'=>$test_id]);
$answer = ['error' => null, 'error_descr' => null];
http_response_code(200);
echo json_encode($answer);
