<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once '../auth/middleware.php';
require_once 'middleware.php';
require_once 'test_validation.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin', 'user']);
$test_id = check_get_test();
$input_json = json_decode(file_get_contents('php://input'),true);
//Если пользователь не дал json-а совсем или нет резккльтатов
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE
    || !isset($input_json['results'])
    || gettype(($input_json['results'])) !== 'array')) {

    require '../api_errors/errors.php';
    $answer['error'] = 'JSON_INV';
    $answer['error_descr'] = $api_errors['JSON_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}

$conn = create_conn();
$stmt = $conn->prepare('SELECT test FROM TESTS WHERE ID = :id');
$stmt->execute(['id' => $test_id]);
$test = json_decode($stmt->fetch()['test'],true);
$test_answ=$input_json['results'];

if (is_valid_test_answ($test_answ,$test)){
    $conn = create_conn();
    $stmt = $conn->prepare('UPDATE tests SET passed = passed + 1 WHERE ID = :id');
    $stmt->execute(['id' => $test_id]);

    $result = get_test_result($test_answ,$test);
    $answer['error'] = null;
    $answer['error_descr'] = null;
    $answer['result']=$result;
    http_response_code(200);//Bad request
    echo json_encode($answer,JSON_UNESCAPED_UNICODE);
    exit;
}
else{
    require '../api_errors/errors.php';
    $answer['error'] = 'TST_RES_INV';
    $answer['error_descr'] = $api_errors['TST_RES_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}