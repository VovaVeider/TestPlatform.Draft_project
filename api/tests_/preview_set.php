<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/../api_errors/errors.php';
require_once __DIR__ .'/middleware.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin']);
$test_id = check_get_test(); //Если тест есть то его получим ид иначе отправим ошибку

$uploadFolder = __DIR__ .'/../previews'; //  имя целевой папки
$uploadedFile = $_FILES[array_key_last($_FILES)]; // Получаем файл из $_FILES
if (validateImage($uploadedFile)) {
    $conn = create_conn();
    $stmt_old_prev = $conn->prepare('SELECT preview_id from tests where id =:id');
    $stmt_old_prev->execute(['id'=>$test_id]);
    $old_prev_id = $stmt_old_prev->fetch()['preview_id'];
    if ($old_prev_id !==null){
        //Удалим файл старого превью
        unlink($uploadFolder.'/'.strval($old_prev_id).'.jpg');
        //Удалим запись в бд
        $stmt_del_old_prev = $conn->prepare('DELETE from preview where id =:id');
        $stmt_del_old_prev->execute(['id'=>$old_prev_id]);
    }
    //Добавим новое превью в БД
    $stmt_create_prev = $conn->prepare('INSERT INTO preview DEFAULT VALUES RETURNING id');
    $stmt_create_prev->execute();
    $new_prev_id = $conn->lastInsertId();
    //Закрепим превью за конкретным тестом
    $stmt_add_prev = $conn->prepare('UPDATE tests SET preview_id =:prev_id where id=:test_id');
    $stmt_add_prev->execute(['prev_id'=>$new_prev_id,'test_id'=>$test_id]);
    //Сохраним файл нового превью
    $filename = strval($new_prev_id).'.jpg';
    $result = saveImage($uploadedFile, $uploadFolder, $filename);

    $answer['error'] = null;
    $answer['error_descr'] = null;
    $answer['preview'] = "api.testplatform.ru/tests/$test_id/preview";
    http_response_code(200);//Bad request
    echo json_encode($answer);
    exit();
}   else{
    global $api_errors;
    $answer['error'] = 'INVALID_IMAGE';
    $answer['error_descr'] = $api_errors['INVALID_IMAGE'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
}
function validateImage($file, $maxWidth = 200, $maxHeight = 200) {
    // Проверяем, является ли файл изображением JPEG
    $imageInfo = getimagesize($file["tmp_name"]);
    if (!$imageInfo || $imageInfo["mime"] !== "image/jpeg") {
        return false;
    }

    // Проверяем размер изображения
    list($width, $height) = $imageInfo;
    if ($width > $maxWidth || $height > $maxHeight) {
        return false;
    }

    return true;
}

function saveImage($file, $destinationFolder,$filename) {
    // Создаем путь к папке назначения
    $destinationPath = $destinationFolder . '/' .$filename;
    // Сохраняем файл
    if (move_uploaded_file($file["tmp_name"], $destinationPath)) {
        return $filename;
    }
    return false;
}