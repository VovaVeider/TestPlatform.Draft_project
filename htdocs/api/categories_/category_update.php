<?php
require_once '../auth/middleware.php';
require_once '../db/db.php';
require 'utils.php';
require_once 'middleware.php';
header('Content-Type: application/json');
$payload=rights_auth_check(['admin']);
$cat_id = check_get_catid();
$input_json = json_decode(file_get_contents('php://input'),true);


//Если нет JSON-а или в нем нет нужных параметров
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE)
    || !isset($input_json['name']) || (gettype($input_json['name'])!=='string'))
{
    require  '../api_errors/errors.php';
    $answer['error'] = 'JSON_INV';
    $answer['error_descr'] = $api_errors['JSON_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}
$name = $input_json['name'];
if (category_name_is_occupied($name)){
    require  '../api_errors/errors.php';
    $answer['error'] = 'CAT_EXISTS';
    $answer['error_descr'] = $api_errors['CAT_EXISTS'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
} else{
    $conn=create_conn();
    $stmt=$conn->prepare('UPDATE categories  SET name =:name WHERE ID = :id');
    $stmt->execute(['id'=>$cat_id,'name'=>$name]);
    $answer = ['error' => null, 'error_descr' => null];
    http_response_code(200);
    echo json_encode($answer);
}

