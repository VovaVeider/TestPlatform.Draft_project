<?php
require __DIR__ .'/../api_errors/errors.php';
global $api_errors;
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/../db/db.php';
header('Content-Type: application/json');
$payload=rights_auth_check(['admin','user']);
$input_json = json_decode(file_get_contents('php://input'),true);
$answer = ['error' => null, 'error_descr' => null];
//Если пользователь не дал нового или старого пароля
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE)
    || !isset($input_json['old_password'])
    || !isset($input_json['new_password'])) {

    $answer['error'] = 'JSON_INV';
    $answer['error_descr'] = $api_errors['JSON_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer,JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id=$payload['id'];
$old_passwd = $input_json['old_password'];
$new_passwd = $input_json['new_password'];
//если новый пароль слабый
if (mb_strlen($new_passwd,'UTF-8')<8){
    $answer['error'] = 'WEAK_PASSWD';
    $answer['error_descr'] = $api_errors['WEAK_PASSWD'];
    http_response_code(400);//Bad request
    echo json_encode($answer,JSON_UNESCAPED_UNICODE);
    exit();
}
$conn = create_conn();
$stmt = $conn->prepare('UPDATE users 
                                SET password = :new_password
                                where id =:id and password=:old_password');
$stmt->execute(['old_password'=>$old_passwd,'id'=>$user_id,'new_password'=>$new_passwd]);
if ($stmt->rowCount()===0){
    $answer['error'] = 'AUTH_FAIL';
    $answer['error_descr'] = $api_errors['AUTH_FAIL'];
    http_response_code(403);
}
echo json_encode($answer,JSON_UNESCAPED_UNICODE);

