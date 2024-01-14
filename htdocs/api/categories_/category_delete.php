<?php
require_once '../auth/middleware.php';
require_once 'middleware.php';
require_once '../db/db.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin']);
$cat_id = check_get_catid();

$conn = create_conn();
$stmt=$conn->prepare('DELETE FROM categories WHERE ID = :id');
$stmt->execute(['id'=>$cat_id]);
$answer = ['error' => null, 'error_descr' => null];
http_response_code(200);
echo json_encode($answer);
