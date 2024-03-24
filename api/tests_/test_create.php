<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/../api_errors/errors.php';
require_once  __DIR__ .'/test_validation.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin']);
$input_json = json_decode(file_get_contents('php://input'),true);

//TODO: ДОБАВИТЬ ПРОВЕРКУ НА ТИПЫ ДЛЯ ИМЕНИ, ОПИСАНИЯ И Т.Д
//Если нет JSON-а или в нем нет нужных параметров
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE)
    || !isset($input_json['name']) || !isset($input_json['category']) ||
    !isset($input_json['description']) ||!isset($input_json['test_body'])
) {
        require  __DIR__ .'/../api_errors/errors.php';
        $answer['error'] = 'JSON_INV';
        $answer['error_descr'] = $api_errors['JSON_INV'];
        http_response_code(400);//Bad request
        echo json_encode($answer);
        exit();
}
//Стуктура самого теста неверная
if (!is_valid_test_struct(json_decode(json_encode($input_json['test_body'])))){
    require  __DIR__ .'/../api_errors/errors.php';
    $answer['error'] = 'TEST_INV';
    $answer['error_descr'] = $api_errors['TEST_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}
//Если указана категория и ее нет в базе
if ($input_json['category']!==-1){
    $conn = create_conn();
    $stmt = $conn->prepare("SELECT * FROM Categories where id= :cat_id");
    $stmt->execute(["cat_id"=>$input_json['category']]);
    if ($stmt->fetch()===false){
        require  __DIR__ .'/../api_errors/errors.php';
        $answer['error'] = 'СAT_NOTFND';
        $answer['error_descr'] = $api_errors['СAT_NOTFND'];
        http_response_code(400);//Bad request
        echo json_encode($answer);
        exit();
    }
}
if ($input_json['category']!==-1){
    $conn = create_conn();
    $stmt = $conn->prepare('INSERT INTO TESTS (NAME, DESCRIPTION, CATEGORY_ID, TEST)'.
        'VALUES  (:name, :descr, :cat_id, :test) ');
    $stmt->execute([
        "cat_id"=>$input_json['category'],
        'name'=>$input_json['name'],
        'descr'=>$input_json['description'],
        'test'=>json_encode($input_json['test_body'])
        ]);
}   else{
    $conn = create_conn();
    $stmt = $conn->prepare('INSERT INTO TESTS (NAME, DESCRIPTION,TEST)'.
        'VALUES  (:name, :descr, :test) ');
    $stmt->execute([
        'name'=>$input_json['name'],
        'descr'=>$input_json['description'],
        'test'=>json_encode($input_json['test_body'])
    ]);

}
$answer['error'] = null;
$answer['error_descr'] = null;
$answer['id']=(int)$conn->lastInsertId();
http_response_code(200);//Bad request
echo json_encode($answer);
exit();

