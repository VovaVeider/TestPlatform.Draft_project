<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/../api_errors/errors.php';
require_once __DIR__ .'/middleware.php';
$payload = rights_auth_check(['admin']);
$test_id = check_get_test(); //Если тест есть то его получим ид иначе отправим ошибку
$conn = create_conn();
$stmt_old_prev = $conn->prepare('SELECT preview_id from tests where id =:id');
$stmt_old_prev->execute(['id'=>$test_id]);
$old_prev_id = $stmt_old_prev->fetch()['preview_id'];
$uploadFolder = __DIR__ .'/../previews'; //  имя целевой папки
if ($old_prev_id !==null){
    //Удалим файл старого превью
    unlink($uploadFolder.'/'.strval($old_prev_id).'.jpg');
    //Удалим запись в бд
    $stmt_del_old_prev = $conn->prepare('DELETE from preview where id =:id');
    $stmt_del_old_prev->execute(['id'=>$old_prev_id]);
}