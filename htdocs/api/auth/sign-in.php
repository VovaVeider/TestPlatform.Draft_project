<?php
require_once '../api_errors/errors.php';
require_once '../db/db.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
$answer=[];
$answer['error'] = null;
$answer['error_descr'] = null;

$login = $_GET['login'];
$passwd = $_GET['password'];
if (!isset($login) || !isset($passwd)){
    echo json_encode(['error'=>'INVALID_PARAMS','error_descr'=>$api_errors['INVALID_PARAMS']]);
} else {
    $conn = create_conn();
    $stmt = $conn->prepare('SELECT * FROM users WHERE login = :login and password = :password ');
    $stmt->execute(['login' => $login, 'password' => $passwd]);
    $user_info = $stmt->fetch();
    if ($user_info === false) {
        $answer['error'] = 'AUTH_FAIL';
        $answer['error_descr'] = $api_errors['AUTH_FAIL'];
        echo json_encode($answer);
    } else {
        require_once 'utils.php';
        $id = $user_info['id'];
        $role = $user_info['role'];
        $answer['jwt'] = generate_jwt($id, $login,  $role);
        $answer['id'] = $id;
        $answer['role'] = $role;
        $answer['login'] = $login;
        echo json_encode($answer,JSON_UNESCAPED_UNICODE);
    }
}
