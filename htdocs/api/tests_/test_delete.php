<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once '../auth/middleware.php';
require_once  'utils.php';
require_once '../db/db.php';
require_once 'middleware.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin']);
$test_id = check_get_test();

$conn = create_conn();
$stmt=$conn->prepare('DELETE FROM TESTS WHERE ID = :id');
$stmt->execute(['id'=>$id]);
$answer = ['error' => null, 'error_descr' => null];
http_response_code(200);
echo json_encode($answer);
