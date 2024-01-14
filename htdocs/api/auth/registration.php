<?php

require_once '../db/db.php';
require_once '../api_errors/errors.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$answer = ['error' => null, 'error_descr' => null];
// Получаем данные из тела запроса
$input_data = file_get_contents('php://input');
// Парсим JSON-строку
$input_json = json_decode($input_data, true);

//Если нет JSON-а или нет в нём логина,пароля,почты
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE)
    || !isset($input_json['login']) || !isset($input_json['password']) || !isset($input_json['email'])) {
    $answer['error'] = 'JSON_INV';
    $answer['error_descr'] = $api_errors['JSON_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}

$login = $input_json['login'];
$passwd = $input_json['password'];
$email = $input_json['email'];
//Проверка логина на мин. длину
if (mb_strlen($login,'UTF-8')<2){
    $answer['error'] = 'LOGIN_INCORR';
    $answer['error_descr'] = $api_errors['LOGIN_INCORR'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}
//Проверка пароля на мин. длину
if (mb_strlen($passwd,'UTF-8')<8){
    $answer['error'] = 'WEAK_PASSWD';
    $answer['error_descr'] = $api_errors['WEAK_PASSWD'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}

//Проверка email соотвестсвие формату
if (!preg_match("/^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/",$email)){
    $answer['error'] = 'EMAIL_INCORR';
    $answer['error_descr'] = $api_errors['EMAIL_INCORR'];
    echo json_encode($answer);
    http_response_code(400);//Bad request
    exit();
}

try {
    $conn = create_conn();
} catch (PDOException $e) {
    http_response_code(500);//Bad request
    $answer['error'] = 'SERVER_ERR';
    $answer['error_descr'] = $api_errors['SERVER_ERR'];
    echo json_encode($answer);
    exit;
}

//login exists
$sql_login = "SELECT * FROM users WHERE login = :login";
$stmt = $conn->prepare($sql_login);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->execute();
$count = $stmt->rowCount();

if ($count > 0) {
    http_response_code(400);//Bad request
    $answer['error'] = 'LOGIN_EXISTS';
    $answer['error_descr'] = $api_errors['LOGIN_EXISTS'];
    echo json_encode($answer);
    exit;
}

//email exists
$sql_email = "SELECT * FROM users WHERE email = :email";
$stmt = $conn->prepare($sql_email);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$count = $stmt->rowCount();

if ($count > 0) {
    http_response_code(400);//Bad request
    $answer['error'] = 'EMAIL_EXISTS';
    $answer['error_descr'] = $api_errors['EMAIL_EXISTS'];
    echo json_encode($answer);
    exit;
}

//registration
$stmt = $conn->prepare("INSERT INTO Users (ROLE, LOGIN, PASSWORD, EMAIL) VALUES (?, ?, ?, ?)");
$stmt->execute(['user', $login, $passwd, $email]);
$id = $conn->lastInsertId();
require_once  'utils.php';
$answer['jwt'] = generate_jwt($id,$login,'user');
$answer['id']=$id;
$answer['role']='user';
$answer['login']=$login;
http_response_code(200);
echo json_encode($answer,JSON_UNESCAPED_UNICODE);
exit;