<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
require_once __DIR__ .'/../auth/middleware.php';
require_once __DIR__ .'/../db/db.php';
require_once __DIR__ .'/../api_errors/errors.php';
require_once __DIR__ .'/middleware.php';
require_once __DIR__ .'/test_validation.php';
header('Content-Type: application/json');
$payload = rights_auth_check(['admin']);
$input_json = json_decode(file_get_contents('php://input'), true);
$test_id = check_get_test(); //Если тест есть то его получим ид иначе отправим ошибку
//Если нет JSON-а совсем
if (($input_json === null && json_last_error() !== JSON_ERROR_NONE)) {
    require __DIR__ .'/../api_errors/errors.php';
    $answer['error'] = 'JSON_INV';
    $answer['error_descr'] = $api_errors['JSON_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}
//get data
$category = $input_json['category'];
$name = $input_json['name'];
$description = $input_json['description'];
$test_body = $input_json['test_body'];

//Если указана категория и ее нет в базе
if (isset($category) && $category !== -1){
    $conn = create_conn();
    $stmt = $conn->prepare("SELECT * FROM Categories where id= :cat_id");
    $stmt->execute(["cat_id" => $input_json['category']]);
    if ($stmt->fetch() === false) {
        require __DIR__ .'/../api_errors/errors.php';
        $answer['error'] = 'СAT_NOTFND';
        $answer['error_descr'] = $api_errors['СAT_NOTFND'];
        http_response_code(400);//Bad request
        echo json_encode($answer);
        exit();
    }
}
//Если указана категория и ее нет в базе
if ((isset($name) && gettype($name) != 'string') || (isset($description) && gettype($description) != 'string') ){
    require __DIR__ .'/../api_errors/errors.php';
    $answer['error'] = 'INVALID_TYPE';
    $answer['error_descr'] = $api_errors['INVALID_TYPE'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}


//Хочет обновить имя или описание, но задет их не как строки
if (isset($test_body) && !is_valid_test_struct(json_decode(json_encode($input_json['test_body'])))) {
    require __DIR__ .'/../api_errors/errors.php';
    $answer['error'] = 'TEST_INV';
    $answer['error_descr'] = $api_errors['TEST_INV'];
    http_response_code(400);//Bad request
    echo json_encode($answer);
    exit();
}

$conn = create_conn();
if (isset($name)){
    $stmt = $conn->prepare('UPDATE TESTS SET name =:name WHERE id = :id');
    $stmt->execute([
        'id'=>$test_id,
        'name'=>$name
    ]);
}
if (isset($description)){
    $stmt = $conn->prepare('UPDATE TESTS SET description =:description WHERE id = :id');
    $stmt->execute([
        'id'=>$test_id,
        'description'=>$description
    ]);
}
if (isset($test_body)){
    $stmt = $conn->prepare('UPDATE TESTS SET test =:test WHERE id = :id');
    $stmt->execute([
        'id'=>$test_id,
        'test'=>json_encode($test_body,JSON_UNESCAPED_UNICODE)
    ]);
}
if (isset($category)){
    $category = ($category === -1) ? null : $category;
    $stmt = $conn->prepare('UPDATE TESTS SET category_id =:category WHERE id = :id');
    $stmt->execute([
        'id'=>$test_id,
        'category'=>$category
    ]);
}


$answer['error'] = null;
$answer['error_descr'] = null;
http_response_code(200);//Bad request
echo json_encode($answer);
exit();

