<?php
require_once '../auth/middleware.php';
require_once '../db/db.php';
require_once 'middleware.php';
header('Content-Type: application/json');
$payload=rights_auth_check(['admin','user']);
$input_json = json_decode(file_get_contents('php://input'),true);
$answer = ['error' => null, 'error_descr' => null];
//Если пользователь не дал json-а совсем или нет test id
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE
    || !isset($input_json['id'])
    || gettype(($input_json['id'])) !== 'integer')) {

    require '../api_errors/errors.php';
    $answer['error'] = 'JSON_INV';
    $answer['error_descr'] = $api_errors['JSON_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}
$test_id=$input_json['id'];

//Если нет такого теста
$conn = create_conn();
$stmt=$conn->prepare('SELECT COUNT (*) AS NUM FROM TESTS WHERE ID =:id');
$stmt->execute(['id'=>$test_id]);
$result = $stmt->fetch();
if ($result['num'] === 0 ){
    $answer = ['error' => 'TEST_NOT_FOUND', 'error_descr' => $api_errors['TEST_NOT_FOUND']];
    http_response_code(404);
    echo json_encode($answer);
    exit;
}
$stmt=$conn->prepare('SELECT COUNT (*) AS NUM FROM favorites WHERE test_id =:test_id and user_id =:user_id');
$stmt->execute(['test_id'=>$test_id,'user_id'=>$payload['id']]);
$result = $stmt->fetch();
if ($result['num'] === 0 ){
    $stmt=$conn->prepare('INSERT INTO favorites(USER_ID, TEST_ID) VALUES (:user_id,:test_id)');
    $stmt->execute(['test_id'=>$test_id,'user_id'=>$payload['id']]);
}

http_response_code(200);
echo json_encode($answer);
exit;
